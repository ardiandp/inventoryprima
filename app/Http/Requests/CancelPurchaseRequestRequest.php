<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelPurchaseRequestRequest extends FormRequest
{
    /**
     * Hanya pemohon RPO (atau editor berizin) yang boleh membatalkan.
     */
    public function authorize(): bool
    {
        $purchaseRequest = $this->route('purchase_request');

        return $this->user()?->can('cancel', $purchaseRequest) ?? false;
    }

    /**
     * Aturan validasi pembatalan.
     */
    public function rules(): array
    {
        return [
            // Pembatalan selalu memerlukan alasan (PRD bagian 4).
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan error dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
        ];
    }
}
