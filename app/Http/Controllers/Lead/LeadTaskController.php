<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadTaskController extends Controller
{
    public function index(Lead $lead)
    {
        $tasks = $lead->tasks()->with(['assignedUser', 'creator'])->orderBy('due_date', 'asc')->get();
        $users = User::where('employee_status', 'active')->get();
        return response()->json(['tasks' => $tasks, 'users' => $users]);
    }

    public function store(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = Auth::id();
        $lead->tasks()->create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task created successfully.']);
        }

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, LeadTask $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task->update($validated);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task updated successfully.']);
        }
        
        return back()->with('success', 'Task updated successfully.');
    }

    public function updateStatus(Request $request, LeadTask $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);
        return back()->with('success', 'Task status updated successfully.');
    }

    public function destroy(Request $request, LeadTask $task)
    {
        $task->delete();
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
        }
        
        return back()->with('success', 'Task deleted successfully.');
    }
}
