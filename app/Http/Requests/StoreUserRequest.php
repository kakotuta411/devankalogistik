<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->isSuperAdmin(); }
    public function rules(): array {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:Admin,Management_View,Staff_Invoice,Staff_Shipment',
            'phone_number' => 'nullable|digits_between:8,20',
            'branch_id' => 'nullable|uuid|exists:branches,id',
        ];
    }
}
