<?php

namespace App\Http\Controllers\VehicleModel;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessVehicleModelImport;
use App\Models\VehicleModel;
use App\Models\Segment;
use App\Models\Transmission;
use App\Models\FuelType;
use App\Models\Colour;
use App\Models\Company;
use App\Models\DealerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VehicleModelController extends Controller
{
    private function getCurrentCompanyId(): ?int
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('superadmin')) {
            return null;
        }
        return $user->employee?->company_id ? (int) $user->employee->company_id : null;
    }

    private function ensureModelCompanyAccess(VehicleModel $vehicleModel): void
    {
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId === null) {
            return;
        }

        $hasAccess = $vehicleModel->companies()->where('companies.id', $currentCompanyId)->exists();
        if (!$hasAccess) {
            abort(403, 'You can access only your company records.');
        }
    }

    public function index(Request $request) {
      $currentCompanyId = $this->getCurrentCompanyId();
      $models = VehicleModel::with(['segment', 'transmissions', 'fuelTypes', 'colours', 'companies', 'dealerLocations'])
          ->when($request->name, fn($q) => $q->where('name', 'like', '%'.$request->name.'%'))
          ->when($request->segment_id, fn($q) => $q->where('segment_id', $request->segment_id))
          ->when($request->fuel_type_id, fn($q) => $q->whereHas('fuelTypes', fn($query) => $query->where('fuel_type_id', $request->fuel_type_id)))
          ->when($request->company_id, fn($q) => $q->whereHas('companies', fn($query) => $query->where('company_id', $request->company_id)))
          ->when($currentCompanyId !== null, fn($q) => $q->whereHas('companies', fn($query) => $query->where('company_id', $currentCompanyId)))
          ->orderBy('name')
          ->paginate(10);

      $activeModels = VehicleModel::active()
          ->when($currentCompanyId !== null, fn($q) => $q->whereHas('companies', fn($query) => $query->where('company_id', $currentCompanyId)))
          ->count();
      $inactiveModels = VehicleModel::inactive()
          ->when($currentCompanyId !== null, fn($q) => $q->whereHas('companies', fn($query) => $query->where('company_id', $currentCompanyId)))
          ->count();

      $segments = Segment::active()->get();
      $fuelTypes = FuelType::active()->get();
      $companies = Company::active()
          ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
          ->get();

      return view('vehicle-models.index', compact('models', 'activeModels', 'inactiveModels', 'segments', 'fuelTypes', 'companies'));
    }

    // create
    public function create() {
        $currentCompanyId = $this->getCurrentCompanyId();
        $segments = Segment::active()->get();
        $transmissions = Transmission::active()->get();
        $fuelTypes = FuelType::active()->get();
        $colours = Colour::active()->get();
        $dealerLocations = DealerLocation::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('company_id', $currentCompanyId))
            ->get();
        $companies = Company::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
            ->get();
        $existingModels = VehicleModel::active()->get(['id', 'name']);

        return view('vehicle-models.create', compact('segments', 'transmissions', 'fuelTypes', 'colours', 'dealerLocations', 'companies', 'existingModels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_models,name',
            'segment_id' => 'required|exists:segments,id',
            'transmission_ids' => 'required|array|min:1',
            'transmission_ids.*' => 'exists:transmissions,id',
            'fuel_type_ids' => 'required|array|min:1',
            'fuel_type_ids.*' => 'exists:fuel_types,id',
            'colour_ids' => 'required|array|min:1',
            'colour_ids.*' => 'exists:colours,id',
            'dealer_location_ids' => 'required|array|min:1',
            'dealer_location_ids.*' => 'exists:dealer_locations,id',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'status' => 'required|in:active,inactive',
        ]);
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null) {
            $validated['company_ids'] = [(int) $currentCompanyId];
            $validated['dealer_location_ids'] = DealerLocation::whereIn('id', (array) $validated['dealer_location_ids'])
                ->where('company_id', $currentCompanyId)
                ->pluck('id')
                ->toArray();
        }

        $vehicleModel = VehicleModel::create([
            'name' => $validated['name'],
            'segment_id' => $validated['segment_id'],
            'status' => $validated['status']
        ]);

        // Attach many-to-many relationships
        $vehicleModel->transmissions()->attach($validated['transmission_ids']);
        $vehicleModel->fuelTypes()->attach($validated['fuel_type_ids']);
        $vehicleModel->colours()->attach($validated['colour_ids']);
        $vehicleModel->dealerLocations()->attach($validated['dealer_location_ids']);
        $vehicleModel->companies()->attach($validated['company_ids']);

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Vehicle model created successfully.');
    }

    public function show(VehicleModel $vehicleModel)
    {
        $this->ensureModelCompanyAccess($vehicleModel);
        $vehicleModel->load(['companies', 'dealerLocations', 'segment', 'transmissions', 'fuelTypes', 'colours']);
        return view('vehicle-models.show', compact('vehicleModel'));
    }

    public function edit(VehicleModel $vehicleModel)
    {
        $this->ensureModelCompanyAccess($vehicleModel);
        $currentCompanyId = $this->getCurrentCompanyId();
        $segments = Segment::active()->get();
        $transmissions = Transmission::active()->get();
        $fuelTypes = FuelType::active()->get();
        $colours = Colour::active()->get();
        $companies = Company::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
            ->get();
        $dealerLocations = DealerLocation::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('company_id', $currentCompanyId))
            ->get();

        return view('vehicle-models.edit', compact(
            'vehicleModel', 'segments', 'transmissions',
            'fuelTypes', 'colours', 'companies', 'dealerLocations'
        ));
    }

    public function update(Request $request, VehicleModel $vehicleModel)
    {
        $this->ensureModelCompanyAccess($vehicleModel);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_models,name,' . $vehicleModel->id,
            'segment_id' => 'required|exists:segments,id',
            'transmission_ids' => 'required|array|min:1',
            'transmission_ids.*' => 'exists:transmissions,id',
            'fuel_type_ids' => 'required|array|min:1',
            'fuel_type_ids.*' => 'exists:fuel_types,id',
            'colour_ids' => 'required|array|min:1',
            'colour_ids.*' => 'exists:colours,id',
            'dealer_location_ids' => 'required|array|min:1',
            'dealer_location_ids.*' => 'exists:dealer_locations,id',
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'status' => 'required|in:active,inactive',
        ]);
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null) {
            $validated['company_ids'] = [(int) $currentCompanyId];
            $validated['dealer_location_ids'] = DealerLocation::whereIn('id', (array) $validated['dealer_location_ids'])
                ->where('company_id', $currentCompanyId)
                ->pluck('id')
                ->toArray();
        }

        $vehicleModel->update([
            'name' => $validated['name'],
            'segment_id' => $validated['segment_id'],
            'status' => $validated['status']
        ]);

        // Sync many-to-many relationships
        $vehicleModel->transmissions()->sync($validated['transmission_ids']);
        $vehicleModel->fuelTypes()->sync($validated['fuel_type_ids']);
        $vehicleModel->colours()->sync($validated['colour_ids']);
        $vehicleModel->dealerLocations()->sync($validated['dealer_location_ids']);
        $vehicleModel->companies()->sync($validated['company_ids']);

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Vehicle model updated successfully.');
    }

    public function destroy(VehicleModel $vehicleModel)
    {
        $this->ensureModelCompanyAccess($vehicleModel);
        $vehicleModel->delete();

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Vehicle model deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="vehicle_model_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            $columns = [
                'name',
                'segment_code',
                'status',
                'transmission_names',
                'fuel_type_names',
                'colour_names',
                'company_codes',
                'dealer_location_codes',
            ];

            fputcsv($handle, $columns);

            fputcsv($handle, [
                'XUV700',
                'SUV',
                'active',
                'Automatic,Manual',
                'Petrol,Diesel',
                'Red,White,Black',
                'MAH',
                'DL001',
            ]);

            fputcsv($handle, [
                'Scorpio N',
                'SUV',
                'active',
                'Automatic',
                'Diesel',
                'Silver',
                'MAH',
                'DL001,DL002',
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

        $storedPath = $file->store('imports/vehicle-models');

        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('vehicle_model_import_', true);

        ProcessVehicleModelImport::dispatch($storedPath, $batchId);

        return redirect()->route('vehicle-models.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }

    /**
     * Get fuel types for a specific vehicle model
     */
    public function getFuelTypes(VehicleModel $vehicleModel)
    {
        try {
            $this->ensureModelCompanyAccess($vehicleModel);
            // Debug logging
            Log::info('Getting fuel types for vehicle model', ['model_id' => $vehicleModel->id, 'model_name' => $vehicleModel->name]);

            $fuelTypes = $vehicleModel->fuelTypes()->get(['id', 'name']);

            Log::info('Fuel types found', ['count' => $fuelTypes->count(), 'fuel_types' => $fuelTypes->toArray()]);
            
            return response()->json([
                'success' => true,
                'fuelTypes' => $fuelTypes,
                'model' => ['id' => $vehicleModel->id, 'name' => $vehicleModel->name],
                'message' => 'Fuel types retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving fuel types', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fuel types',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
