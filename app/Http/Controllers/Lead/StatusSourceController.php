<?php
namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\LeadStatus;
use App\Models\StatusSource;
use Illuminate\Http\Request;

class StatusSourceController extends Controller
{
    public function index()
    {
        $data = StatusSource::with(['leadStatus'])->get();
        return view('status-source.index', compact('data'));
    }

    public function create()
    {
        $leadStatuses = LeadStatus::all();
        return view('status-source.create', compact('leadStatuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:255',
            'lead_status_id' => 'required|exists:lead_statuses,id',
        ]);
        StatusSource::create($validated);
        return redirect()->route('status-sources.index')->with('success', 'Lead Response created');
    }

    public function show(StatusSource $statusSource)
    {
        $statusSource->load('leadStatus');
        return view('status-source.show', compact('statusSource'));
    }

    public function destroy(StatusSource $statusSource)
    {
        $statusSource->delete();
        return back()->with('success', 'Lead Response deleted');
    }

    public function edit(StatusSource $statusSource)
    {
        $leadStatuses = LeadStatus::all();

        return view('status-source.edit', compact(
            'statusSource',
            'leadStatuses'
        ));

    }

    public function update(Request $request, StatusSource $statusSource)
    {
        $validated = $request->validate([
            'response' => 'required|string|max:255',
            'lead_status_id' => 'required|exists:lead_statuses,id',
        ]);

        $statusSource->update($validated);

        return redirect()
            ->route('status-sources.index')
            ->with('success', 'Lead Response updated');
    }

}
