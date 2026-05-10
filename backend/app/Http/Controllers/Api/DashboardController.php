<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Thesis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $stats = [
            'total_theses' => Thesis::count(),
            'student_theses' => Thesis::where('document_type', 'student_thesis')->count(),
            'faculty_research' => Thesis::where('document_type', 'faculty_research')->count(),
            'approved_theses' => Thesis::where('status', 'approved')->count(),
            'pending_theses' => Thesis::where('status', 'pending')->count(),
            'total_downloads' => Thesis::sum('download_count'),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
        ];

        return response()->json($stats);
    }

    public function recentActivity(Request $request)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $recentTheses = Thesis::with(['uploader.role', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentDocuments = Document::with(['thesis', 'uploader.role'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'recent_theses' => $recentTheses,
            'recent_documents' => $recentDocuments,
        ]);
    }

    public function chartData(Request $request)
    {
        if (!$request->user()->canApproveTheses()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Theses by year
        $thesesByYear = Thesis::select('year', DB::raw('count(*) as count'))
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Theses by department
        $thesesByDepartment = Thesis::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Theses by status
        $thesesByStatus = Thesis::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Monthly uploads (last 12 months)
        $monthlyUploads = Thesis::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json([
            'theses_by_year' => $thesesByYear,
            'theses_by_department' => $thesesByDepartment,
            'theses_by_status' => $thesesByStatus,
            'monthly_uploads' => $monthlyUploads,
        ]);
    }

    public function userStats(Request $request)
    {
        $user = $request->user();
        
        $stats = [
            'uploaded_theses' => $user->uploadedTheses()->count(),
            'approved_theses' => $user->uploadedTheses()->where('status', 'approved')->count(),
            'pending_theses' => $user->uploadedTheses()->where('status', 'pending')->count(),
            'total_downloads' => $user->uploadedTheses()->sum('download_count'),
        ];

        if ($user->canApproveTheses()) {
            $stats['approved_by_me'] = $user->approvedTheses()->count();
        }

        return response()->json($stats);
    }
}