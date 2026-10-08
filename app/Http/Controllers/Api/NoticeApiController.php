<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeApiController extends Controller
{
    /**
     * Get notices relevant to the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        $notices = Notice::where('is_active', true)
            ->where(function($query) use ($role) {
                $query->where('target_role', 'all')
                      ->orWhere('target_role', $role);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedNotices = $notices->map(function($notice) {
            return [
                'id' => $notice->id,
                'title' => $notice->title,
                'file_path' => $notice->file_path ? asset('public/' . $notice->file_path) : null,
                'link' => $notice->link,
                'target_role' => $notice->target_role,
                'created_at' => $notice->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedNotices
        ]);
    }
}
