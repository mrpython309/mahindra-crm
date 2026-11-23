<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->paginate(20);

        $user = Auth::user();
        $canCreate = $user->hasRole('superadmin') || $user->can('users:create');
        $canEdit = $user->hasRole('superadmin') || $user->can('users:edit');
        $canDelete = $user->hasRole('superadmin') || $user->can('users:delete');

        return view('master.users.index', compact('users', 'canCreate', 'canEdit', 'canDelete'));
    }

    public function create()
    {
        return view('master.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();
        if (!($currentUser->hasRole('superadmin') || $currentUser->can('users:edit'))) {
            abort(403, 'Unauthorized');
        }

        $userBranches = is_string($user->branches)
        ? json_decode($user->branches, true)
        : ($user->branches ?? []);

        return view('master.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        if (!($currentUser->hasRole('superadmin') || $currentUser->can('users:edit'))) {
            abort(403, 'Unauthorized');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();
        if (!($currentUser->hasRole('superadmin') || $currentUser->can('users:delete'))) {
            abort(403, 'Unauthorized');
        }

        // Prevent deleting your own account
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function show(User $user)
    {
        $currentUser = Auth::user();
        if (!($currentUser->hasRole('superadmin') || $currentUser->can('users:view'))) {
            abort(403, 'Unauthorized');
        }

        return view('master.users.show', compact('user'));
    }
}
