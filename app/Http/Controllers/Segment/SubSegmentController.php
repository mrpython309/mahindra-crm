<?php
namespace App\Http\Controllers\Segment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSubSegmentImport;
use App\Models\Segment;
use App\Models\SubSegment;
use Illuminate\Http\Request;

class SubSegmentController extends Controller
{
    public function index()
    {
        $subsegments = SubSegment::with('segment')
            ->orderBy('id', 'desc')
            ->get();

        return view('subsegment.index', compact('subsegments'));
    }

    public function create()
    {
        $segments = Segment::where('status', 'active')->get();
        return view('subsegment.create', compact('segments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'segment_id'      => 'required|exists:segments,id',
            'subsegment_name' => 'required|string|max:255|unique:sub_segments,subsegment_name,NULL,id,segment_id,' . $request->segment_id,
            'status'          => 'required|in:active,inactive',
        ]);

        SubSegment::create($validated);

        return redirect()
            ->route('subsegment.index')
            ->with('success', 'Sub Segment created successfully.');
    }

    public function show(SubSegment $subsegment)
    {
        return redirect()->route('subsegment.edit', $subsegment);
    }

    public function edit(SubSegment $subsegment)
    {
        $segments = Segment::where('status', 'active')->get();
        return view('subsegment.edit', compact('subsegment', 'segments'));
    }

    public function update(Request $request, SubSegment $subsegment)
    {
        $validated = $request->validate([
            'segment_id'      => 'required|exists:segments,id',
            'subsegment_name' => 'required|string|max:255|unique:sub_segments,subsegment_name,' . $subsegment->id . ',id,segment_id,' . $request->segment_id,
            'status'          => 'required|in:active,inactive',
        ]);

        $subsegment->update($validated);

        return redirect()
            ->route('subsegment.index')
            ->with('success', 'Sub Segment updated successfully.');

    }

    public function destroy(SubSegment $subsegment)
    {
        $subsegment->delete();

        return redirect()
            ->route('subsegment.index')
            ->with('success', 'Sub Segment deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $department = SubSegment::findOrFail($request->id);

        $department->status = $request->status;
        $department->save();

        return response()->json(['success' => true]);
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="subsegment_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['segment_code', 'subsegment_name', 'status']);
            fputcsv($handle, ['SUV', 'Compact SUV', 'active']);
            fputcsv($handle, ['SUV', 'Full Size SUV', 'active']);
            fclose($handle);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:10240']);
        $file = $request->file('file');
        if (!$file->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }
        $storedPath = $file->store('imports/subsegment');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessSubSegmentImport::dispatch($storedPath, uniqid('subsegment_import_', true));
        return redirect()->route('subsegment.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }

}
