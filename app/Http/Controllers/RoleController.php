<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('id', 'desc')->paginate(15);
        return view('master.roles.index', compact('roles'));
    }

    public function create()
    {
        // Gather permissions grouped by menu similar to AccessController
        $permissions = Permission::all()->groupBy(function($p) {
            $parts = explode(':', $p->name);
            return $parts[0] ?? 'general';
        });

        return view('master.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $permissions = $data['permissions'] ?? [];

        // Create role with name and description
        $roleData = ['name' => $data['name']];
        if (!empty($data['description'])) {
            $roleData['description'] = $data['description'];
        }

        $role = Role::create($roleData);

        if (!empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')->with('success', 'Role created successfully with ' . count($permissions) . ' permissions assigned.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $permissionsGrouped = $role->permissions->groupBy(function ($p) {
            $parts = explode(':', $p->name);
            return $parts[0] ?? 'general';
        });

        return view('master.roles.show', compact('role', 'permissionsGrouped'));
    }

    public function edit(Role $role)
    {
        // Prevent editing Super Admin role
        if ($role->name === 'superadmin') {
            abort(403, 'You are not allowed to modify the Super Admin role or its permissions.');
        }

        // Gather permissions grouped by menu
        $permissions = Permission::all()->groupBy(function($p) {
            $parts = explode(':', $p->name);
            return $parts[0] ?? 'general';
        });

        // Get current role permissions
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('master.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        // Prevent updating Super Admin role
        if ($role->name === 'superadmin') {
            abort(403, 'You are not allowed to modify the Super Admin role or its permissions.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $permissions = $data['permissions'] ?? [];

        // Update role with name and description
        $roleData = ['name' => $data['name']];
        if (!empty($data['description'])) {
            $roleData['description'] = $data['description'];
        } else {
            $roleData['description'] = null;
        }

        $role->update($roleData);

        // Sync permissions
        $role->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully with ' . count($permissions) . ' permissions assigned.');
    }

    public function destroy(Role $role)
    {
        // Prevent deleting Super Admin role
        if ($role->name === 'superadmin') {
            abort(403, 'You are not allowed to delete the Super Admin role.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }
}
