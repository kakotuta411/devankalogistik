<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->canWrite(); }
    public function rules(): array {
        return [
            'customer_id' => 'nullable|uuid|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string',
            'customer_email' => 'nullable|email|max:255',
            'customer_province_code' => 'nullable|string|max:100',
            'customer_regency_code' => 'nullable|string|max:100',
            'customer_district_code' => 'nullable|string|max:100',
            'customer_village_code' => 'nullable|string|max:100',
            'shipping_cost' => 'nullable|numeric|min:0',
            'packaging_cost' => 'nullable|numeric|min:0',
            'equipment_charge' => 'nullable|numeric|min:0',
            'ppftz03' => 'nullable|numeric|min:0',
            'ppn' => 'nullable|numeric|min:0',
            'pph' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|string|in:nominal,percent',
            'discount_value' => 'nullable|numeric|min:0',
            'other_costs' => 'nullable|numeric|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'invoice_status' => 'required|in:Draft,Paid,Cancelled',
        ];
    }
}
