<?php

namespace App\Http\Controllers\Colour;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessColourImport;
use App\Models\Colour;
use Illuminate\Http\Request;

class ColourController extends Controller
{
    public function index() {
      $colours = Colour::orderBy('name')->paginate(15);
      $activeColours = Colour::active()->count();
      $inactiveColours = Colour::inactive()->count();
      return view('colours.index', compact('colours', 'activeColours', 'inactiveColours'));
    }

    public function create()
    {
        return view('colours.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colours',
            'status' => 'required|in:active,inactive',
        ]);

        Colour::create($validated);

        return redirect()->route('colours.index')
            ->with('success', 'Colour created successfully.');
    }

    public function show(Colour $colour)
    {
        return view('colours.show', compact('colour'));
    }

    public function edit(Colour $colour)
    {
        return view('colours.edit', compact('colour'));
    }

    public function update(Request $request, Colour $colour)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:colours,name,' . $colour->id,
            'status' => 'required|in:active,inactive',
        ]);

        $colour->update($validated);

        return redirect()->route('colours.index')
            ->with('success', 'Colour updated successfully.');
    }

    public function destroy(Colour $colour)
    {
        $colour->delete();

        return redirect()->route('colours.index')
            ->with('success', 'Colour deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="colour_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            $columns = [
                'name',
                'status',
            ];

            fputcsv($handle, $columns);

            fputcsv($handle, [
                'Red',
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

        $storedPath = $file->store('imports/colours');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('colour_import_', true);

        ProcessColourImport::dispatch($storedPath, $batchId);

        return redirect()->route('colours.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
