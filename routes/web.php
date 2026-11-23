<?php

use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\DealerLocation\DealerLocationController;
use App\Http\Controllers\Department\DepartmentController;
use App\Http\Controllers\Designation\DesignationController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\FuelType\FuelTypeController;
use App\Http\Controllers\Colour\ColourController;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Lead\DuplicateLeadController;
use App\Http\Controllers\Lead\EnquiryTypeController;
use App\Http\Controllers\Lead\LeadSourceController;
use App\Http\Controllers\Lead\LeadStatusController;
use App\Http\Controllers\Lead\LeadTypeController;
use App\Http\Controllers\Lead\StatusSourceController;
use App\Http\Controllers\Make\MakeController;
use App\Http\Controllers\BrandModel\BrandModelController;
use App\Http\Controllers\Lead\LeadAttachmentController;
use App\Http\Controllers\Lead\LeadCallController;
use App\Http\Controllers\Lead\LeadActivityController;
use App\Http\Controllers\Lead\LeadNoteController;
use App\Http\Controllers\Lead\LeadTaskController;
use App\Http\Controllers\Lead\LeadReminderController;
use App\Http\Controllers\Lead\LeadTagController;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Segment\SegmentController;
use App\Http\Controllers\Segment\SubSegmentController;
use App\Http\Controllers\Transmission\TransmissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\VehicleModel\VehicleModelController;
use App\Http\Controllers\Variant\VariantController;
use App\Http\Controllers\Reports\ReportController;
use Illuminate\Support\Facades\Route;

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// authentication
Route::get('/login', [LoginBasic::class, 'index'])->name('login');
Route::post('/login', [LoginBasic::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');
// Registration is disabled; no register route
Route::get('/auth/forgot-password-basic', [ForgotPasswordBasic::class, 'index'])->name('auth-reset-password-basic');

Route::middleware(['auth'])->group(function () {
  // Dashboard
  Route::get('/', [Analytics::class, 'index'])->name('dashboard');

  // ========== COMPANY MASTER ==========
  Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/', [CompanyController::class, 'index'])->name('index')->middleware('permission:companies:view');
    Route::get('/create', [CompanyController::class, 'create'])->name('create')->middleware('permission:companies:create');
    Route::post('/', [CompanyController::class, 'store'])->name('store')->middleware('permission:companies:create');
    Route::get('/import/template', [CompanyController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:companies:create');
    Route::post('/import', [CompanyController::class, 'import'])
      ->name('import')
      ->middleware('permission:companies:create');
    Route::get('/{company}', [CompanyController::class, 'show'])->name('show')->middleware('permission:companies:view');
    Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit')->middleware('permission:companies:edit');
    Route::put('/{company}', [CompanyController::class, 'update'])->name('update')->middleware('permission:companies:edit');
    Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy')->middleware('permission:companies:delete');
  });

  // =============== Roles And Permisson Routes ================
  Route::prefix('roles')->name('roles.')->group(function () {

    Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:roles:view');
    Route::get('create', [RoleController::class, 'create'])->name('create')->middleware('permission:roles:create');
    Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:roles:create');
    Route::get('/{role}', [RoleController::class, 'show'])->name('show')->middleware('permission:roles:view');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:roles:edit');
    Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('permission:roles:edit');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles:delete');
  });

  Route::prefix('permissions')->name('permissions.')->group(function () {

    Route::get('/', [PermissionController::class, 'index'])->name('index')->middleware('permission:permissions:view');

    Route::middleware(['permission:permissions:create'])->group(function () {
      Route::get('create', [PermissionController::class, 'create'])->name('create');
      Route::post('/', [PermissionController::class, 'store'])->name('store');
    });

    Route::middleware(['permission:permissions:edit'])->group(function () {
      Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
      Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
    });

    Route::middleware(['permission:permissions:delete'])->group(function () {
      Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
    });
  });

  Route::prefix('users')->name('users.')->group(function () {

    Route::get('/', [UserController::class, 'index'])->name('index')->middleware('permission:users:view');
    Route::get('create', [UserController::class, 'create'])->name('create')->middleware('permission:users:create');
    Route::get('{user}', [UserController::class, 'show'])->name('show')->middleware('permission:users:view');
    Route::post('', [UserController::class, 'store'])->name('store')->middleware('permission:users:create');
    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('permission:users:edit');
    Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('permission:users:edit');
    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('permission:users:delete');
  });

  Route::get('users/{user}/roles', [UserRoleController::class, 'edit'])->name('users.roles.edit')->middleware('permission:users:edit');
  Route::post('users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update')->middleware('permission:users:edit');

  // ========== DEALER LOCATION ==========
  Route::prefix('dealer-locations')->name('dealer-locations.')->group(function () {

    Route::get('/', [DealerLocationController::class, 'index'])->name('index')->middleware('permission:dealer-locations:view');
    Route::get('create', [DealerLocationController::class, 'create'])->name('create')->middleware('permission:dealer-locations:create');
    Route::post('/', [DealerLocationController::class, 'store'])->name('store')->middleware('permission:dealer-locations:create');
    Route::get('import/template', [DealerLocationController::class, 'downloadImportTemplate'])->name('import.template')->middleware('permission:dealer-locations:create');
    Route::post('import', [DealerLocationController::class, 'import'])->name('import')->middleware('permission:dealer-locations:create');
    Route::get('/{dealerLocation}', [DealerLocationController::class, 'show'])->name('show')->middleware('permission:dealer-locations:view');
    Route::get('/{dealerLocation}/edit', [DealerLocationController::class, 'edit'])->name('edit')->middleware('permission:dealer-locations:edit');
    Route::put('/{dealerLocation}', [DealerLocationController::class, 'update'])->name('update')->middleware('permission:dealer-locations:edit');
    Route::delete('/{dealerLocation}', [DealerLocationController::class, 'destroy'])->name('destroy')->middleware('permission:dealer-locations:delete');
  });

  // ========== SEGMENTS ==========

  Route::prefix('segments')->name('segments.')->group(function () {

    Route::get('/', [SegmentController::class, 'index'])->name('index')->middleware('permission:segments:view');
    Route::get('/create', [SegmentController::class, 'create'])->name('create')->middleware('permission:segments:create');
    Route::post('/', [SegmentController::class, 'store'])->name('store')->middleware('permission:segments:create');
    Route::get('/import/template', [SegmentController::class, 'downloadImportTemplate'])->name('import.template')->middleware('permission:segments:create');
    Route::post('/import', [SegmentController::class, 'import'])->name('import')->middleware('permission:segments:create');
    Route::get('/{segment}', [SegmentController::class, 'show'])->name('show')->middleware('permission:segments:view');
    Route::get('/{segment}/edit', [SegmentController::class, 'edit'])->name('edit')->middleware('permission:segments:edit');
    Route::put('/{segment}', [SegmentController::class, 'update'])->name('update')->middleware('permission:segments:edit');
    Route::delete('/{segment}', [SegmentController::class, 'destroy'])->name('destroy')->middleware('permission:segments:delete');
    Route::get('/check-code/{code}', [SegmentController::class, 'checkCode'])->name('check-code')->middleware('permission:segments:checkcode');
  });

  // ========== TRANSMISSIONS ==========
  Route::prefix('transmissions')->name('transmissions.')->group(function () {

    Route::get('/', [TransmissionController::class, 'index'])
      ->name('index')
      ->middleware('permission:transmissions:view');

    Route::get('/create', [TransmissionController::class, 'create'])
      ->name('create')
      ->middleware('permission:transmissions:create');

    Route::post('/', [TransmissionController::class, 'store'])
      ->name('store')
      ->middleware('permission:transmissions:create');

    Route::get('/import/template', [TransmissionController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:transmissions:create');

    Route::post('/import', [TransmissionController::class, 'import'])
      ->name('import')
      ->middleware('permission:transmissions:create');

    Route::get('/{transmission}', [TransmissionController::class, 'show'])
      ->name('show')
      ->middleware('permission:transmissions:view');

    Route::get('/{transmission}/edit', [TransmissionController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:transmissions:edit');

    Route::put('/{transmission}', [TransmissionController::class, 'update'])
      ->name('update')
      ->middleware('permission:transmissions:edit');

    Route::delete('/{transmission}', [TransmissionController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:transmissions:delete');
  });

  // ========== FUEL TYPES ==========
  Route::prefix('fuel-types')->name('fuel-types.')->group(function () {

    Route::get('/', [FuelTypeController::class, 'index'])
      ->name('index')
      ->middleware('permission:fuel-types:view');

    Route::get('/create', [FuelTypeController::class, 'create'])
      ->name('create')
      ->middleware('permission:fuel-types:create');

    Route::post('/', [FuelTypeController::class, 'store'])
      ->name('store')
      ->middleware('permission:fuel-types:create');

    Route::get('/import/template', [FuelTypeController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:fuel-types:create');

    Route::post('/import', [FuelTypeController::class, 'import'])
      ->name('import')
      ->middleware('permission:fuel-types:create');

    Route::get('/{fuelType}', [FuelTypeController::class, 'show'])
      ->name('show')
      ->middleware('permission:fuel-types:view');

    Route::get('/{fuelType}/edit', [FuelTypeController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:fuel-types:edit');

    Route::put('/{fuelType}', [FuelTypeController::class, 'update'])
      ->name('update')
      ->middleware('permission:fuel-types:edit');

    Route::delete('/{fuelType}', [FuelTypeController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:fuel-types:delete');
  });

  // ========== COLOURS ==========
  Route::prefix('colours')->name('colours.')->group(function () {

    Route::get('/', [ColourController::class, 'index'])
      ->name('index')
      ->middleware('permission:colours:view');

    Route::get('/create', [ColourController::class, 'create'])
      ->name('create')
      ->middleware('permission:colours:create');

    Route::post('/', [ColourController::class, 'store'])
      ->name('store')
      ->middleware('permission:colours:create');

    Route::get('/import/template', [ColourController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:colours:create');

    Route::post('/import', [ColourController::class, 'import'])
      ->name('import')
      ->middleware('permission:colours:create');

    Route::get('/{colour}', [ColourController::class, 'show'])
      ->name('show')
      ->middleware('permission:colours:view');

    Route::get('/{colour}/edit', [ColourController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:colours:edit');

    Route::put('/{colour}', [ColourController::class, 'update'])
      ->name('update')
      ->middleware('permission:colours:edit');

    Route::delete('/{colour}', [ColourController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:colours:delete');
  });

  // ========== VEHICLE MODELS ==========
  Route::prefix('vehicle-models')->name('vehicle-models.')->group(function () {

    Route::get('/', [VehicleModelController::class, 'index'])
      ->name('index')
      ->middleware('permission:vehicle-models:view');

    Route::get('/create', [VehicleModelController::class, 'create'])
      ->name('create')
      ->middleware('permission:vehicle-models:create');

    Route::post('/', [VehicleModelController::class, 'store'])
      ->name('store')
      ->middleware('permission:vehicle-models:create');

    Route::get('/import/template', [VehicleModelController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:vehicle-models:create');

    Route::post('/import', [VehicleModelController::class, 'import'])
      ->name('import')
      ->middleware('permission:vehicle-models:create');

    Route::get('/{vehicleModel}', [VehicleModelController::class, 'show'])
      ->name('show')
      ->middleware('permission:vehicle-models:view');

    Route::get('/{vehicleModel}/edit', [VehicleModelController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:vehicle-models:edit');

    Route::put('/{vehicleModel}', [VehicleModelController::class, 'update'])
      ->name('update')
      ->middleware('permission:vehicle-models:edit');

    Route::delete('/{vehicleModel}', [VehicleModelController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:vehicle-models:delete');

    // Get fuel types for a specific vehicle model
    Route::get('/{vehicleModel}/fuel-types', [VehicleModelController::class, 'getFuelTypes'])
      ->name('fuel-types')
      ->middleware('permission:vehicle-models:view');
  });

  // ========== VARIANTS ==========
  Route::prefix('variants')->name('variants.')->group(function () {

    // AJAX routes MUST come before {variant} parameter route
    Route::get('/ajax/fuel-types', [VariantController::class, 'getFuelTypes'])
      ->name('get-fuel-types')
      ->middleware('permission:variants:view');

    Route::get('/ajax/transmissions', [VariantController::class, 'getTransmissions'])
      ->name('get-transmissions')
      ->middleware('permission:variants:view');

    Route::get('/ajax/colours', [VariantController::class, 'getColours'])
      ->name('get-colours')
      ->middleware('permission:variants:view');

    Route::get('/', [VariantController::class, 'index'])
      ->name('index')
      ->middleware('permission:variants:view');

    Route::get('/create', [VariantController::class, 'create'])
      ->name('create')
      ->middleware('permission:variants:create');

    Route::post('/', [VariantController::class, 'store'])
      ->name('store')
      ->middleware('permission:variants:create');

    Route::get('/import/template', [VariantController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:variants:create');

    Route::post('/import', [VariantController::class, 'import'])
      ->name('import')
      ->middleware('permission:variants:create');

    Route::get('/{variant}', [VariantController::class, 'show'])
      ->name('show')
      ->middleware('permission:variants:view');

    Route::get('/{variant}/edit', [VariantController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:variants:edit');

    Route::put('/{variant}', [VariantController::class, 'update'])
      ->name('update')
      ->middleware('permission:variants:edit');

    Route::delete('/{variant}', [VariantController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:variants:delete');
  });

  // ========== ENQUIRY TYPES ==========
  Route::prefix('enquiry-types')->name('enquiry-types.')->group(function () {

    Route::get('/', [EnquiryTypeController::class, 'index'])
      ->name('index')
      ->middleware('permission:enquiry-types:view');

    Route::get('/create', [EnquiryTypeController::class, 'create'])
      ->name('create')
      ->middleware('permission:enquiry-types:create');

    Route::post('/', [EnquiryTypeController::class, 'store'])
      ->name('store')
      ->middleware('permission:enquiry-types:create');

    Route::get('/{enquiryType}/edit', [EnquiryTypeController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:enquiry-types:edit');

    Route::put('/{enquiryType}', [EnquiryTypeController::class, 'update'])
      ->name('update')
      ->middleware('permission:enquiry-types:edit');

    Route::delete('/{enquiryType}', [EnquiryTypeController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:enquiry-types:delete');
  });

  // ========== MAKES ==========
  Route::prefix('makes')->name('makes.')->group(function () {

    Route::get('/', [MakeController::class, 'index'])
      ->name('index')
      ->middleware('permission:makes:view');

    Route::get('/create', [MakeController::class, 'create'])
      ->name('create')
      ->middleware('permission:makes:create');

    Route::post('/', [MakeController::class, 'store'])
      ->name('store')
      ->middleware('permission:makes:create');

    Route::get('/import/template', [MakeController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:makes:create');

    Route::post('/import', [MakeController::class, 'import'])
      ->name('import')
      ->middleware('permission:makes:create');

    Route::get('/{make}/edit', [MakeController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:makes:edit');

    Route::put('/{make}', [MakeController::class, 'update'])
      ->name('update')
      ->middleware('permission:makes:edit');

    Route::delete('/{make}', [MakeController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:makes:delete');
  });

  // ========== BRAND MODELS ==========
  Route::prefix('brand-models')->name('brand-models.')->group(function () {

    Route::get('/', [BrandModelController::class, 'index'])
      ->name('index')
      ->middleware('permission:brand-models:view');

    Route::get('/create', [BrandModelController::class, 'create'])
      ->name('create')
      ->middleware('permission:brand-models:create');

    Route::post('/', [BrandModelController::class, 'store'])
      ->name('store')
      ->middleware('permission:brand-models:create');

    Route::get('/import/template', [BrandModelController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:brand-models:create');

    Route::post('/import', [BrandModelController::class, 'import'])
      ->name('import')
      ->middleware('permission:brand-models:create');

    Route::get('/{brandModel}/edit', [BrandModelController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:brand-models:edit');

    Route::put('/{brandModel}', [BrandModelController::class, 'update'])
      ->name('update')
      ->middleware('permission:brand-models:edit');

    Route::delete('/{brandModel}', [BrandModelController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:brand-models:delete');
  });

  // ========== LEAD TYPES ==========
  Route::prefix('lead-types')->name('lead-types.')->group(function () {

    Route::get('/', [LeadTypeController::class, 'index'])
      ->name('index')
      ->middleware('permission:lead-types:view');

    Route::get('/create', [LeadTypeController::class, 'create'])
      ->name('create')
      ->middleware('permission:lead-types:create');

    Route::post('/', [LeadTypeController::class, 'store'])
      ->name('store')
      ->middleware('permission:lead-types:create');

    Route::get('/{leadType}/edit', [LeadTypeController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:lead-types:edit');

    Route::put('/{leadType}', [LeadTypeController::class, 'update'])
      ->name('update')
      ->middleware('permission:lead-types:edit');

    Route::delete('/{leadType}', [LeadTypeController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:lead-types:delete');
  });

  // ========== LEAD STATUSES ==========
  Route::prefix('lead-statuses')->name('lead-statuses.')->group(function () {

    Route::get('/', [LeadStatusController::class, 'index'])
      ->name('index')
      ->middleware('permission:lead-statuses:view');

    Route::get('/create', [LeadStatusController::class, 'create'])
      ->name('create')
      ->middleware('permission:lead-statuses:create');

    Route::post('/', [LeadStatusController::class, 'store'])
      ->name('store')
      ->middleware('permission:lead-statuses:create');

    Route::get('/{leadStatus}/edit', [LeadStatusController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:lead-statuses:edit');

    Route::put('/{leadStatus}', [LeadStatusController::class, 'update'])
      ->name('update')
      ->middleware('permission:lead-statuses:edit');

    Route::delete('/{leadStatus}', [LeadStatusController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:lead-statuses:delete');
  });

  // ========== LEAD SOURCES ==========
  Route::prefix('lead-sources')->name('lead-sources.')->group(function () {

    Route::get('/', [LeadSourceController::class, 'index'])
      ->name('index')
      ->middleware('permission:lead-sources:view');

    Route::get('/create', [LeadSourceController::class, 'create'])
      ->name('create')
      ->middleware('permission:lead-sources:create');

    Route::post('/', [LeadSourceController::class, 'store'])
      ->name('store')
      ->middleware('permission:lead-sources:create');

    Route::get('/{leadSource}/edit', [LeadSourceController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:lead-sources:edit');

    Route::put('/{leadSource}', [LeadSourceController::class, 'update'])
      ->name('update')
      ->middleware('permission:lead-sources:edit');

    Route::delete('/{leadSource}', [LeadSourceController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:lead-sources:delete');
  });

  // ========== LEAD RESPONSES ==========
  Route::prefix('status-sources')->name('status-sources.')->group(function () {

    Route::get('/', [StatusSourceController::class, 'index'])
      ->name('index')
      ->middleware('permission:status-sources:view');

    Route::get('/create', [StatusSourceController::class, 'create'])
      ->name('create')
      ->middleware('permission:status-sources:create');

    Route::post('/', [StatusSourceController::class, 'store'])
      ->name('store')
      ->middleware('permission:status-sources:create');

    Route::get('/{statusSource}', [StatusSourceController::class, 'show'])
      ->name('show')
      ->middleware('permission:status-sources:view');

    Route::get('/{statusSource}/edit', [StatusSourceController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:status-sources:edit');

    Route::put('/{statusSource}', [StatusSourceController::class, 'update'])
      ->name('update')
      ->middleware('permission:status-sources:edit');

    Route::delete('/{statusSource}', [StatusSourceController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:status-sources:delete');
  });

  // ========== DESIGNATION ==========
  Route::prefix('designation')->name('designation.')->group(function () {

    Route::get('/', [DesignationController::class, 'index'])
      ->name('index')
      ->middleware('permission:designation:view');

    Route::get('/create', [DesignationController::class, 'create'])
      ->name('create')
      ->middleware('permission:designation:create');

    Route::get('/{designation}/edit', [DesignationController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:designation:edit');

    Route::post('/', [DesignationController::class, 'store'])
      ->name('store')
      ->middleware('permission:designation:create');

    Route::get('/import/template', [DesignationController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:designation:create');

    Route::post('/import', [DesignationController::class, 'import'])
      ->name('import')
      ->middleware('permission:designation:create');

    Route::delete('/{designation}', [DesignationController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:designation:delete');

    Route::put('/{designation}', [DesignationController::class, 'update'])
      ->name('update')
      ->middleware('permission:designation:edit');

    Route::put('/', [DesignationController::class, 'updateStatus'])
      ->name('status')
      ->middleware('permission:designation:status');
  });

  // ========== DEPARTMENT ==========
  Route::prefix('department')->name('department.')->group(function () {

    Route::get('/', [DepartmentController::class, 'index'])
      ->name('index')
      ->middleware('permission:department:view');

    Route::get('/create', [DepartmentController::class, 'create'])
      ->name('create')
      ->middleware('permission:department:create');

    Route::post('/', [DepartmentController::class, 'store'])
      ->name('store')
      ->middleware('permission:department:create');

    Route::get('/import/template', [DepartmentController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:department:create');

    Route::post('/import', [DepartmentController::class, 'import'])
      ->name('import')
      ->middleware('permission:department:create');

    Route::get('/{department}/edit', [DepartmentController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:department:edit');

    Route::put('/{department}', [DepartmentController::class, 'update'])
      ->name('update')
      ->middleware('permission:department:edit');

    Route::delete('/{department}', [DepartmentController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:department:delete');

    Route::put('/', [DepartmentController::class, 'updateStatus'])
      ->name('status')
      ->middleware('permission:department:status');
  });

  Route::prefix('subsegment')->name('subsegment.')->group(function () {

    Route::get('/', [SubSegmentController::class, 'index'])
      ->name('index')
      ->middleware('permission:subsegments:view');

    Route::get('/create', [SubSegmentController::class, 'create'])
      ->name('create')
      ->middleware('permission:subsegments:create');

    Route::post('/', [SubSegmentController::class, 'store'])
      ->name('store')
      ->middleware('permission:subsegments:create');

    Route::get('/import/template', [SubSegmentController::class, 'downloadImportTemplate'])
      ->name('import.template')
      ->middleware('permission:subsegments:create');

    Route::post('/import', [SubSegmentController::class, 'import'])
      ->name('import')
      ->middleware('permission:subsegments:create');

    Route::get('/{subsegment}', [SubSegmentController::class, 'show'])
      ->name('show')
      ->middleware('permission:subsegments:view');

    Route::get('/{subsegment}/edit', [SubSegmentController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:subsegments:edit');

    Route::put('/{subsegment}', [SubSegmentController::class, 'update'])
      ->name('update')
      ->middleware('permission:subsegments:edit');

    Route::delete('/{subsegment}', [SubSegmentController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:subsegments:delete');

    Route::put('/', [SubSegmentController::class, 'updateStatus'])
      ->name('status')
      ->middleware('permission:subsegments:status');
  });

  Route::prefix('employee')->name('employee.')->group(function () {

    Route::post('/data', [EmployeeController::class, 'getData'])
      ->name('data')
      ->middleware('permission:employee:view');

    Route::get('/', [EmployeeController::class, 'index'])
      ->name('index')
      ->middleware('permission:employee:view');

    Route::get('/create', [EmployeeController::class, 'create'])
      ->name('create')
      ->middleware('permission:employee:create');

    Route::get('/hierarchy', [EmployeeController::class, 'hierarchy'])
      ->name('hierarchy')
      ->middleware('permission:employee:view');

    Route::post('/', [EmployeeController::class, 'store'])
      ->name('store')
      ->middleware('permission:employee:create');

    Route::get('/export', [EmployeeController::class, 'export'])
      ->name('export')
      ->middleware('permission:employee:view');

    Route::get('/ajax/employees-by-company/{company}', [EmployeeController::class, 'employeesByCompany'])
      ->name('employeesByCompany')
      ->middleware('permission:employee:view');

    Route::get('/by-multiple-companies', [EmployeeController::class, 'byMultipleCompanies'])
      ->name('byMultipleCompanies')
      ->middleware('permission:employee:view');

    Route::get('/{employee}', [EmployeeController::class, 'show'])
      ->name('show')
      ->middleware('permission:employee:view');

    Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])
      ->name('edit')
      ->middleware('permission:employee:edit');

    Route::match(['put', 'patch'], '/{employee}/update', [EmployeeController::class, 'update'])
      ->name('update')
      ->middleware('permission:employee:edit');

    Route::delete('/{employee}', [EmployeeController::class, 'destroy'])
      ->name('destroy')
      ->middleware('permission:employee:delete');

    Route::get('/{employee}/bySegment', [EmployeeController::class, 'getBySegment'])
      ->name('bySegment')
      ->middleware('permission:employee:view');

    Route::get('/{employee}/byLocations', [EmployeeController::class, 'byLocations'])
      ->name('byLocations')
      ->middleware('permission:employee:view');
  });

  // ========== LEAD MANAGEMENT ==========
  Route::prefix('leads')->name('leads.')->group(function () {
    Route::post('/data', [LeadController::class, 'getData'])->name('data')->middleware('permission:leads:view');

    // AJAX routes for cascading dropdowns (must come before {lead} parameter routes)
    Route::get('/ajax/sub-segments/{segment}', [LeadController::class, 'getSubSegments'])->name('ajax.sub-segments')->middleware('permission:leads:view');
    Route::get('/ajax/vehicle-models/{subSegment}', [LeadController::class, 'getVehicleModels'])->name('ajax.vehicle-models')->middleware('permission:leads:view');
    Route::get('/ajax/variants/{vehicleModel}', [LeadController::class, 'getVariants'])->name('ajax.variants')->middleware('permission:leads:view');
    Route::post('/ajax/fuel-types', [LeadController::class, 'getFuelTypes'])->name('ajax.fuel-types')->middleware('permission:leads:view');
    Route::post('/ajax/transmissions', [LeadController::class, 'getTransmissions'])->name('ajax.transmissions')->middleware('permission:leads:view');
    Route::post('/ajax/colours', [LeadController::class, 'getColours'])->name('ajax.colours')->middleware('permission:leads:view');
    Route::get('/ajax/lead-sources/{enquiryType}', [LeadController::class, 'getLeadSourcesByEnquiryType'])->name('ajax.lead-sources')->middleware('permission:leads:view');
    Route::get('/ajax/brand-models/{make}', [LeadController::class, 'getBrandModelsByMake'])->name('ajax.brand-models')->middleware('permission:leads:view');
    Route::get('/ajax/location-by-pincode/{pincode}', [LeadController::class, 'getLocationByPincode'])->name('ajax.location-by-pincode')->middleware('permission:leads:view');
    Route::get('/ajax/locations/{company}', [LeadController::class, 'getLocationsByCompany'])->name('ajax.locations')->middleware('permission:leads:view');
    Route::get('/ajax/sales-consultants/{location}', [LeadController::class, 'getSalesConsultants'])->name('ajax.sales-consultants')->middleware('permission:leads:view');
    Route::get('/ajax/assignable-users/{location}', [LeadController::class, 'getAssignableUsers'])->name('ajax.assignable-users')->middleware('permission:leads:view');
    Route::get('/ajax/tele-callers', [LeadController::class, 'getActiveTeleCallers'])->name('ajax.tele-callers')->middleware('permission:leads:assign');
    Route::get('/ajax/districts/{state}', [LeadController::class, 'getDistricts'])->name('ajax.districts')->middleware('permission:leads:view');
    Route::get('/ajax/cities/{district}', [LeadController::class, 'getCities'])->name('ajax.cities')->middleware('permission:leads:view');

    Route::get('/duplicates', [DuplicateLeadController::class, 'index'])->name('duplicates.index')->middleware('permission:leads:duplicates:view');
    Route::get('/duplicates/{user}', [DuplicateLeadController::class, 'show'])->name('duplicates.show')->middleware('permission:leads:duplicates:view');

    Route::get('/import/template', [LeadController::class, 'downloadImportTemplate'])->name('import.template')->middleware('permission:leads:create');
    Route::get('/import/logs', [LeadController::class, 'importLogs'])->name('import.logs')->middleware('permission:leads:create');
    Route::post('/import', [LeadController::class, 'import'])->name('import')->middleware('permission:leads:create');
    Route::get('/export', [LeadController::class, 'export'])->name('export')->middleware('permission:leads:view');

    Route::get('/user-data/template', [LeadController::class, 'downloadUserDataTemplate'])->name('user-data.template')->middleware('permission:leads:create');
    Route::post('/user-data/upload', [LeadController::class, 'userDataUpload'])->name('user-data.upload')->middleware('permission:leads:create');

    Route::get('/', [LeadController::class, 'index'])->name('index')->middleware('permission:leads:view');
    Route::get('/create', [LeadController::class, 'create'])->name('create')->middleware('permission:leads:create');
    Route::post('/', [LeadController::class, 'store'])->name('store')->middleware('permission:leads:create');
    Route::get('/{lead}', [LeadController::class, 'show'])->name('show')->middleware('permission:leads:view');
    Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit')->middleware('permission:leads:edit');
    Route::put('/{lead}', [LeadController::class, 'update'])->name('update')->middleware('permission:leads:edit');
    Route::delete('/{lead}', [LeadController::class, 'destroy'])->name('destroy')->middleware('permission:leads:delete');
    Route::post('/{lead}/assign', [LeadController::class, 'assign'])->name('assign')->middleware('permission:leads:assign');
    Route::post('/bulk-assign', [LeadController::class, 'bulkAssign'])->name('bulk-assign')->middleware('permission:leads:assign');

    // Attachments
    Route::post('/{lead}/attachments', [LeadAttachmentController::class, 'store'])->name('attachments.store')->middleware('permission:leads:attachments:create');
    Route::get('/attachments/{attachment}/download', [LeadAttachmentController::class, 'download'])->name('attachments.download')->middleware('permission:leads:attachments:view');
    Route::delete('/attachments/{attachment}', [LeadAttachmentController::class, 'destroy'])->name('attachments.destroy')->middleware('permission:leads:attachments:delete');

    // Calls
    Route::get('/{lead}/calls', [LeadCallController::class, 'index'])->name('calls.index')->middleware('permission:leads:calls:view');
    Route::post('/{lead}/calls', [LeadCallController::class, 'store'])->name('calls.store')->middleware('permission:leads:calls:create');
    Route::put('/calls/{call}', [LeadCallController::class, 'update'])->name('calls.update')->middleware('permission:leads:calls:create');
    Route::delete('/calls/{call}', [LeadCallController::class, 'destroy'])->name('calls.destroy')->middleware('permission:leads:calls:delete');

    // Activities
    Route::get('/{lead}/activities', [LeadActivityController::class, 'index'])->name('activities.index')->middleware('permission:leads:activities:view');
    Route::post('/{lead}/activities', [LeadActivityController::class, 'store'])->name('activities.store');
    Route::delete('/activities/{activity}', [LeadActivityController::class, 'destroy'])->name('activities.destroy');

    // Notes
    Route::get('/{lead}/notes', [LeadNoteController::class, 'index'])->name('notes.index')->middleware('permission:leads:notes:view');
    Route::post('/{lead}/notes', [LeadNoteController::class, 'store'])->name('notes.store')->middleware('permission:leads:notes:create');
    Route::put('/notes/{note}', [LeadNoteController::class, 'update'])->name('notes.update')->middleware('permission:leads:notes:create');
    Route::post('/notes/{note}/toggle-pin', [LeadNoteController::class, 'togglePin'])->name('notes.toggle-pin')->middleware('permission:leads:notes:create');
    Route::delete('/notes/{note}', [LeadNoteController::class, 'destroy'])->name('notes.destroy')->middleware('permission:leads:notes:delete');

    // Tasks
    Route::get('/{lead}/tasks', [LeadTaskController::class, 'index'])->name('tasks.index')->middleware('permission:leads:tasks:view');
    Route::post('/{lead}/tasks', [LeadTaskController::class, 'store'])->name('tasks.store')->middleware('permission:leads:tasks:create');
    Route::put('/tasks/{task}', [LeadTaskController::class, 'update'])->name('tasks.update')->middleware('permission:leads:tasks:create');
    Route::post('/tasks/{task}/status', [LeadTaskController::class, 'updateStatus'])->name('tasks.update-status')->middleware('permission:leads:tasks:create');
    Route::delete('/tasks/{task}', [LeadTaskController::class, 'destroy'])->name('tasks.destroy')->middleware('permission:leads:tasks:delete');

    // Reminders
    Route::get('/{lead}/reminders', [LeadReminderController::class, 'index'])->name('reminders.index')->middleware('permission:leads:reminders:view');
    Route::post('/{lead}/reminders', [LeadReminderController::class, 'store'])->name('reminders.store')->middleware('permission:leads:reminders:create');
    Route::put('/reminders/{reminder}', [LeadReminderController::class, 'update'])->name('reminders.update')->middleware('permission:leads:reminders:create');
    Route::delete('/reminders/{reminder}', [LeadReminderController::class, 'destroy'])->name('reminders.destroy')->middleware('permission:leads:reminders:delete');

    // Tags
    Route::get('/{lead}/tags', [LeadTagController::class, 'index'])->name('tags.index')->middleware('permission:leads:tags:view');
    Route::post('/{lead}/tags', [LeadTagController::class, 'store'])->name('tags.store')->middleware('permission:leads:tags:create');
    Route::delete('/tags/{tag}', [LeadTagController::class, 'destroy'])->name('tags.destroy')->middleware('permission:leads:tags:delete');
  });

  // ========== REPORTS ==========
  Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index')->middleware('permission:reports:index');
    Route::get('/lead-summary', [ReportController::class, 'leadSummary'])->name('lead-summary')->middleware('permission:reports:lead-summary');
    Route::get('/follow-up', [ReportController::class, 'followUp'])->name('follow-up')->middleware('permission:reports:follow-up');
    Route::get('/employee-performance', [ReportController::class, 'employeePerformance'])->name('employee-performance')->middleware('permission:reports:employee-performance');
    Route::get('/vehicle-interest', [ReportController::class, 'vehicleInterest'])->name('vehicle-interest')->middleware('permission:reports:vehicle-interest');
    Route::get('/lead-source', [ReportController::class, 'leadSource'])->name('lead-source')->middleware('permission:reports:lead-source');
    Route::get('/location-wise', [ReportController::class, 'locationWise'])->name('location-wise')->middleware('permission:reports:location-wise');
    Route::get('/likely-purchase', [ReportController::class, 'likelyPurchase'])->name('likely-purchase')->middleware('permission:reports:likely-purchase');
  });
});
