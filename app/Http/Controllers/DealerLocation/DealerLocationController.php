<?php

namespace App\Http\Controllers\DealerLocation;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDealerLocationImport;
use App\Models\Company;
use App\Models\DealerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealerLocationController extends Controller
{
    private function getCurrentCompanyId(): ?int
    {
        $user = Auth::user();
        if (!$user || $user->hasRole('superadmin')) {
            return null;
        }
        return $user->employee?->company_id ? (int) $user->employee->company_id : null;
    }

    private function ensureCompanyAccess(int $companyId): void
    {
        $currentCompanyId = $this->getCurrentCompanyId();
        if ($currentCompanyId !== null && $currentCompanyId !== $companyId) {
            abort(403, 'You can access only your company records.');
        }
    }

    public function index(Request $request) {
        $currentCompanyId = $this->getCurrentCompanyId();
        $dealerLocations = DealerLocation::with('company')
            ->when($request->name, fn($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->code, fn($q) => $q->where('code', $request->code))
            ->when($request->company_id, fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($currentCompanyId !== null, fn($q) => $q->where('company_id', $currentCompanyId))
            ->paginate(10);

        $companies = Company::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
            ->get();
        return view('dealer-locations.index', compact('dealerLocations', 'companies'));
    }

    public function create() {
      $currentCompanyId = $this->getCurrentCompanyId();
      $companies = Company::active()
          ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
          ->get();
      return view('dealer-locations.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:dealer_locations',
            'company_id' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        $this->ensureCompanyAccess((int) $validated['company_id']);

        DealerLocation::create($validated);

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location created successfully.');
    }

    public function show(DealerLocation $dealerLocation)
    {
        return redirect()->route('dealer-locations.edit', $dealerLocation);
    }

    public function edit(DealerLocation $dealerLocation)
    {
        $this->ensureCompanyAccess((int) $dealerLocation->company_id);
        $currentCompanyId = $this->getCurrentCompanyId();
        $companies = Company::active()
            ->when($currentCompanyId !== null, fn($q) => $q->where('id', $currentCompanyId))
            ->get();
        return view('dealer-locations.edit', compact('dealerLocation', 'companies'));
    }

    public function update(Request $request, DealerLocation $dealerLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:dealer_locations,code,' . $dealerLocation->id,
            'company_id' => 'required|exists:companies,id',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'contact_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        $this->ensureCompanyAccess((int) $dealerLocation->company_id);
        $this->ensureCompanyAccess((int) $validated['company_id']);

        $dealerLocation->update($validated);

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location updated successfully.');
    }

    public function destroy(DealerLocation $dealerLocation)
    {
        $this->ensureCompanyAccess((int) $dealerLocation->company_id);
        if ($dealerLocation->vehicleModels()->count() > 0) {
            return back()->with('error', 'Cannot delete dealer location with vehicle models.');
        }

        $dealerLocation->delete();

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Dealer location deleted successfully.');
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dealer_location_import_sample.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            $columns = [
                'name',
                'code',
                'company_code',
                'address',
                'city',
                'state',
                'country',
                'contact_number',
                'email',
                'status',
            ];
            fputcsv($handle, $columns);
            // Example row: use a company code that exists in your system (e.g. MAH from Companies)
            fputcsv($handle, [
                'Chennai Main Branch',
                'DL001',
                'MAH',
                '123 Gandhi Road',
                'Chennai',
                'Tamil Nadu',
                'India',
                '+91 9876543210',
                'chennai@dealer.com',
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

        $storedPath = $file->store('imports/dealer-locations');
        if ($storedPath === false) {
            return back()->with('error', 'Unable to store uploaded file.');
        }

        $batchId = uniqid('dealer_location_import_', true);
        ProcessDealerLocationImport::dispatch($storedPath, $batchId);

        return redirect()->route('dealer-locations.index')
            ->with('success', 'Import started. You can continue working while we process the file in the background.');
    }
}
