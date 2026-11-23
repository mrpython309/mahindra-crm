<?php

namespace App\Http\Controllers\FuelType;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFuelTypeImport;
use App\Models\FuelType;
use Illuminate\Http\Request;

class FuelTypeController extends Controller
{
    public function index() {
      $fuelTypes = FuelType::orderBy('name')->paginate(15);
      $activeFuelTypes = FuelType::active()->count();
      $inactiveFuelTypes = FuelType::inactive()->count();
      return view('fuel-types.index', compact('fuelTypes', 'activeFuelTypes', 'inactiveFuelTypes'));
    }

    public function create()
    {
        return view('fuel-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_types',
            'status' => 'required|in:active,inactive',
        ]);

        FuelType::create($validated);

        return redirect()->route('fuel-types.index')
            ->with('success', 'Fuel type created successfully.');
    }

    public function show(FuelType $fuelType)
    {
        return redirect()->route('fuel-types.edit', $fuelType);
    }

    public function edit(FuelType $fuelType)
    {
        return view('fuel-types.edit', compact('fuelType'));
    }

    public function update(Request $request, FuelType $fuelType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:fuel_types,name,' . $fuelType->id,
            'status' => 'required|in:active,inactive',
        ]);

        $fuelType->update($validated);

        return redirect()->route('fuel-types.index')
            ->with('success', 'Fuel type updated successfully.');
    }

    public function destroy(FuelType $fuelType)
    {
        if ($fuelType->vehicleModels()->count() > 0) {
            return back()->with('error', 'Cannot delete fuel type used in vehicle models.');
        }

        $fuelType->delete();

        return redirect()->route('fuel-types.index')
            ->with('success', 'Fuel type deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fuel_type_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            $columns = [
                'name',
                'status',
            ];

            fputcsv($handle, $columns);

            fputcsv($handle, [
                'Petrol',
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

        $storedPath = $file->store('imports/fuel-types');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('fuel_type_import_', true);

        ProcessFuelTypeImport::dispatch($storedPath, $batchId);

        return redirect()->route('fuel-types.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
