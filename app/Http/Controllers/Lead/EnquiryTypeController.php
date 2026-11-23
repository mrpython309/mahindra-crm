<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EnquiryType;

class EnquiryTypeController extends Controller
{
    public function index() {
        $data = EnquiryType::latest()->get();
        return view('enquiry-types.index', compact('data'));
    }

    public function create() {
        return view('enquiry-types.create');
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|unique:enquiry_types,name|max:100'
        ]);
        EnquiryType::create($validated);
        return redirect()->route('enquiry-types.index')->with('success','Enquiry Type created');
    }

    public function edit(EnquiryType $enquiryType) {
        return view('enquiry-types.edit', compact('enquiryType'));
    }

    public function update(Request $request, EnquiryType $enquiryType) {
        $validated = $request->validate([
            'name' => 'required|max:100|unique:enquiry_types,name,'.$enquiryType->id
        ]);
        $enquiryType->update($validated);
        return redirect()->route('enquiry-types.index')->with('success','Enquiry Type updated');
    }

    public function destroy(EnquiryType $enquiryType) {
        $enquiryType->delete();
        return back()->with('success','Enquiry Type deleted');
    }
}


