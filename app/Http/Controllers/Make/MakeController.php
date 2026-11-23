<?php

namespace App\Http\Controllers\Make;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMakeImport;
use App\Models\Make;
use Illuminate\Http\Request;

class MakeController extends Controller
{
    public function index()
    {
        $data = Make::latest()->get();
        return view('makes.index', compact('data'));
    }

    public function create()
    {
        return view('makes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:makes,name|max:100',
            'status' => 'required|in:active,inactive'
        ]);
        Make::create($validated);
        return redirect()->route('makes.index')->with('success', 'Make created successfully');
    }

    public function edit(Make $make)
    {
        return view('makes.edit', compact('make'));
    }

    public function update(Request $request, Make $make)
    {
        $validated = $request->validate([
            'name' => 'required|max:100|unique:makes,name,' . $make->id,
            'status' => 'required|in:active,inactive'
        ]);
        $make->update($validated);
        return redirect()->route('makes.index')->with('success', 'Make updated successfully');
    }

    public function destroy(Make $make)
    {
        $make->delete();
        return back()->with('success', 'Make deleted successfully');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="make_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'status']);
            fputcsv($handle, ['Mahindra', 'active']);
            fputcsv($handle, ['Tata', 'active']);
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
        $storedPath = $file->store('imports/makes');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessMakeImport::dispatch($storedPath, uniqid('make_import_', true));
        return redirect()->route('makes.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
