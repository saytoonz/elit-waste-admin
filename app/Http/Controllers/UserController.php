<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = User::with('roles')->orderBy('name');

        // Hide SuperAdmin accounts from non-SuperAdmin viewers
        if (!auth()->user()?->hasRole('SuperAdmin')) {
            $query->whereDoesntHave('roles', fn($q) => $q->where('name', 'SuperAdmin'));
        }

        $users = $query->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureSuperAdminOnlyCreate();
        $roles = $this->assignableRoles();
        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->ensureSuperAdminOnlyCreate();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', Rule::in(array_keys($this->assignableRoles()))],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->roles);

        \App\Services\AuditService::log('Created User', "Name: {$user->name}, Role: " . implode(', ', $request->roles));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->guardSuperAdminTarget($user);
        $roles = $this->assignableRoles();
        $userRole = $user->roles->pluck('name', 'name')->all();

        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->guardSuperAdminTarget($user);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', Rule::in(array_keys($this->assignableRoles()))],
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Preserve SuperAdmin role on the target user if the editor cannot manage it
        $rolesToSync = $request->roles;
        if (!auth()->user()?->hasRole('SuperAdmin') && $user->hasRole('SuperAdmin')) {
            $rolesToSync[] = 'SuperAdmin';
        }
        $user->syncRoles($rolesToSync);

        \App\Services\AuditService::log('Updated User', "ID: {$user->id}, Name: {$user->name}");

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        $this->guardSuperAdminTarget($user);

        $user->delete();
        \App\Services\AuditService::log('Deleted User', "Name: {$user->name}");

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Roles the current user is allowed to assign. SuperAdmin is hidden unless the
     * acting user is themselves a SuperAdmin — that role is granted via `php artisan platform:grant`.
     */
    private function assignableRoles(): array
    {
        $query = Role::query();
        if (!auth()->user()?->hasRole('SuperAdmin')) {
            $query->where('name', '!=', 'SuperAdmin');
        }
        return $query->pluck('name', 'name')->all();
    }

    /**
     * 404 the route if a non-SuperAdmin tries to operate on a SuperAdmin user.
     */
    private function guardSuperAdminTarget(User $user): void
    {
        if ($user->hasRole('SuperAdmin') && !auth()->user()?->hasRole('SuperAdmin')) {
            abort(404);
        }
    }

    /**
     * Non-SuperAdmin users add staff via the paid Email-account flow at My Services.
     */
    private function ensureSuperAdminOnlyCreate(): void
    {
        if (!auth()->user()?->hasRole('SuperAdmin')) {
            abort(403, 'New users are added by purchasing an Email account at My Services.');
        }
    }
}
