<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionSeeder extends Seeder
{
  public function run(): void
  {
    $guard = 'web';

    /**
     * 🔹 Step 1: Define permissions (based on routes and modules)
     */
    $permissions = [
      // Roles & Permissions
      'roles:view',
      'roles:create',
      'roles:edit',
      'roles:delete',
      'permissions:view',
      'permissions:create',
      'permissions:edit',
      'permissions:delete',
      'users:view',
      'users:create',
      'users:edit',
      'users:delete',

      // Companies
      'companies:view',
      'companies:create',
      'companies:edit',
      'companies:delete',

      // Dealer Locations
      'dealer-locations:view',
      'dealer-locations:create',
      'dealer-locations:edit',
      'dealer-locations:delete',

      // Segments
      'segments:view',
      'segments:create',
      'segments:edit',
      'segments:delete',
      'segments:checkcode',

      // Masters: Transmissions, Fuel Types, Colours
      'transmissions:view',
      'transmissions:create',
      'transmissions:edit',
      'transmissions:delete',
      'fuel-types:view',
      'fuel-types:create',
      'fuel-types:edit',
      'fuel-types:delete',
      'colours:view',
      'colours:create',
      'colours:edit',
      'colours:delete',

      // Vehicle Models, Variants
      'vehicle-models:view',
      'vehicle-models:create',
      'vehicle-models:edit',
      'vehicle-models:delete',
      'variants:view',
      'variants:create',
      'variants:edit',
      'variants:delete',

      // Enquiry Types, Makes, Brand Models
      'enquiry-types:view',
      'enquiry-types:create',
      'enquiry-types:edit',
      'enquiry-types:delete',
      'makes:view',
      'makes:create',
      'makes:edit',
      'makes:delete',
      'brand-models:view',
      'brand-models:create',
      'brand-models:edit',
      'brand-models:delete',

      // Lead Masters
      'lead-types:view',
      'lead-types:create',
      'lead-types:edit',
      'lead-types:delete',
      'lead-statuses:view',
      'lead-statuses:create',
      'lead-statuses:edit',
      'lead-statuses:delete',
      'lead-sources:view',
      'lead-sources:create',
      'lead-sources:edit',
      'lead-sources:delete',
      'status-sources:view',
      'status-sources:create',
      'status-sources:edit',
      'status-sources:delete',

      // Designation, Department, Subsegments
      'designation:view',
      'designation:create',
      'designation:edit',
      'designation:delete',
      'designation:status',
      'department:view',
      'department:create',
      'department:edit',
      'department:delete',
      'department:status',
      'subsegments:view',
      'subsegments:create',
      'subsegments:edit',
      'subsegments:delete',
      'subsegments:status',

      // Employee
      'employee:view',
      'employee-hierarchy:view',
      'employee:create',
      'employee:edit',
      'employee:delete',

      // Lead Management
      'leads:view',
      'leads:create',
      'leads:edit',
      'leads:delete',
      'leads:assign',
      'leads:attachments:view',
      'leads:attachments:create',
      'leads:attachments:delete',
      'leads:calls:view',
      'leads:calls:create',
      'leads:calls:delete',
      'leads:activities:view',
      'leads:notes:view',
      'leads:notes:create',
      'leads:notes:delete',
      'leads:tasks:view',
      'leads:tasks:create',
      'leads:tasks:delete',
      'leads:reminders:view',
      'leads:reminders:create',
      'leads:reminders:delete',
      'leads:tags:view',
      'leads:tags:create',
      'leads:tags:delete',
      'leads:duplicates:view',

      // Reports (hub + one permission per report)
      'reports:index',
      'reports:lead-summary',
      'reports:follow-up',
      'reports:employee-performance',
      'reports:vehicle-interest',
      'reports:lead-source',
      'reports:location-wise',
      'reports:likely-purchase',
    ];

    /**
     * 🔹 Step 2: Create permissions
     */
    foreach ($permissions as $permission) {
      Permission::firstOrCreate([
        'name' => $permission,
        'guard_name' => $guard
      ]);
    }

    // Removed in favour of granular report permissions (see reports:* above).
    Permission::where('name', 'reports:view')->where('guard_name', $guard)->delete();

    /**
     * 🔹 Step 3: Create roles
     */
    $superAdmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => $guard]);
    $teleCaller = Role::firstOrCreate(['name' => 'tele-caller', 'guard_name' => $guard]);
    $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => $guard]);
    $salesConsultant = Role::firstOrCreate(['name' => 'sales-consultant', 'guard_name' => $guard]);
    $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => $guard]);

    /**
     * 🔹 Step 4: Assign permissions to roles
     */

    // Superadmin → ALL permissions
    $superAdmin->syncPermissions(Permission::all());

    // Tele-caller → User & role management, companies, locations, masters (incl. import), lead assignment
    $teleCaller->syncPermissions([
      'roles:view',
      'roles:create',
      'roles:edit',
      'permissions:view',
      'users:view',
      'users:create',
      'users:edit',
      'companies:view',
      'companies:create',
      'companies:edit',
      'dealer-locations:view',
      'dealer-locations:create',
      'dealer-locations:edit',
      'segments:view',
      'segments:create',
      'segments:edit',
      'segments:delete',
      'segments:checkcode',
      'designation:view',
      'designation:create',
      'designation:edit',
      'designation:delete',
      'designation:status',
      'department:view',
      'department:create',
      'department:edit',
      'department:delete',
      'department:status',
      'subsegments:view',
      'subsegments:create',
      'subsegments:edit',
      'subsegments:delete',
      'subsegments:status',
      'transmissions:view',
      'transmissions:create',
      'transmissions:edit',
      'transmissions:delete',
      'fuel-types:view',
      'fuel-types:create',
      'fuel-types:edit',
      'fuel-types:delete',
      'colours:view',
      'colours:create',
      'colours:edit',
      'colours:delete',
      'vehicle-models:view',
      'vehicle-models:create',
      'vehicle-models:edit',
      'vehicle-models:delete',
      'variants:view',
      'variants:create',
      'variants:edit',
      'variants:delete',
      'makes:view',
      'makes:create',
      'makes:edit',
      'makes:delete',
      'brand-models:view',
      'brand-models:create',
      'brand-models:edit',
      'brand-models:delete',
      'enquiry-types:view',
      'enquiry-types:create',
      'enquiry-types:edit',
      'enquiry-types:delete',
      'lead-types:view',
      'lead-types:create',
      'lead-types:edit',
      'lead-types:delete',
      'lead-statuses:view',
      'lead-statuses:create',
      'lead-statuses:edit',
      'lead-statuses:delete',
      'lead-sources:view',
      'lead-sources:create',
      'lead-sources:edit',
      'lead-sources:delete',
      'status-sources:view',
      'status-sources:create',
      'status-sources:edit',
      'status-sources:delete',
      'employee:view',
      'employee:create',
      'employee:edit',
      'employee:delete',
      'leads:view',
      'leads:create',
      'leads:edit',
      'leads:assign',
      'leads:attachments:view',
      'leads:attachments:create',
      'leads:attachments:delete',
      'leads:calls:view',
      'leads:calls:create',
      'leads:calls:delete',
      'leads:activities:view',
      'leads:notes:view',
      'leads:notes:create',
      'leads:notes:delete',
      'leads:tasks:view',
      'leads:tasks:create',
      'leads:tasks:delete',
      'leads:reminders:view',
      'leads:reminders:create',
      'leads:reminders:delete',
      'leads:tags:view',
      'leads:tags:create',
      'leads:tags:delete',
      'reports:index',
      'reports:lead-summary',
      'reports:follow-up',
      'reports:employee-performance',
      'reports:vehicle-interest',
      'reports:lead-source',
      'reports:location-wise',
      'reports:likely-purchase',
    ]);

    // Manager → Lead management, employee view, companies/locations view, master data view
    $manager->syncPermissions([
      'companies:view',
      'dealer-locations:view',
      'employee:view',
      'segments:view',
      'subsegments:view',
      'designation:view',
      'department:view',
      'transmissions:view',
      'fuel-types:view',
      'colours:view',
      'vehicle-models:view',
      'variants:view',
      'makes:view',
      'brand-models:view',
      'enquiry-types:view',
      'lead-types:view',
      'lead-statuses:view',
      'lead-sources:view',
      'status-sources:view',
      'leads:view',
      'leads:create',
      'leads:edit',
      'leads:assign',
      'leads:attachments:view',
      'leads:attachments:create',
      'leads:attachments:delete',
      'leads:calls:view',
      'leads:calls:create',
      'leads:calls:delete',
      'leads:activities:view',
      'leads:notes:view',
      'leads:notes:create',
      'leads:notes:delete',
      'leads:tasks:view',
      'leads:tasks:create',
      'leads:tasks:delete',
      'leads:reminders:view',
      'leads:reminders:create',
      'leads:reminders:delete',
      'leads:tags:view',
      'leads:tags:create',
      'leads:tags:delete',
      'leads:duplicates:view',
      'reports:index',
      'reports:lead-summary',
      'reports:follow-up',
      'reports:employee-performance',
      'reports:vehicle-interest',
      'reports:lead-source',
      'reports:location-wise',
      'reports:likely-purchase',
    ]);

    // Sales Consultant → Full lead management (own leads), no assign, no employee edit, master data view
    $salesConsultant->syncPermissions([
      'companies:view',
      'dealer-locations:view',
      'employee:view',
      'segments:view',
      'subsegments:view',
      'designation:view',
      'department:view',
      'transmissions:view',
      'fuel-types:view',
      'colours:view',
      'vehicle-models:view',
      'variants:view',
      'makes:view',
      'brand-models:view',
      'enquiry-types:view',
      'lead-types:view',
      'lead-statuses:view',
      'lead-sources:view',
      'status-sources:view',
      'leads:view',
      'leads:create',
      'leads:edit',
      'leads:attachments:view',
      'leads:attachments:create',
      'leads:attachments:delete',
      'leads:calls:view',
      'leads:calls:create',
      'leads:calls:delete',
      'leads:activities:view',
      'leads:notes:view',
      'leads:notes:create',
      'leads:notes:delete',
      'leads:tasks:view',
      'leads:tasks:create',
      'leads:tasks:delete',
      'leads:reminders:view',
      'leads:reminders:create',
      'leads:reminders:delete',
      'leads:tags:view',
      'leads:tags:create',
      'leads:tags:delete',
    ]);

    // Viewer → Read-only access (including master data view for dropdowns/reports)
    $viewer->syncPermissions([
      'companies:view',
      'dealer-locations:view',
      'employee:view',
      'segments:view',
      'subsegments:view',
      'designation:view',
      'department:view',
      'transmissions:view',
      'fuel-types:view',
      'colours:view',
      'vehicle-models:view',
      'variants:view',
      'makes:view',
      'brand-models:view',
      'enquiry-types:view',
      'lead-types:view',
      'lead-statuses:view',
      'lead-sources:view',
      'status-sources:view',
      'leads:view',
      'leads:attachments:view',
      'leads:calls:view',
      'leads:activities:view',
      'leads:notes:view',
      'leads:tasks:view',
      'leads:reminders:view',
      'leads:tags:view',
    ]);

    /**
     * 🔹 Step 5: Assign superadmin role to first user
     */
    $user = User::find(1);
    if ($user) {
      $user->assignRole($superAdmin);
    }
  }
}
