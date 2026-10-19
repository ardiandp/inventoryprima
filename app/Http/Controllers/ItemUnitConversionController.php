<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemUnitConversion;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemUnitConversionController extends Controller
{
    /**
     * Tambah konversi satuan untuk barang.
     * Kombinasi (item, from, to) unik; satuan asal/tujuan harus aktif.
     */
    public function store(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'from_unit_id' => ['required', 'integer', Rule::exists('units', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'to_unit_id' => ['required', 'integer', Rule::exists('units', 'id')
                ->where('is_active', true)->whereNull('deleted_at'), 'different:from_unit_id'],
            'conversion_factor' => ['required', 'numeric', 'gt:0'],
        ]);

        $duplicate = ItemUnitConversion::where('item_id', $item->id)
            ->where('from_unit_id', $validated['from_unit_id'])
            ->where('to_unit_id', $validated['to_unit_id'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'from_unit_id' => 'Konversi untuk kombinasi satuan ini sudah ada.',
            ]);
        }

        $conversion = $item->unitConversions()->create([
            'from_unit_id' => $validated['from_unit_id'],
            'to_unit_id' => $validated['to_unit_id'],
            'conversion_factor' => $validated['conversion_factor'],
        ]);

        AuditLogger::log('create', ItemUnitConversion::class, $conversion->id, [
            'new' => [
                'item_id' => $item->id,
                'from_unit_id' => $conversion->from_unit_id,
                'to_unit_id' => $conversion->to_unit_id,
                'conversion_factor' => $conversion->conversion_factor,
            ],
        ]);

        return redirect()->route('master.items.edit', $item)
            ->with('success', 'Konversi satuan berhasil ditambahkan.');
    }

    /**
     * Hapus konversi satuan barang.
     */
    public function destroy(Item $item, ItemUnitConversion $conversion): RedirectResponse
    {
        // Konversi harus milik barang yang bersangkutan
        if ($conversion->item_id !== $item->id) {
            abort(404);
        }

        $old = [
            'from_unit_id' => $conversion->from_unit_id,
            'to_unit_id' => $conversion->to_unit_id,
            'conversion_factor' => $conversion->conversion_factor,
        ];

        $conversion->delete();

        AuditLogger::log('delete', ItemUnitConversion::class, $conversion->id, ['old' => $old]);

        return redirect()->route('master.items.edit', $item)
            ->with('success', 'Konversi satuan berhasil dihapus.');
    }
}