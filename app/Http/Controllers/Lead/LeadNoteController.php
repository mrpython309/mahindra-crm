<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadNoteController extends Controller
{
    public function index(Lead $lead)
    {
        $notes = $lead->notes()->with('creator')->orderBy('is_pinned', 'desc')->orderBy('created_at', 'desc')->get();
        return response()->json($notes);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $validated['created_by'] = Auth::id();
        $lead->notes()->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Note added successfully.']);
        }

        return back()->with('success', 'Note added successfully.');
    }

    public function update(Request $request, LeadNote $note)
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        $note->update($validated);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Note updated successfully.']);
        }
        
        return back()->with('success', 'Note updated successfully.');
    }

    public function togglePin(Request $request, LeadNote $note)
    {
        $note->update(['is_pinned' => !$note->is_pinned]);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Note ' . ($note->is_pinned ? 'pinned' : 'unpinned') . ' successfully.']);
        }
        
        return back()->with('success', 'Note ' . ($note->is_pinned ? 'pinned' : 'unpinned') . ' successfully.');
    }

    public function destroy(Request $request, LeadNote $note)
    {
        $note->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Note deleted successfully.']);
        }
        
        return back()->with('success', 'Note deleted successfully.');
    }
}
