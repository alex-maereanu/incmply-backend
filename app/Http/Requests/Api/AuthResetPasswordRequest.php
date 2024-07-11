<?php

namespace App\Http\Requests\Api;

use App\Models\Tenant;
use Illuminate\Foundation\Http\FormRequest;

class AuthResetPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $tenant = Tenant::class;

        return [
            'email'                 => 'required|email|max:255',
            'token'                 => 'required',
            'password'              => 'required|min:8|max:255',
            'password_confirmation' => 'required|same:password',
            'tenant_ids.*'          => "required|string|uuid|exists:{$tenant},id",
        ];
    }
}
