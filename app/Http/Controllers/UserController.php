<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Daftar pengguna.
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');

        $users = User::with('roles', 'department')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Form buat pengguna baru.
     */
    public function create(): View
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'department_id' => $validated['department_id'] ?? null,
        ]);

        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        AuditLogger::log('create', User::class, $user->id, [
            'new' => ['name' => $user->name, 'email' => $user->email, 'role' => $validated['role']],
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} berhasil dibuat.");
    }

    /**
     * Form edit pengguna.
     */
    public function edit(User $user): View
    {
        $roles = Role::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $currentRole = $user->roles()->pluck('name')->first();

        return view('users.edit', compact('user', 'roles', 'departments', 'currentRole'));
    }

    /**
     * Update pengguna.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'exists:roles,name'],
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'department_id' => $user->department_id,
        ];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->department_id = $validated['department_id'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        // Update role
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        $newData = [
            'name' => $user->name,
            'email' => $user->email,
            'department_id' => $user->department_id,
            'role' => $validated['role'],
        ];

        AuditLogger::log('update', User::class, $user->id, [
            'old' => $oldData,
            'new' => $newData,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Jangan izinkan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Jangan hapus admin terakhir
        $adminCount = User::whereHas('roles', fn ($q) => $q->where('name', 'super_admin'))->count();
        if ($user->hasRole('super_admin') && $adminCount <= 1) {
            return back()->with('error', 'Tidak dapat menghapus super_admin terakhir.');
        }

        $name = $user->name;
        $user->delete();

        AuditLogger::log('delete', User::class, $user->id, [
            'old' => ['name' => $name, 'email' => $user->email],
        ]);

        return redirect()->route('users.index')
            ->with('success', "Pengguna {$name} berhasil dihapus.");
    }
}