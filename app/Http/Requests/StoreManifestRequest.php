<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreManifestRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->canWrite(); }
    public function rules(): array {
        return [
            'destination_branch_id' => 'required|uuid|exists:branches,id',
            'vehicle_no' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'shipment_ids' => 'nullable|array',
            'shipment_ids.*' => 'uuid|exists:shipments,id',
        ];
    }
}
