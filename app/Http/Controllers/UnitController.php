<?php

namespace App\Http\Controllers;

use App\Models\ItemUnitConversion;
use App\Models\Unit;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UnitController extends Controller
{
    /**
     * Daftar satuan (search + filter status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $units = Unit::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.units.index', compact('units', 'search', 'status'));
    }

    /**
     * Form buat satuan baru.
     */
    public function create(): View
    {
        return view('master.units.create');
    }

    /**
     * Simpan satuan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:units,code'],
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $unit = Unit::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', Unit::class, $unit->id, [
            'new' => ['code' => $unit->code, 'name' => $unit->name, 'symbol' => $unit->symbol],
        ]);

        return redirect()->route('master.units.index')
            ->with('success', "Satuan {$unit->name} berhasil dibuat.");
    }

    /**
     * Form edit satuan.
     */
    public function edit(Unit $unit): View
    {
        return view('master.units.edit', compact('unit'));
    }

    /**
     * Update satuan.
     */
    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('units', 'code')->ignore($unit->id)],
            'name' => ['required', 'string', 'max:50'],
            'symbol' => ['required', 'string', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = [
            'code' => $unit->code,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'is_active' => $unit->is_active,
        ];

        $unit->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'symbol' => $validated['symbol'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', Unit::class, $unit->id, [
            'old' => $oldData,
            'new' => [
                'code' => $unit->code,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'is_active' => $unit->is_active,
            ],
        ]);

        return redirect()->route('master.units.index')
            ->with('success', "Satuan {$unit->name} berhasil diperbarui.");
    }

    /**
     * Hapus satuan (soft delete — tidak pernah hapus fisik).
     * Satuan yang dipakai barang/konversi tidak dapat dihapus.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        $itemCount = $unit->items()->withTrashed()->count();
        $conversionCount = ItemUnitConversion::where('from_unit_id', $unit->id)
            ->orWhere('to_unit_id', $unit->id)
            ->count();

        if ($itemCount > 0 || $conversionCount > 0) {
            return back()->with('error',
                "Satuan {$unit->name} tidak dapat dihapus karena dipakai {$itemCount} barang dan {$conversionCount} konversi. Gunakan status Nonaktif.");
        }

        $name = $unit->name;
        $unit->delete();

        AuditLogger::log('delete', Unit::class, $unit->id, [
            'old' => ['code' => $unit->code, 'name' => $name],
        ]);

        return redirect()->route('master.units.index')
            ->with('success', "Satuan {$name} berhasil dihapus.");
    }
}