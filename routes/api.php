<?php

use App\Http\Controllers\Api\MasterApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CAI LMM Integration API Routes (v1)
|--------------------------------------------------------------------------
|
| Read-only master data and integration endpoints for the CAI LMM project.
| Protected by ValidateApiKey middleware (X-API-KEY header / Bearer token).
|
*/

Route::prefix('v1')->middleware('api.key')->group(function () {
    // Companies & Branches
    Route::get('/companies', [MasterApiController::class, 'companies'])->name('api.v1.companies');
    Route::get('/companies/{company}/branches', [MasterApiController::class, 'branchesByCompany'])->name('api.v1.companies.branches');

    // Departments & Designations
    Route::get('/departments', [MasterApiController::class, 'departments'])->name('api.v1.departments');
    Route::get('/designations', [MasterApiController::class, 'designations'])->name('api.v1.designations');

    // Vehicle Models & Variants
    Route::get('/vehicle-models', [MasterApiController::class, 'vehicleModels'])->name('api.v1.vehicle-models');
    Route::get('/vehicle-models/{model}/variants', [MasterApiController::class, 'variantsByModel'])->name('api.v1.vehicle-models.variants');

    // Enquiry Types & Sources
    Route::get('/enquiry-types', [MasterApiController::class, 'enquiryTypes'])->name('api.v1.enquiry-types');
    Route::get('/enquiry-types/{type}/sources', [MasterApiController::class, 'sourcesByType'])->name('api.v1.enquiry-types.sources');

    // Lead Statuses & Responses
    Route::get('/lead-statuses', [MasterApiController::class, 'leadStatuses'])->name('api.v1.lead-statuses');
    Route::get('/lead-statuses/{status}/responses', [MasterApiController::class, 'responsesByStatus'])->name('api.v1.lead-statuses.responses');

    // Employees (sanitized read-only listing)
    Route::get('/employees', [MasterApiController::class, 'employees'])->name('api.v1.employees');
});
