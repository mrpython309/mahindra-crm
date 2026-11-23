<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadActivityController extends Controller
{
    public function index(Lead $lead)
    {
        $activities = $lead->activities()->with('creator')->orderBy('activity_date', 'desc')->get();
        return response()->json($activities);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'activity_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_date' => 'required|date',
        ]);

        $validated['created_by'] = Auth::id();
        $lead->activities()->create($validated);

        return back()->with('success', 'Activity logged successfully.');
    }

    public function destroy(LeadActivity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Activity deleted successfully.');
    }
}
