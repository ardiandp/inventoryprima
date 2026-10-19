<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ItemCategoryController extends Controller
{
    /**
     * Daftar kategori barang (search + filter status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $categories = ItemCategory::with('parent')
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

        return view('master.categories.index', compact('categories', 'search', 'status'));
    }

    /**
     * Form buat kategori baru.
     */
    public function create(): View
    {
        $parents = ItemCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('master.categories.create', compact('parents'));
    }

    /**
     * Simpan kategori baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:item_categories,code'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', Rule::exists('item_categories', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = ItemCategory::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', ItemCategory::class, $category->id, [
            'new' => ['code' => $category->code, 'name' => $category->name],
        ]);

        return redirect()->route('master.categories.index')
            ->with('success', "Kategori {$category->name} berhasil dibuat.");
    }

    /**
     * Form edit kategori.
     */
    public function edit(ItemCategory $category): View
    {
        $parents = ItemCategory::where('is_active', true)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('master.categories.edit', compact('category', 'parents'));
    }

    /**
     * Update kategori.
     */
    public function update(Request $request, ItemCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('item_categories', 'code')->ignore($category->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'not_in:'.$category->id, Rule::exists('item_categories', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = [
            'code' => $category->code,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'is_active' => $category->is_active,
        ];

        $category->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', ItemCategory::class, $category->id, [
            'old' => $oldData,
            'new' => [
                'code' => $category->code,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'is_active' => $category->is_active,
            ],
        ]);

        return redirect()->route('master.categories.index')
            ->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Hapus kategori (soft delete — tidak pernah hapus fisik).
     * Kategori yang dipakai item/anak tidak dapat dihapus.
     */
    public function destroy(ItemCategory $category): RedirectResponse
    {
        $itemCount = $category->items()->withTrashed()->count();
        $childCount = $category->children()->withTrashed()->count();

        if ($itemCount > 0 || $childCount > 0) {
            return back()->with('error',
                "Kategori {$category->name} tidak dapat dihapus karena dipakai {$itemCount} barang dan memiliki {$childCount} sub-kategori. Gunakan status Nonaktif.");
        }

        $name = $category->name;
        $category->delete();

        AuditLogger::log('delete', ItemCategory::class, $category->id, [
            'old' => ['code' => $category->code, 'name' => $name],
        ]);

        return redirect()->route('master.categories.index')
            ->with('success', "Kategori {$name} berhasil dihapus.");
    }
}