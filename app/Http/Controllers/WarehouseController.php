<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    /**
     * Daftar gudang (search + filter status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $warehouses = Warehouse::withCount('locations')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.warehouses.index', compact('warehouses', 'search', 'status'));
    }

    /**
     * Form buat gudang baru.
     */
    public function create(): View
    {
        return view('master.warehouses.create');
    }

    /**
     * Simpan gudang baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $warehouse = Warehouse::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', Warehouse::class, $warehouse->id, [
            'new' => ['code' => $warehouse->code, 'name' => $warehouse->name],
        ]);

        return redirect()->route('master.warehouses.index')
            ->with('success', "Gudang {$warehouse->name} berhasil dibuat.");
    }

    /**
     * Form edit gudang.
     */
    public function edit(Warehouse $warehouse): View
    {
        return view('master.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update gudang.
     */
    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'name' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $warehouse->only(['code', 'name', 'contact_person', 'phone', 'is_active']);

        $warehouse->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', Warehouse::class, $warehouse->id, [
            'old' => $oldData,
            'new' => $warehouse->fresh()->only(['code', 'name', 'contact_person', 'phone', 'is_active']),
        ]);

        return redirect()->route('master.warehouses.index')
            ->with('success', "Gudang {$warehouse->name} berhasil diperbarui.");
    }

    /**
     * Hapus gudang (soft delete — tidak pernah hapus fisik).
     * Gudang yang masih memiliki lokasi tidak dapat dihapus.
     */
    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $locationCount = $warehouse->locations()->withTrashed()->count();

        if ($locationCount > 0) {
            return back()->with('error',
                "Gudang {$warehouse->name} tidak dapat dihapus karena masih memiliki {$locationCount} lokasi. Hapus lokasinya terlebih dahulu atau gunakan status Nonaktif.");
        }

        $name = $warehouse->name;
        $warehouse->delete();

        AuditLogger::log('delete', Warehouse::class, $warehouse->id, [
            'old' => ['code' => $warehouse->code, 'name' => $name],
        ]);

        return redirect()->route('master.warehouses.index')
            ->with('success', "Gudang {$name} berhasil dihapus.");
    }
}