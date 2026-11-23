<?php

namespace App\Http\Controllers\Variant;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVariantImport;
use App\Models\Variant;
use App\Models\VehicleModel;
use App\Models\FuelType;
use App\Models\Transmission;
use App\Models\Colour;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    public function index(Request $request)
    {
        $variants = Variant::with(['vehicleModel', 'fuelTypes', 'transmissions', 'colours'])
            ->when($request->name, fn($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->vehicle_model_id, fn($q) => $q->where('vehicle_model_id', $request->vehicle_model_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(10);

        $activeVariants = Variant::active()->count();
        $inactiveVariants = Variant::inactive()->count();
        $vehicleModels = VehicleModel::active()->get();

        return view('variants.index', compact('variants', 'activeVariants', 'inactiveVariants', 'vehicleModels'));
    }

    public function create()
    {
        $vehicleModels = VehicleModel::with(['fuelTypes', 'transmissions'])->active()->get();
        return view('variants.create', compact('vehicleModels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'fuel_type_ids' => 'required|array|min:1',
            'fuel_type_ids.*' => 'exists:fuel_types,id',
            'transmission_ids' => 'required|array|min:1',
            'transmission_ids.*' => 'exists:transmissions,id',
            'colour_ids' => 'nullable|array',
            'colour_ids.*' => 'exists:colours,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Validate that fuel types belong to the selected vehicle model
        $vehicleModel = VehicleModel::with(['fuelTypes', 'transmissions'])->find($validated['vehicle_model_id']);
        $availableFuelTypeIds = $vehicleModel->fuelTypes->pluck('id')->toArray();
        $availableTransmissionIds = $vehicleModel->transmissions->pluck('id')->toArray();

        foreach ($validated['fuel_type_ids'] as $fuelTypeId) {
            if (!in_array($fuelTypeId, $availableFuelTypeIds)) {
                return back()->withErrors(['fuel_type_ids' => 'Selected fuel types must belong to the chosen vehicle model.']);
            }
        }

        foreach ($validated['transmission_ids'] as $transmissionId) {
            if (!in_array($transmissionId, $availableTransmissionIds)) {
                return back()->withErrors(['transmission_ids' => 'Selected transmissions must belong to the chosen vehicle model.']);
            }
        }

        $variant = Variant::create([
            'name' => $validated['name'],
            'vehicle_model_id' => $validated['vehicle_model_id'],
            'status' => $validated['status']
        ]);

        $variant->fuelTypes()->attach($validated['fuel_type_ids']);
        $variant->transmissions()->attach($validated['transmission_ids']);

        if (isset($validated['colour_ids']) && !empty($validated['colour_ids'])) {
            $variant->colours()->attach($validated['colour_ids']);
        }

        return redirect()->route('variants.index')
            ->with('success', 'Variant created successfully.');
    }

    public function show(Variant $variant)
    {
        return redirect()->route('variants.edit', $variant);
    }

    public function edit(Variant $variant)
    {
        $vehicleModels = VehicleModel::with(['fuelTypes', 'transmissions'])->active()->get();
        $variant->load(['vehicleModel', 'fuelTypes', 'transmissions', 'colours']);
        return view('variants.edit', compact('variant', 'vehicleModels'));
    }

    public function update(Request $request, Variant $variant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'fuel_type_ids' => 'required|array|min:1',
            'fuel_type_ids.*' => 'exists:fuel_types,id',
            'transmission_ids' => 'required|array|min:1',
            'transmission_ids.*' => 'exists:transmissions,id',
            'colour_ids' => 'nullable|array',
            'colour_ids.*' => 'exists:colours,id',
            'status' => 'required|in:active,inactive',
        ]);

        // Validate that fuel types belong to the selected vehicle model
        $vehicleModel = VehicleModel::with(['fuelTypes', 'transmissions'])->find($validated['vehicle_model_id']);
        $availableFuelTypeIds = $vehicleModel->fuelTypes->pluck('id')->toArray();
        $availableTransmissionIds = $vehicleModel->transmissions->pluck('id')->toArray();

        foreach ($validated['fuel_type_ids'] as $fuelTypeId) {
            if (!in_array($fuelTypeId, $availableFuelTypeIds)) {
                return back()->withErrors(['fuel_type_ids' => 'Selected fuel types must belong to the chosen vehicle model.']);
            }
        }

        foreach ($validated['transmission_ids'] as $transmissionId) {
            if (!in_array($transmissionId, $availableTransmissionIds)) {
                return back()->withErrors(['transmission_ids' => 'Selected transmissions must belong to the chosen vehicle model.']);
            }
        }

        $variant->update([
            'name' => $validated['name'],
            'vehicle_model_id' => $validated['vehicle_model_id'],
            'status' => $validated['status']
        ]);

        $variant->fuelTypes()->sync($validated['fuel_type_ids']);
        $variant->transmissions()->sync($validated['transmission_ids']);

        if (isset($validated['colour_ids']) && !empty($validated['colour_ids'])) {
            $variant->colours()->sync($validated['colour_ids']);
        } else {
            $variant->colours()->sync([]);
        }

        return redirect()->route('variants.index')
            ->with('success', 'Variant updated successfully.');
    }

    public function destroy(Variant $variant)
    {
        $variant->fuelTypes()->detach();
        $variant->transmissions()->detach();
        $variant->colours()->detach();
        $variant->delete();

        return redirect()->route('variants.index')
            ->with('success', 'Variant deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="variant_import_sample.csv"',
        ];
        $callback = function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['vehicle_model_name', 'name', 'fuel_type_names', 'transmission_names', 'colour_names', 'status']);
            fputcsv($handle, ['XUV700', 'AX7 Luxury', 'Petrol,Diesel', 'Automatic,Manual', 'Red,White', 'active']);
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
        $storedPath = $file->store('imports/variants');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }
        ProcessVariantImport::dispatch($storedPath, uniqid('variant_import_', true));
        return redirect()->route('variants.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }

    // AJAX method to get fuel types based on vehicle model
    public function getFuelTypes(Request $request)
    {
        $vehicleModel = VehicleModel::with('fuelTypes')->find($request->vehicle_model_id);

        if (!$vehicleModel) {
            return response()->json([
                'success' => false,
                'fuel_types' => [],
                'message' => 'Vehicle model not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'fuel_types' => $vehicleModel->fuelTypes->map(function($fuelType) {
                return [
                    'id' => $fuelType->id,
                    'name' => $fuelType->name
                ];
            }),
            'model_name' => $vehicleModel->name,
            'message' => 'Fuel types loaded successfully'
        ]);
    }

    // AJAX method to get transmissions based on vehicle model
    public function getTransmissions(Request $request)
    {
        $vehicleModel = VehicleModel::with('transmissions')->find($request->vehicle_model_id);

        if (!$vehicleModel) {
            return response()->json([
                'success' => false,
                'transmissions' => [],
                'message' => 'Vehicle model not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'transmissions' => $vehicleModel->transmissions->map(function($transmission) {
                return [
                    'id' => $transmission->id,
                    'name' => $transmission->name
                ];
            }),
            'model_name' => $vehicleModel->name,
            'message' => 'Transmissions loaded successfully'
        ]);
    }

    // AJAX method to get colours based on vehicle model (like fuel & transmission)
    public function getColours(Request $request)
    {
        $vehicleModel = VehicleModel::with('colours')->find($request->vehicle_model_id);

        if (!$vehicleModel) {
            return response()->json([
                'success' => false,
                'colours' => [],
                'message' => 'Vehicle model not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'colours' => $vehicleModel->colours->map(function($colour) {
                return [
                    'id' => $colour->id,
                    'name' => $colour->name
                ];
            }),
            'model_name' => $vehicleModel->name,
            'message' => 'Colours loaded successfully'
        ]);
    }
}
