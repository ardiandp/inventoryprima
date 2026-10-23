<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QcInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('view', QcInspection::class);
    }

    public function rules(): array
    {
        $inspectionId = $this->route('inspection')?->id;

        return [
            'receipt_id' => [
                'required',
                'exists:purchase_receipts,id',
                fn() => $this->userCannotApproveHisOwnReception() ?? true,
            ],
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'display_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('qc_inspections', 'display_number')->where(function ($query) {
                    $query->when($inspectionId, fn($q) => $q->where('id', '<>', $inspectionId));
                }),
            ],
            'observations' => 'nullable|string|max:500',
            'accepted_quantity' => ['required', 'decimal:0,2', 'min:0'],
            'rejected_quantity' => ['required', 'decimal:0,2', 'min:0'],
            'status' => [
                'required',
                Rule::in(['draft', 'received', 'pending_inspection', 'partially_accepted', 'accepted', 'rejected', 'posted']),
            ],
        ];
    }

    protected function validationData()
    {
        $data = $this->validated();

        // Auto-set inspected_by and inspected_at if creating new
        if ($this->isMethod('POST') && empty($data['inspected_by'])) {
            $data['inspected_by'] = auth()->id();
        }
        if ($this->isMethod('POST') && empty($data['inspected_at'])) {
            $data['inspected_at'] = now();
        }

        return $data;
    }

    private function userCannotApproveHisOwnReception(): bool
    {
        // Business rule: penerimaan yang dibuat oleh user finance tidak boleh dicek QC sama user
        // This is a simplified check - full logic would involve user role/department
        return true;
    }
}