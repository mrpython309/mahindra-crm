<?php

namespace App\Http\Controllers\Segment;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSegmentImport;
use App\Models\Segment;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    public function index(Request $request)
    {
        $segments = Segment::orderBy('name')
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                           ->orWhere('code', 'like', "%{$search}%");
            })
            ->paginate(15);

        $activeSegments = Segment::active()->count();
        $inactiveSegments = Segment::inactive()->count();

        return view('segments.index', compact('segments', 'activeSegments', 'inactiveSegments'));
    }

    public function create()
    {
        return view('segments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:segments,code|max:10|regex:/^[A-Z0-9]+$/',
            'name' => 'required|unique:segments,name|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        Segment::create($validated);

        return redirect()->route('segments.index')
            ->with('success', 'Segment created successfully!');
    }

    public function show(Segment $segment)
    {
        return redirect()->route('segments.edit', $segment);
    }

    public function edit(Segment $segment)
    {
        return view('segments.edit', compact('segment'));
    }

    public function update(Request $request, Segment $segment)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:segments,code,' . $segment->id,
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $segment->update($validated);

        return redirect()->route('segments.index')
            ->with('success', 'Segment updated successfully.');
    }

    public function destroy(Segment $segment)
    {
        // Check if used in vehicle models
        if ($segment->vehicleModels()->count() > 0) {
            return back()->with('error', 'Cannot delete segment used in vehicle models.');
        }

        $segment->delete();

        return redirect()->route('segments.index')
            ->with('success', 'Segment deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="segment_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            $columns = [
                'name',
                'code',
                'status',
            ];

            fputcsv($handle, $columns);

            fputcsv($handle, [
                'SUV',
                'SUV',
                'active',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');

        if (!$file->isValid()) {
            return back()->with('error', 'Uploaded file is not valid.');
        }

        $storedPath = $file->store('imports/segments');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('segment_import_', true);

        ProcessSegmentImport::dispatch($storedPath, $batchId);

        return redirect()->route('segments.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
