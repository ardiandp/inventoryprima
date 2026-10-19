<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Daftar supplier (search + filter status + pagination).
     */
    public function index(Request $request): View
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $suppliers = Supplier::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.suppliers.index', compact('suppliers', 'search', 'status'));
    }

    /**
     * Form buat supplier baru.
     */
    public function create(): View
    {
        return view('master.suppliers.create');
    }

    /**
     * Simpan supplier baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:suppliers,code'],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $supplier = Supplier::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('create', Supplier::class, $supplier->id, [
            'new' => ['code' => $supplier->code, 'name' => $supplier->name],
        ]);

        return redirect()->route('master.suppliers.index')
            ->with('success', "Supplier {$supplier->name} berhasil dibuat.");
    }

    /**
     * Form edit supplier.
     */
    public function edit(Supplier $supplier): View
    {
        return view('master.suppliers.edit', compact('supplier'));
    }

    /**
     * Update supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('suppliers', 'code')->ignore($supplier->id)],
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'tax_id' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $oldData = $supplier->only(['code', 'name', 'contact_person', 'email', 'phone', 'tax_id', 'is_active']);

        $supplier->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log('update', Supplier::class, $supplier->id, [
            'old' => $oldData,
            'new' => $supplier->fresh()->only(['code', 'name', 'contact_person', 'email', 'phone', 'tax_id', 'is_active']),
        ]);

        return redirect()->route('master.suppliers.index')
            ->with('success', "Supplier {$supplier->name} berhasil diperbarui.");
    }

    /**
     * Hapus supplier (soft delete — tidak pernah hapus fisik).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $supplier->delete();

        AuditLogger::log('delete', Supplier::class, $supplier->id, [
            'old' => ['code' => $supplier->code, 'name' => $name],
        ]);

        return redirect()->route('master.suppliers.index')
            ->with('success', "Supplier {$name} berhasil dihapus.");
    }
}