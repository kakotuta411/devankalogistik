<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->canWrite(); }
    public function rules(): array {
        return [
            'destination_branch_id' => 'required|uuid|exists:branches,id',
            'customer_id' => 'required|uuid|exists:customers,id',
            'po_number' => 'nullable|string|max:100',
            'payment_type' => 'nullable|string|in:kredit,lunas,cod',
            'sender_name' => 'nullable|string|max:255',
            'sender_phone' => 'nullable|digits_between:8,20',
            'sender_address' => 'nullable|string',
            'sender_province_code' => 'nullable|string|max:100',
            'sender_regency_code' => 'nullable|string|max:100',
            'sender_district_code' => 'nullable|string|max:100',
            'sender_village_code' => 'nullable|string|max:100',
            'receiver_name' => 'nullable|string|max:255',
            'receiver_phone' => 'nullable|digits_between:8,20',
            'receiver_address' => 'nullable|string',
            'receiver_province_code' => 'nullable|string|max:100',
            'receiver_regency_code' => 'nullable|string|max:100',
            'receiver_district_code' => 'nullable|string|max:100',
            'receiver_village_code' => 'nullable|string|max:100',
            'total_koli' => 'nullable|integer|min:1',
            'total_weight' => 'nullable|numeric|min:0',
            'service_type' => 'nullable|string|max:50',
            'package_description' => 'nullable|string|max:500',
        ];
    }
}
