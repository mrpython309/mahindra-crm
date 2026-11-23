<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LeadAttachmentController extends Controller
{
    private const ALLOWED_TITLES = [
        'Aadhar',
        'PAN',
        'ID Proof',
        'Address Proof',
        'Others',
    ];

    public function store(Request $request, Lead $lead)
    {
        $request->validate([
            'title' => ['required', Rule::in(self::ALLOWED_TITLES)],
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'title.in' => 'Please select a valid attachment title.',
            'file.mimes' => 'Unsupported file type. Allowed types: JPG, JPEG, PNG, PDF.',
            'file.max' => 'Attachment size must not exceed 2MB.',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('lead_attachments', $fileName, 'public');

            $lead->attachments()->create([
                'title' => $request->title,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_type' => $file->getMimeType(),
                'uploaded_by' => Auth::id(),
            ]);

            // Log activity
            $lead->activities()->create([
                'activity_type' => 'attachment_added',
                'title' => 'Attachment Added',
                'description' => 'File "' . $request->title . '" was uploaded by ' . Auth::user()->name,
                'activity_date' => now(),
                'created_by' => Auth::id(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Attachment uploaded successfully.']);
            }

            return back()->with('success', 'Attachment uploaded successfully.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'File upload failed.']);
        }

        return back()->with('error', 'File upload failed.');
    }

    public function download(LeadAttachment $attachment)
    {
        if (Storage::disk('public')->exists($attachment->file_path)) {
            return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
        }

        return back()->with('error', 'File not found.');
    }

    public function destroy(Request $request, LeadAttachment $attachment)
    {
        $lead = $attachment->lead;

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $fileName = $attachment->title;
        $attachment->delete();

        // Log activity
        $lead->activities()->create([
            'activity_type' => 'attachment_deleted',
            'title' => 'Attachment Deleted',
            'description' => 'File "' . $fileName . '" was deleted by ' . Auth::user()->name,
            'activity_date' => now(),
            'created_by' => Auth::id(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Attachment deleted successfully.']);
        }

        return back()->with('success', 'Attachment deleted successfully.');
    }
}
