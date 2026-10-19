<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Daftar role.
     */
    public function index(): View
    {
        $roles = Role::withCount('users', 'permissions')->orderBy('name')->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Form buat role baru.
     */
    public function create(): View
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('roles.create', compact('permissions'));
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        AuditLogger::log('create', Role::class, $role->id, [
            'new' => ['name' => $role->name, 'description' => $role->description],
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Role {$role->name} berhasil dibuat.");
    }

    /**
     * Form edit role.
     */
    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $rolePermissionIds = $role->permissions()->pluck('permissions.id')->all();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    /**
     * Update role.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $oldData = [
            'name' => $role->name,
            'description' => $role->description,
            'is_active' => $role->is_active,
        ];

        $role->name = $validated['name'];
        $role->description = $validated['description'] ?? null;
        $role->is_active = $request->boolean('is_active', $role->is_active);
        $role->save();

        if (array_key_exists('permissions', $validated)) {
            $role->permissions()->sync($validated['permissions']);
        }

        $newData = [
            'name' => $role->name,
            'description' => $role->description,
            'is_active' => $role->is_active,
        ];

        AuditLogger::log('update', Role::class, $role->id, [
            'old' => $oldData,
            'new' => $newData,
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Role {$role->name} berhasil diperbarui.");
    }

    /**
     * Hapus role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Jangan hapus role yang sedang dipakai user
        if ($role->users()->exists()) {
            return back()->with('error', "Role {$role->name} masih digunakan oleh pengguna dan tidak dapat dihapus.");
        }

        $name = $role->name;
        $role->delete();

        AuditLogger::log('delete', Role::class, $role->id, [
            'old' => ['name' => $name],
        ]);

        return redirect()->route('roles.index')
            ->with('success', "Role {$name} berhasil dihapus.");
    }

    /**
     * Daftar semua permission.
     */
    public function permissions(): View
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');

        return view('permissions.index', compact('permissions'));
    }
}