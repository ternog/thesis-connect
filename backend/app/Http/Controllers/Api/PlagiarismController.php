<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PlagiarismCheck;
use App\Models\Thesis;
use App\Services\PlagiarismChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlagiarismController extends Controller
{
    protected $plagiarismChecker;

    public function __construct(PlagiarismChecker $plagiarismChecker)
    {
        $this->plagiarismChecker = $plagiarismChecker;
    }

    /**
     * Check thesis for plagiarism
     */
    public function checkThesis(Request $request, Thesis $thesis)
    {
        // Only admin, library staff, and faculty can check plagiarism
        if (!$request->user()->canApproveTheses() && !$request->user()->isFaculty()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        try {
            // Combine title and abstract for checking
            $textToCheck = $thesis->title . ' ' . $thesis->abstract;

            // Run plagiarism check
            $result = $this->plagiarismChecker->checkSimilarity($textToCheck, $thesis->id);

            // Save the check result
            $check = PlagiarismCheck::create([
                'thesis_id' => $thesis->id,
                'checked_by' => $request->user()->id,
                'similarity_score' => $result['overall_score'],
                'matches' => $result['matches'],
                'checked_content' => substr($textToCheck, 0, 1000), // Store first 1000 chars
                'status' => $result['status'],
            ]);

            // Log activity
            ActivityLog::logActivity(
                "Ran plagiarism check on thesis: {$thesis->title}",
                $thesis,
                $request->user(),
                [
                    'similarity_score' => $result['overall_score'],
                    'matches_found' => count($result['matches']),
                ],
                'plagiarism_check',
                'thesis'
            );

            return response()->json([
                'check' => $check->load('checker'),
                'severity' => $check->getSeverityLevel(),
                'message' => $this->getSeverityMessage($check->getSeverityLevel()),
            ]);
        } catch (\Exception $e) {
            Log::error('Plagiarism check error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to run plagiarism check',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check text content for plagiarism (before submission)
     */
    public function checkText(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:100',
            'thesis_id' => 'nullable|exists:theses,id',
        ]);

        try {
            $result = $this->plagiarismChecker->checkSimilarity(
                $request->text,
                $request->thesis_id
            );

            return response()->json([
                'similarity_score' => $result['overall_score'],
                'matches' => $result['matches'],
                'severity' => $this->getSeverityLevelFromScore($result['overall_score']),
                'message' => $this->getSeverityMessage($this->getSeverityLevelFromScore($result['overall_score'])),
                'status' => $result['status'],
            ]);
        } catch (\Exception $e) {
            Log::error('Text plagiarism check error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to check text',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get plagiarism check history for a thesis
     */
    public function getThesisChecks(Request $request, Thesis $thesis)
    {
        if (!$request->user()->canApproveTheses() && 
            !$request->user()->isFaculty() && 
            $thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $checks = PlagiarismCheck::where('thesis_id', $thesis->id)
            ->with('checker')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($check) {
                return [
                    'id' => $check->id,
                    'similarity_score' => $check->similarity_score,
                    'matches_count' => count($check->matches ?? []),
                    'severity' => $check->getSeverityLevel(),
                    'status' => $check->status,
                    'checked_by' => $check->checker->name,
                    'checked_at' => $check->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json($checks);
    }

    /**
     * Get detailed plagiarism check result
     */
    public function getCheckDetail(Request $request, PlagiarismCheck $check)
    {
        if (!$request->user()->canApproveTheses() && 
            !$request->user()->isFaculty() && 
            $check->thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'check' => $check->load(['thesis', 'checker']),
            'severity' => $check->getSeverityLevel(),
            'severity_color' => $check->getSeverityColor(),
            'message' => $this->getSeverityMessage($check->getSeverityLevel()),
        ]);
    }

    /**
     * Get all plagiarism checks (admin only)
     */
    public function index(Request $request)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = PlagiarismCheck::with(['thesis', 'checker']);

        // Filter by severity
        if ($request->has('severity')) {
            $severity = $request->severity;
            $query->where(function($q) use ($severity) {
                switch($severity) {
                    case 'high':
                        $q->where('similarity_score', '>=', 70);
                        break;
                    case 'medium':
                        $q->whereBetween('similarity_score', [40, 69.99]);
                        break;
                    case 'low':
                        $q->whereBetween('similarity_score', [20, 39.99]);
                        break;
                    case 'minimal':
                        $q->where('similarity_score', '<', 20);
                        break;
                }
            });
        }

        $checks = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($checks);
    }

    /**
     * Get severity level from score
     */
    private function getSeverityLevelFromScore(float $score): string
    {
        if ($score >= 70) {
            return 'high';
        } elseif ($score >= 40) {
            return 'medium';
        } elseif ($score >= 20) {
            return 'low';
        }
        return 'minimal';
    }

    /**
     * Get severity message
     */
    private function getSeverityMessage(string $level): string
    {
        return match($level) {
            'high' => 'High similarity detected. This content may require significant revision.',
            'medium' => 'Moderate similarity detected. Review and revise similar sections.',
            'low' => 'Low similarity detected. Minor similarities found.',
            'minimal' => 'Minimal similarity detected. Content appears original.',
            default => 'Similarity check completed.',
        };
    }
}
