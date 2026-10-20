<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequestRequest extends FormRequest
{
    /**
     * Pemohon harus login (middleware auth sudah menjaga hal ini).
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', PurchaseRequest::class) ?? false;
    }

    /**
     * Aturan validasi pembuatan RPO.
     */
    public function rules(): array
    {
        return [
            'request_type' => ['required', Rule::in(PurchaseRequest::requestTypes())],
            'reason' => ['required', 'string', 'max:500'],
            'priority' => ['required', Rule::in(PurchaseRequest::priorities())],
            'projected_date' => ['nullable', 'date', 'after_or_equal:today'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')
                ->where('is_active', true)],
            'comment' => ['nullable', 'string', 'max:1000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'items.*.unit_id' => ['required', 'integer', Rule::exists('units', 'id')
                ->where('is_active', true)->whereNull('deleted_at')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.minimum_term' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'items.*.specification' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan/keperluan permintaan wajib diisi.',
            'projected_date.after_or_equal' => 'Tanggal kebutuhan tidak boleh di masa lalu.',
            'items.required' => 'RPO harus memiliki minimal satu item.',
            'items.min' => 'RPO harus memiliki minimal satu item.',
            'items.*.item_id.exists' => 'Item yang dipilih tidak valid atau tidak aktif.',
            'items.*.unit_id.exists' => 'Satuan yang dipilih tidak valid atau tidak aktif.',
            'items.*.quantity.gt' => 'Kuantitas harus lebih besar dari 0.',
            'items.*.quantity.required' => 'Kuantitas item wajib diisi.',
        ];
    }

    /**
     * Validasi tambahan yang butuh konteks database.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            // Item tidak boleh dobel dalam satu RPO (sesuai unique index DB).
            $itemIds = array_filter(array_map(
                fn ($item) => is_array($item) ? ($item['item_id'] ?? null) : null,
                $items
            ));

            if (count($itemIds) !== count(array_unique($itemIds))) {
                $validator->errors()->add('items', 'Item yang sama tidak dapat dipilih lebih dari sekali.');
            }

            // Jika tipe proyek, proyek wajib dipilih.
            if ($this->input('request_type') === 'project' && empty($this->input('project_id'))) {
                $validator->errors()->add('project_id', 'Permintaan bertipe proyek wajib memilih proyek.');
            }

            // Satuan harus cocok dengan konversi barang (barang punya default unit + konversi).
            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemId = $item['item_id'] ?? null;
                $unitId = $item['unit_id'] ?? null;

                if (! $itemId || ! $unitId) {
                    continue;
                }

                $model = Item::find($itemId);
                if (! $model) {
                    continue;
                }

                // Satuan yang sah: satuan default barang, atau satuan hasil konversi.
                $allowed = Unit::where('id', $unitId)->exists()
                    && ($model->default_unit_id == $unitId
                        || $model->unitConversions()->where('from_unit_id', $unitId)->exists()
                        || $model->unitConversions()->where('to_unit_id', $unitId)->exists());

                if (! $allowed) {
                    $validator->errors()->add(
                        "items.{$index}.unit_id",
                        "Satuan tidak sesuai dengan satuan yang tersedia untuk {$model->name}."
                    );
                }
            }
        });
    }
}
