<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuotationRequest extends FormRequest
{
    /**\n     * Determine if the user is authorized to make this request.\n     */\n    public function authorize(): bool\n    {\n        return $this->user()->can('sourcing', $this->route('purchaseRequest'));\n    }\n\n    /**\n     * Get the validation rules that apply to the request.\n     */\n    public function rules(): array\n    {\n        return [\n            'supplier_id' => 'required|exists:suppliers,id','is_active' => true),\n            'supplier_name' => 'required|string|max:255',\n            'total_amount' => 'required|numeric|min:0',\n            'valid_until' => 'nullable|date|after_or_equal:today',\n            'notes' => 'nullable|string|max:255',\n        ];\n    }\n\n    /**\n     * Get the error messages for the defined validation rules.\n     */\n    public function messages(): array\n    {\n        return [\n            'supplier_id.required' => 'Supplier wajib dipilih.',\n            'supplier_id.exists' => 'Supplier tidak valid.',\n            'supplier_name.required' => 'Nama supplier wajib diisi.',\n            'total_amount.required' => 'Total amount wajib diisi.',\n            'total_amount.numeric' => 'Total amount harus berupa angka.',\n        ];\n    }\n}