<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('perform', App\Models\StockTransaction::class);
    }

    public function rules(): array
    {
        return [
            'receipt_id' => ['nullable', 'exists:purchase_receipts,id'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'production_order_id' => ['nullable', 'exists:production_orders,id'],
            'display_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('stock_transactions', 'display_number')->where(function ($query) {
                    $query->when($this->route('transaction')?->id, fn($q) => $q->where('id', '<>', $this->route('transaction')->id));
                }),
            ],
            'issue_quantity' => ['required', 'decimal:0,2', 'min:0'],
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'receipt_id.exists' => 'No. penerimaan tidak valid.',
            'purchase_order_id.exists' => 'No. PO tidak valid.',
            'production_order_id.exists' => 'No. Order produksi tidak valid.',
            'display_number.required' => 'No. transaksi stok wajib diisi.',
            'display_number.unique' => 'No. transaksi stok sudah ada, gunakan nomor lain.',
            'issue_quantity.required' => 'Quantity issue wajib diisi.',
            'issue_quantity.decimal' => 'Quantity issue harus numeric.',
            'issue_quantity.min' => 'Quantity issue harus minimal 0.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}