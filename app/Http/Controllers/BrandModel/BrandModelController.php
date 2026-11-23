<?php

namespace App\Http\Controllers\BrandModel;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBrandModelImport;
use App\Models\BrandModel;
use App\Models\Make;
use Illuminate\Http\Request;

class BrandModelController extends Controller
{
    public function index()
    {
        $data = BrandModel::with('make')->latest()->get();
        return view('brand-models.index', compact('data'));
    }

    public function create()
    {
        $makes = Make::where('status', 'active')->orderBy('name')->get();
        return view('brand-models.create', compact('makes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'make_id' => 'required|exists:makes,id',
            'name' => 'required|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        // Check for unique combination of make_id and name
        $exists = BrandModel::where('make_id', $validated['make_id'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'This model already exists for the selected make.'])->withInput();
        }

        BrandModel::create($validated);
        return redirect()->route('brand-models.index')->with('success', 'Brand Model created successfully');
    }

    public function edit(BrandModel $brandModel)
    {
        $makes = Make::where('status', 'active')->orderBy('name')->get();
        return view('brand-models.edit', compact('brandModel', 'makes'));
    }

    public function update(Request $request, BrandModel $brandModel)
    {
        $validated = $request->validate([
            'make_id' => 'required|exists:makes,id',
            'name' => 'required|max:100',
            'status' => 'required|in:active,inactive'
        ]);

        // Check for unique combination of make_id and name (excluding current record)
        $exists = BrandModel::where('make_id', $validated['make_id'])
            ->where('name', $validated['name'])
            ->where('id', '!=', $brandModel->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'This model already exists for the selected make.'])->withInput();
        }

        $brandModel->update($validated);
        return redirect()->route('brand-models.index')->with('success', 'Brand Model updated successfully');
    }

    public function destroy(BrandModel $brandModel)
    {
        $brandModel->delete();
        return back()->with('success', 'Brand Model deleted successfully');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="brand_model_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['make_name', 'name', 'status']);
            fputcsv($handle, ['Mahindra', 'XUV700', 'active']);
            fputcsv($handle, ['Mahindra', 'Scorpio N', 'active']);
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
        $storedPath = $file->store('imports/brand-models');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessBrandModelImport::dispatch($storedPath, uniqid('brand_model_import_', true));
        return redirect()->route('brand-models.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
