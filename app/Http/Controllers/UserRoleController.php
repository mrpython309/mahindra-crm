<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('master.users.roles', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        // Protect Super Admin user (id=1) from role changes
        if ($user->id === 1) {
            return redirect()
                ->route('users.index')
                ->with('error', 'You are not allowed to change roles for the Super Admin user.');
        }

        $data = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()->route('users.index')->with('success', 'User roles updated.');
    }
}
