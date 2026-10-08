<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use \App\Models\AcademicSession;
use \App\Models\Course;
use \App\Models\Semester;
use App\Services\FcmService;

class NotificationController extends Controller
{
    public function create()
    {
        $users = User::with(['studentProfile', 'devices'])
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get();

        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $courses = Course::orderBy('name')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('admin.notifications.create', compact('users', 'sessions', 'courses', 'semesters'));
    }

    public function send(Request $request, FcmService $fcmService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            'send_to_all' => 'nullable|boolean',
        ]);

        $title = $request->title;
        $body = $request->message;

        if ($request->send_to_all) {
            $users = User::whereHas('devices', function($query) {
                $query->whereNotNull('fcm_token');
            })->with('devices')->get();

            $tokens = [];
            foreach ($users as $user) {
                foreach ($user->devices as $device) {
                    if ($device->fcm_token) {
                        $tokens[] = $device->fcm_token;
                    }
                }
            }
            
            if (!empty($tokens)) {
                $fcmService->sendToMultiple($tokens, $title, $body);
            }

            // Save for all users or just generic broadcast log
            Notification::create([
                'title' => $title,
                'message' => $body,
                'user_id' => null // broadcast
            ]);
            
            return back()->with('success', 'Notification sent to all users.');
        } 
        
        if ($request->has('user_ids') && count($request->user_ids) > 0) {
            $users = User::whereIn('id', $request->user_ids)
                ->whereHas('devices', function($query) {
                    $query->whereNotNull('fcm_token');
                })
                ->with('devices')
                ->get();
            
            if ($users->isNotEmpty()) {
                $tokens = [];
                foreach ($users as $user) {
                    foreach ($user->devices as $device) {
                        if ($device->fcm_token) {
                            $tokens[] = $device->fcm_token;
                        }
                    }
                }
                
                if (!empty($tokens)) {
                    $fcmService->sendToMultiple($tokens, $title, $body);
                }
                
                foreach ($users as $user) {
                    Notification::create([
                        'title' => $title,
                        'message' => $body,
                        'user_id' => $user->id
                    ]);
                }
                
                return back()->with('success', 'Notification sent to ' . count($users) . ' selected user(s) successfully.');
            }
            return back()->with('error', 'Selected users do not have active device tokens.');
        }

        return back()->with('error', 'Please select at least one user or "Send to All".');
    }
}
