<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('admin.notices.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:5120',
            'link' => 'nullable|url',
            'target_role' => 'required|in:all,student,faculty,staff',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $this->handlePhotoUpload($request->file('file'), 'notice', 'upload/notices');
        }

        Notice::create([
            'title' => $request->title,
            'file_path' => $filePath,
            'link' => $request->link,
            'target_role' => $request->target_role,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.notices.index')->with('success', 'Notice created successfully.');
    }

    public function edit(Notice $notice)
    {
        return view('admin.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,jpeg|max:5120',
            'link' => 'nullable|url',
            'target_role' => 'required|in:all,student,faculty,staff',
        ]);

        if ($request->hasFile('file')) {
            $notice->file_path = $this->handlePhotoUpload($request->file('file'), 'notice', 'upload/notices', $notice->file_path);
        }

        $notice->title = $request->title;
        $notice->link = $request->link;
        $notice->target_role = $request->target_role;
        $notice->is_active = $request->has('is_active');
        $notice->save();

        return redirect()->route('admin.notices.index')->with('success', 'Notice updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        if ($notice->file_path) {
            $this->deleteOldPhoto($notice->file_path);
        }
        $notice->delete();
        return redirect()->route('admin.notices.index')->with('success', 'Notice deleted successfully.');
    }
}
