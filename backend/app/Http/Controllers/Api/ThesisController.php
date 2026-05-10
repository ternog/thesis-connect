<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Thesis;
use App\Models\ThesisView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ThesisController extends Controller
{
    public function index(Request $request)
    {
        // Handle optional authentication for API routes
        $user = null;
        if ($request->bearerToken()) {
            try {
                $user = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable;
            } catch (\Exception $e) {
                // Token is invalid, continue as unauthenticated
            }
        }

        $query = Thesis::with(['category', 'uploader.role', 'activeDocument']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filters
        if ($request->has('year') && $request->year) {
            $query->byYear($request->year);
        }

        if ($request->has('department') && $request->department) {
            $query->byDepartment($request->department);
        }

        if ($request->has('program') && $request->program) {
            $query->byProgram($request->program);
        }

        if ($request->has('academic_level') && $request->academic_level) {
            $query->byAcademicLevel($request->academic_level);
        }

        if ($request->has('document_type') && $request->document_type) {
            $query->byDocumentType($request->document_type);
        }

        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter - only show approved for non-admin users
        if (!$user || !$user->canApproveTheses()) {
            $query->approved();
        } elseif ($request->has('status') && $request->status) {
            // Admin user with specific status filter - show only that status
            $query->where('status', $request->status);
        } else {
            // For admin users without status filter, show approved by default
            // This prevents showing all statuses when no filter is specified
            $query->approved();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $theses = $query->paginate($request->get('per_page', 15));

        return response()->json($theses);
    }

    public function store(Request $request)
    {
        // Check if user can upload theses
        if (!$request->user()->canUploadTheses()) {
            return response()->json([
                'message' => 'You do not have permission to upload theses.'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'authors' => 'required|array|min:1',
            'authors.*' => 'required|string|max:255',
            'adviser' => 'nullable|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'department' => 'required|string|max:255',
            'program' => 'required|string|max:255',
            'academic_level' => 'required|in:undergraduate,graduate',
            'document_type' => 'required|in:student_thesis,faculty_research',
            'abstract' => 'required|string',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        // Check for duplicate thesis
        $existingThesis = Thesis::where('title', $request->title)
            ->whereJsonContains('authors', $request->authors[0])
            ->first();

        if ($existingThesis) {
            return response()->json([
                'message' => 'A thesis with this title and author already exists.'
            ], 422);
        }

        // Run plagiarism check on title and abstract
        $plagiarismChecker = new \App\Services\PlagiarismChecker();
        $textToCheck = $request->title . ' ' . $request->abstract;
        $plagiarismResult = $plagiarismChecker->checkSimilarity($textToCheck);

        // Get plagiarism threshold from config
        $plagiarismThreshold = config('plagiarism.threshold', 40);

        // If plagiarism score is too high, reject the submission
        if ($plagiarismResult['overall_score'] >= $plagiarismThreshold) {
            return response()->json([
                'message' => 'Thesis submission rejected due to high plagiarism detection.',
                'plagiarism_score' => $plagiarismResult['overall_score'],
                'threshold' => $plagiarismThreshold,
                'matches' => array_slice($plagiarismResult['matches'], 0, 5), // Return top 5 matches
                'error_type' => 'plagiarism_detected'
            ], 422);
        }

        // Determine status based on user role
        $user = $request->user();
        $userRole = $user->role->name;
        
        // Admin uploads are auto-approved
        // Library staff and faculty uploads need approval
        $needsApproval = in_array($userRole, ['library_staff', 'faculty']);
        
        $status = $needsApproval ? 'pending' : 'approved';
        $approvedAt = $needsApproval ? null : now();
        $approvedBy = $needsApproval ? null : $user->id;

        $thesis = Thesis::create([
            'title' => $request->title,
            'authors' => $request->authors,
            'adviser' => $request->adviser,
            'year' => $request->year,
            'department' => $request->department,
            'program' => $request->program,
            'academic_level' => $request->academic_level,
            'document_type' => $request->document_type,
            'abstract' => $request->abstract,
            'keywords' => $request->keywords,
            'category_id' => $request->category_id,
            'uploaded_by' => $user->id,
            'status' => $status,
            'approved_at' => $approvedAt,
            'approved_by' => $approvedBy,
        ]);

        // Store plagiarism check result
        if ($plagiarismResult['status'] === 'completed') {
            \App\Models\PlagiarismCheck::create([
                'thesis_id' => $thesis->id,
                'checked_by' => $user->id,
                'similarity_score' => $plagiarismResult['overall_score'],
                'matches_found' => count($plagiarismResult['matches']),
                'check_result' => $plagiarismResult,
                'status' => 'completed',
            ]);
        }

        // Log activity
        $activityMessage = $needsApproval 
            ? "Uploaded thesis for approval: {$thesis->title}"
            : "Created and published thesis: {$thesis->title}";
            
        ActivityLog::logActivity(
            $activityMessage,
            $thesis,
            $user,
            [
                'status' => $status,
                'uploader_role' => $userRole,
                'needs_approval' => $needsApproval,
                'plagiarism_score' => $plagiarismResult['overall_score'],
            ],
            'thesis_created',
            'thesis'
        );

        // Clear cache
        Cache::forget('filter_options');

        $message = $needsApproval
            ? 'Thesis uploaded successfully. Waiting for admin approval before it becomes publicly visible.'
            : 'Thesis created and published successfully.';

        return response()->json([
            'thesis' => $thesis->load(['category', 'uploader']),
            'message' => $message,
            'needs_approval' => $needsApproval,
            'plagiarism_score' => $plagiarismResult['overall_score'],
            'plagiarism_status' => 'passed',
        ], 201);
    }

    public function show(Request $request, Thesis $thesis)
    {
        // Check if user can view this thesis
        if ($thesis->status !== 'approved' && 
            !$request->user()?->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()?->id) {
            return response()->json(['message' => 'Thesis not found.'], 404);
        }

        // Record view
        ThesisView::recordView($thesis, $request->user());

        // Log activity
        if ($request->user()) {
            ActivityLog::logActivity(
                "Viewed thesis: {$thesis->title}",
                $thesis,
                $request->user(),
                null,
                'thesis_viewed',
                'thesis'
            );
        }

        return response()->json($thesis->load([
            'category', 
            'uploader.role', 
            'approver.role', 
            'activeDocument',
            'authors'
        ]));
    }

    public function update(Request $request, Thesis $thesis)
    {
        // Check permissions
        if (!$request->user()->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'authors' => 'sometimes|required|array|min:1',
            'authors.*' => 'required|string|max:255',
            'adviser' => 'nullable|string|max:255',
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'department' => 'sometimes|required|string|max:255',
            'program' => 'sometimes|required|string|max:255',
            'academic_level' => 'sometimes|required|in:undergraduate,graduate',
            'document_type' => 'sometimes|required|in:student_thesis,faculty_research',
            'abstract' => 'sometimes|required|string',
            'keywords' => 'sometimes|required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'sometimes|required|in:pending,approved,rejected,archived',
        ]);

        $updateData = $request->only([
            'title', 'authors', 'adviser', 'year', 'department', 
            'program', 'academic_level', 'document_type', 
            'abstract', 'keywords', 'category_id'
        ]);

        // If non-admin edits an approved thesis, reset to pending
        if (!$request->user()->hasRole('admin') && 
            $thesis->status === 'approved' && 
            count($updateData) > 0) {
            $updateData['status'] = 'pending';
            $updateData['approved_at'] = null;
            $updateData['approved_by'] = null;
        }

        // Only admins/library staff can change status
        if ($request->has('status') && $request->user()->canApproveTheses()) {
            $updateData['status'] = $request->status;
            
            if ($request->status === 'approved') {
                $updateData['approved_at'] = now();
                $updateData['approved_by'] = $request->user()->id;
            }
        }

        $thesis->update($updateData);

        // Log activity
        ActivityLog::logActivity(
            "Updated thesis: {$thesis->title}",
            $thesis,
            $request->user(),
            ['updated_fields' => array_keys($updateData)],
            'thesis_updated',
            'thesis'
        );

        return response()->json($thesis->load(['category', 'uploader', 'approver']));
    }

    public function destroy(Request $request, Thesis $thesis)
    {
        if (!$request->user()->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Log activity before deletion
        ActivityLog::logActivity(
            "Deleted thesis: {$thesis->title}",
            $thesis,
            $request->user(),
            ['thesis_id' => $thesis->id],
            'thesis_deleted',
            'thesis'
        );

        $thesis->delete();

        // Clear cache
        Cache::forget('filter_options');

        return response()->json(['message' => 'Thesis deleted successfully.']);
    }

    public function approve(Request $request, Thesis $thesis)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $thesis->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        ActivityLog::logActivity(
            "Approved thesis: {$thesis->title}",
            $thesis,
            $request->user(),
            ['status' => 'approved'],
            'thesis_approved',
            'thesis'
        );

        return response()->json($thesis->load(['category', 'uploader', 'approver']));
    }

    public function reject(Request $request, Thesis $thesis)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $thesis->update(['status' => 'rejected']);

        ActivityLog::logActivity(
            "Rejected thesis: {$thesis->title}",
            $thesis,
            $request->user(),
            ['reason' => $request->reason],
            'thesis_rejected',
            'thesis'
        );

        return response()->json($thesis->load(['category', 'uploader']));
    }

    public function getFilters()
    {
        // Cache filter options for 1 hour
        $filters = Cache::remember('filter_options', 3600, function () {
            return [
                'years' => Thesis::distinct()->pluck('year')->sort()->values(),
                'departments' => Thesis::distinct()->pluck('department')->sort()->values(),
                'programs' => Thesis::distinct()->pluck('program')->sort()->values(),
                'academic_levels' => ['undergraduate', 'graduate'],
                'document_types' => ['student_thesis', 'faculty_research'],
            ];
        });

        return response()->json($filters);
    }

    public function checkPlagiarism(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'abstract' => 'required|string',
            'thesis_id' => 'nullable|integer|exists:theses,id',
        ]);

        $plagiarismChecker = new \App\Services\PlagiarismChecker();
        $textToCheck = $request->title . ' ' . $request->abstract;
        $plagiarismResult = $plagiarismChecker->checkSimilarity($textToCheck, $request->thesis_id);

        $threshold = config('plagiarism.threshold', 40);
        $status = $plagiarismResult['overall_score'] >= $threshold ? 'failed' : 'passed';

        return response()->json([
            'score' => $plagiarismResult['overall_score'],
            'threshold' => $threshold,
            'status' => $status,
            'matches' => array_slice($plagiarismResult['matches'], 0, 5),
            'message' => $status === 'passed' 
                ? 'Plagiarism check passed. You can proceed with submission.'
                : 'Plagiarism score is too high. Please revise your thesis before submitting.',
        ]);
    }
}