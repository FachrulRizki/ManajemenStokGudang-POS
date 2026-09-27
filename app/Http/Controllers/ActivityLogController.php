<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('module', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs    = $query->latest()->paginate(20)->withQueryString();
        $users   = User::orderBy('name')->get();
        $modules = ActivityLog::select('module')->distinct()->orderBy('module')->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity-logs.index', compact('logs', 'users', 'modules', 'actions'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        return view('activity-logs.show', compact('activityLog'));
    }

    public function clearOld(Request $request)
    {
        $days = $request->input('days', 90);
        $count = ActivityLog::where('created_at', '<', now()->subDays($days))->count();
        ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        ActivityLog::log('delete', 'activity_logs', "Hapus {$count} log aktivitas lama (lebih dari {$days} hari)");

        return back()->with('success', "{$count} log aktivitas lama berhasil dihapus.");
    }
}
