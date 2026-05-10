<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = ActivityLog::with(['subject', 'causer'])
            ->orderBy('created_at', 'desc');

        // Filter by log name
        if ($request->has('log_name')) {
            $query->inLog($request->log_name);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', 'App\\Models\\User');
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Filter by event type
        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json($logs);
    }

    public function show(ActivityLog $activityLog)
    {
        if (!request()->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json($activityLog->load(['subject', 'causer']));
    }

    public function userActivity(Request $request)
    {
        $logs = ActivityLog::byCauser($request->user())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    public function export(Request $request)
    {
        if (!$request->user()->canManageUsers()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = ActivityLog::with(['subject', 'causer'])
            ->orderBy('created_at', 'desc');

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $logs = $query->get();

        // Convert to CSV format
        $csv = "ID,Date,User,Action,Subject,IP Address\n";
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s\n",
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->causer?->name ?? 'System',
                $log->description,
                $log->subject_type ?? 'N/A',
                $log->ip_address
            );
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="activity_logs_' . now()->format('Y-m-d') . '.csv"');
    }
}
