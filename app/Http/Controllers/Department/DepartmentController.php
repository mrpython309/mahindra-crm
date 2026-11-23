<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDepartmentImport;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{

    public function index()
    {
        $departments = Department::orderBy('id', 'desc')->get();
        return view('department.index', compact('departments'));
    }

    public function create()
    {
        return view('department.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
            'status'          => 'required|in:active,inactive',
        ]);

        Department::create($validated);

        return redirect()
            ->route('department.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('department.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name,' . $department->id,
            'status'          => 'required|in:active,inactive',
        ]);

        $department->update($validated);

        return redirect()
            ->route('department.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()
            ->route('department.index')
            ->with('success', 'Department deleted successfully.');
    }

    
    public function updateStatus(Request $request)
    {
        $department = Department::findOrFail($request->id);

        $department->status = $request->status;
        $department->save();

        return response()->json(['success' => true]);
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="department_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['department_name', 'status']);
            fputcsv($handle, ['Sales', 'active']);
            fputcsv($handle, ['Marketing', 'active']);
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
        $storedPath = $file->store('imports/department');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessDepartmentImport::dispatch($storedPath, uniqid('department_import_', true));
        return redirect()->route('department.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }

}
