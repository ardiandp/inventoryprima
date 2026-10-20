<?php

namespace App\Http\Requests;

use App\Models\PurchaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewPurchaseRequestRequest extends FormRequest
{
    /**
     * Hanya pemverifikasi (Purchasing) yang boleh review RPO.
     */
    public function authorize(): bool
    {
        $purchaseRequest = $this->route('purchase_request');

        return $this->user()?->can('verify', $purchaseRequest) ?? false;
    }

    /**
     * Aturan validasi aksi verifikasi.
     */
    public function rules(): array
    {
        $action = $this->input('action');

        return [
            'action' => ['required', Rule::in(['approve', 'reject', 'request_revision', 'comment'])],
            'comment' => ['nullable', 'string', 'max:1000'],
            'rejection_reason' => [
                'nullable',
                'string',
                'max:500',
                // Alasan penolakan WAJIB (PRD: semua penolakan memerlukan alasan).
                'required_if:action,reject',
                'required_if:action,request_revision',
            ],
            'item_statuses' => ['nullable', 'array'],
            'item_statuses.*' => ['nullable', Rule::in(\App\Models\PurchaseRequestItem::statuses())],
            'item_reasons' => ['nullable', 'array'],
            'item_reasons.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Aksi verifikasi wajib dipilih.',
            'action.in' => 'Aksi verifikasi tidak valid.',
            'rejection_reason.required_if' => 'Alasan wajib diisi untuk penolakan maupun permintaan revisi.',
        ];
    }
}
