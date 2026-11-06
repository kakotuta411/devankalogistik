<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class DispatchManifestRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check() && auth()->user()->canWrite(); }
    public function rules(): array { return []; }
}
