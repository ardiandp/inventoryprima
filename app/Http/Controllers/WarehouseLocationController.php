<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseLocationController extends Controller
{
    /**
     * Daftar lokasi gudang (search + filter gudang/status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');
        $warehouseId = $request->integer('warehouse_id') ?: null;

        $locations = WarehouseLocation::with('warehouse')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('master.locations.index', compact('locations', 'search', 'status', 'warehouseId', 'warehouses'));
    }

    /**
     * Form buat lokasi baru.
     */
    public function create(): View
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('master.locations.create', compact('warehouses'));
    }

    /**
     * Simpan lokasi baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Kode unik per gudang (unique index: warehouse_id + code)
        $duplicate = WarehouseLocation::where('warehouse_id', $validated['warehouse_id'])
            ->where('code', strtoupper($validated['code']))
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'code' => 'Kode lokasi sudah dipakai di gudang tersebut.',
            ]);
        }

        $location = WarehouseLocation::create([
            'warehouse_id' => $validated['warehouse_id'],
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', WarehouseLocation::class, $location->id, [
            'new' => [
                'warehouse_id' => $location->warehouse_id,
                'code' => $location->code,
                'name' => $location->name,
            ],
        ]);

        return redirect()->route('master.locations.index')
            ->with('success', "Lokasi {$location->name} berhasil dibuat.");
    }

    /**
     * Form edit lokasi.
     */
    public function edit(WarehouseLocation $location): View
    {
        // Gudang aktif + gudang saat ini (jika nonaktif tetap ditampilkan agar form valid)
        $warehouses = Warehouse::where(function ($q) {
            $q->where('is_active', true)->orWhere('id', $location->warehouse_id);
        })->orderBy('name')->get();

        return view('master.locations.edit', compact('location', 'warehouses'));
    }

    /**
     * Update lokasi.
     */
    public function update(Request $request, WarehouseLocation $location): RedirectResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $duplicate = WarehouseLocation::where('warehouse_id', $validated['warehouse_id'])
            ->where('code', strtoupper($validated['code']))
            ->where('id', '!=', $location->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'code' => 'Kode lokasi sudah dipakai di gudang tersebut.',
            ]);
        }

        $oldData = [
            'warehouse_id' => $location->warehouse_id,
            'code' => $location->code,
            'name' => $location->name,
            'is_active' => $location->is_active,
        ];

        $location->update([
            'warehouse_id' => $validated['warehouse_id'],
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', WarehouseLocation::class, $location->id, [
            'old' => $oldData,
            'new' => [
                'warehouse_id' => $location->warehouse_id,
                'code' => $location->code,
                'name' => $location->name,
                'is_active' => $location->is_active,
            ],
        ]);

        return redirect()->route('master.locations.index')
            ->with('success', "Lokasi {$location->name} berhasil diperbarui.");
    }

    /**
     * Hapus lokasi (soft delete — tidak pernah hapus fisik).
     */
    public function destroy(WarehouseLocation $location): RedirectResponse
    {
        $name = $location->name;
        $location->delete();

        AuditLogger::log('delete', WarehouseLocation::class, $location->id, [
            'old' => ['warehouse_id' => $location->warehouse_id, 'code' => $location->code, 'name' => $name],
        ]);

        return redirect()->route('master.locations.index')
            ->with('success', "Lokasi {$name} berhasil dihapus.");
    }
}