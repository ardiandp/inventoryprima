<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemController extends Controller
{
    /**
     * Daftar barang (search + filter kategori/status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');
        $categoryId = $request->integer('category_id') ?: null;

        $items = Item::with(['category', 'defaultUnit'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $categories = ItemCategory::where('is_active', true)->orderBy('name')->get();

        return view('master.items.index', compact('items', 'search', 'status', 'categoryId', 'categories'));
    }

    /**
     * Form buat barang baru.
     */
    public function create(): View
    {
        $categories = ItemCategory::where('is_active', true)->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('master.items.create', compact('categories', 'units'));
    }

    /**
     * Simpan barang baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:items,code'],
            'name' => ['required', 'string', 'max:150'],
            // Master nonaktif tidak dapat dipilih untuk data baru
            'category_id' => ['required', 'integer', Rule::exists('item_categories', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'default_unit_id' => ['required', 'integer', Rule::exists('units', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'specification' => ['nullable', 'string'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0', 'gte:min_stock'],
            'requires_qc' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item = Item::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'default_unit_id' => $validated['default_unit_id'],
            'specification' => $validated['specification'] ?? null,
            'min_stock' => $validated['min_stock'],
            'max_stock' => $validated['max_stock'] ?? null,
            'requires_qc' => $request->boolean('requires_qc'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', Item::class, $item->id, [
            'new' => [
                'code' => $item->code,
                'name' => $item->name,
                'category_id' => $item->category_id,
                'default_unit_id' => $item->default_unit_id,
            ],
        ]);

        return redirect()->route('master.items.index')
            ->with('success', "Barang {$item->name} berhasil dibuat.");
    }

    /**
     * Form edit barang + konversi satuan.
     */
    public function edit(Item $item): View
    {
        $item->load(['category', 'defaultUnit', 'unitConversions.fromUnit', 'unitConversions.toUnit']);

        $categories = ItemCategory::where('is_active', true)
            ->orWhere('id', $item->category_id)
            ->orderBy('name')
            ->get();

        $units = Unit::where('is_active', true)
            ->orWhere('id', $item->default_unit_id)
            ->orderBy('name')
            ->get();

        return view('master.items.edit', compact('item', 'categories', 'units'));
    }

    /**
     * Update barang.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('items', 'code')->ignore($item->id)],
            'name' => ['required', 'string', 'max:150'],
            // Master nonaktif tidak dapat dipilih (kecuali nilai saat ini tetap dipertahankan)
            'category_id' => ['required', 'integer', Rule::exists('item_categories', 'id')
                ->where('is_active', true)->whereNull('deleted_at')
                ->orWhere('id', $item->category_id)],
            'default_unit_id' => ['required', 'integer', Rule::exists('units', 'id')
                ->where('is_active', true)->whereNull('deleted_at')
                ->orWhere('id', $item->default_unit_id)],
            'specification' => ['nullable', 'string'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'max_stock' => ['nullable', 'numeric', 'min:0', 'gte:min_stock'],
            'requires_qc' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $item->only([
            'code', 'name', 'category_id', 'default_unit_id',
            'min_stock', 'max_stock', 'requires_qc', 'is_active',
        ]);

        $item->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'default_unit_id' => $validated['default_unit_id'],
            'specification' => $validated['specification'] ?? null,
            'min_stock' => $validated['min_stock'],
            'max_stock' => $validated['max_stock'] ?? null,
            'requires_qc' => $request->boolean('requires_qc'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', Item::class, $item->id, [
            'old' => $oldData,
            'new' => $item->fresh()->only([
                'code', 'name', 'category_id', 'default_unit_id',
                'min_stock', 'max_stock', 'requires_qc', 'is_active',
            ]),
        ]);

        return redirect()->route('master.items.index')
            ->with('success', "Barang {$item->name} berhasil diperbarui.");
    }

    /**
     * Hapus barang (soft delete — tidak pernah hapus fisik).
     * Barang yang sudah dipakai transaksi/konversi tidak dapat dihapus;
     * gunakan status Nonaktif agar tidak dipilih transaksi baru.
     */
    public function destroy(Item $item): RedirectResponse
    {
        $blockReason = $this->usageBlockReason($item);

        if ($blockReason !== null) {
            return back()->with('error', $blockReason);
        }

        $name = $item->name;
        $item->delete();

        AuditLogger::log('delete', Item::class, $item->id, [
            'old' => ['code' => $item->code, 'name' => $name],
        ]);

        return redirect()->route('master.items.index')
            ->with('success', "Barang {$name} berhasil dihapus.");
    }

    /**
     * Alasan pemblokiran hapus barang, atau null jika boleh dihapus.
     * Tabel transaksi bisnis dibuat pada PHASE 4+ — hanya dicek bila sudah ada.
     */
    private function usageBlockReason(Item $item): ?string
    {
        if ($item->unitConversions()->exists()) {
            return "Barang {$item->name} tidak dapat dihapus karena memiliki konversi satuan. Gunakan status Nonaktif.";
        }

        $transactionTables = [
            'purchase_request_items', 'purchase_order_items', 'goods_receipt_items',
            'stock_transactions', 'stock_balances', 'material_reservations',
            'material_issue_items', 'material_return_items',
        ];

        foreach ($transactionTables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)
                && \Illuminate\Support\Facades\DB::table($table)->where('item_id', $item->id)->exists()) {
                return "Barang {$item->name} tidak dapat dihapus karena sudah dipakai transaksi. Gunakan status Nonaktif.";
            }
        }

        return null;
    }
}