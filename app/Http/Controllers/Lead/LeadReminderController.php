<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadReminderController extends Controller
{
    public function index(Lead $lead)
    {
        $reminders = $lead->reminders()->with('creator')->orderBy('reminder_date', 'asc')->get();
        return response()->json($reminders);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_date' => 'required|date',
        ]);

        $validated['created_by'] = Auth::id();
        $lead->reminders()->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reminder created successfully.']);
        }

        return back()->with('success', 'Reminder created successfully.');
    }

    public function update(Request $request, LeadReminder $reminder)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reminder_date' => 'required|date',
        ]);

        $reminder->update($validated);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reminder updated successfully.']);
        }
        
        return back()->with('success', 'Reminder updated successfully.');
    }

    public function destroy(Request $request, LeadReminder $reminder)
    {
        $reminder->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Reminder deleted successfully.']);
        }
        
        return back()->with('success', 'Reminder deleted successfully.');
    }
}
