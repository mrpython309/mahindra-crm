<?php
namespace App\Http\Controllers\Designation;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDesignationImport;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::orderBy('id', 'desc')->get();

        return view('designation.index', compact('designations'));
    }

    public function create()
    {
        return view('designation.create');
    }

    public function edit(Designation $designation)
    {
        return view('designation.edit', compact('designation'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'designation_name' => 'required|string|max:255|unique:designations,designation_name',
            'status'           => 'required|in:active,inactive',
        ]);

        Designation::create($validated);

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation created successfully.');
    }

    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'designation_name' => 'required|string|max:255|unique:designations,designation_name,' . $designation->id,
            'status'           => 'required|in:active,inactive',
        ]);

        $designation->update($validated);

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $designation->delete();

        return redirect()
            ->route('designation.index')
            ->with('success', 'Designation deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $designation = Designation::findOrFail($request->id);

        $designation->status = $request->status;
        $designation->save();

        return response()->json(['success' => true]);
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="designation_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['designation_name', 'status']);
            fputcsv($handle, ['Manager', 'active']);
            fputcsv($handle, ['Executive', 'active']);
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
        $storedPath = $file->store('imports/designation');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessDesignationImport::dispatch($storedPath, uniqid('designation_import_', true));
        return redirect()->route('designation.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }

}
