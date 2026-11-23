<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadStatus;

class LeadStatusController extends Controller
{
    public function index() {
        $data = LeadStatus::all();
        return view('lead-status.index', compact('data'));
    }

    public function create() {
        return view('lead-status.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|in:active,closed'
        ]);
        LeadStatus::create($validated);
        return redirect()->route('lead-statuses.index')->with('success','Lead Stage created');
    }

    public function edit(LeadStatus $leadStatus) {
        return view('lead-status.edit', compact('leadStatus'));
    }

    public function update(Request $request, LeadStatus $leadStatus) {
        $validated = $request->validate([
            'name' => 'required|in:active,closed'
        ]);
        $leadStatus->update($validated);
        return redirect()->route('lead-statuses.index')->with('success','Lead Stage updated');
    }

    public function destroy(LeadStatus $leadStatus) {
        $leadStatus->delete();
        return back()->with('success','Lead Stage deleted');
    }
}

