<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadTagController extends Controller
{
    public function index(Lead $lead)
    {
        $tags = $lead->tags()->with('creator')->orderBy('created_at', 'desc')->get();
        return response()->json($tags);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'tag_name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7', // hex color
        ]);

        $validated['created_by'] = Auth::id();
        $lead->tags()->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tag added successfully.']);
        }

        return back()->with('success', 'Tag added successfully.');
    }

    public function destroy(Request $request, LeadTag $tag)
    {
        $tag->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Tag removed successfully.']);
        }
        
        return back()->with('success', 'Tag removed successfully.');
    }
}
