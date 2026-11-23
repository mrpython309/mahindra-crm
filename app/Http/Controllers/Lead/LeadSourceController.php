<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadSource;
use App\Models\EnquiryType;

class LeadSourceController extends Controller
{
    public function index() {
        $data = LeadSource::with('enquiryType')->latest()->get();
        return view('lead-sources.index', compact('data'));
    }

    public function create() {
        $enquiryTypes = EnquiryType::all();
        return view('lead-sources.create', compact('enquiryTypes'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'campaign' => 'required|max:100',
            'enquiry_type_id' => 'required|exists:enquiry_types,id'
        ]);
        LeadSource::create($validated);
        return redirect()->route('lead-sources.index')->with('success','Lead Source created');
    }

    public function edit(LeadSource $leadSource) {
        $enquiryTypes = EnquiryType::all();
        return view('lead-sources.edit', compact('leadSource', 'enquiryTypes'));
    }

    public function update(Request $request, LeadSource $leadSource) {
        $validated = $request->validate([
            'campaign' => 'required|max:100',
            'enquiry_type_id' => 'required|exists:enquiry_types,id'
        ]);
        $leadSource->update($validated);
        return redirect()->route('lead-sources.index')->with('success','Lead Source updated');
    }

    public function destroy(LeadSource $leadSource) {
        $leadSource->delete();
        return back()->with('success','Lead Source deleted');
    }
}



