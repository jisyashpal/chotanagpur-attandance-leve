<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('actor')->latest('created_at');

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        if ($request->filled('actor_role')) {
            $query->where('actor_role', $request->actor_role);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', 'like', '%'.$request->subject_type.'%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', '%'.$search.'%');
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(25)->withQueryString();
        $actors = User::orderBy('name')->limit(200)->get(['id', 'name', 'role']);
        $actions = ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity_logs.index', compact('logs', 'actors', 'actions'));
    }
}
