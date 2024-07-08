<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OtpRequest extends FormRequest
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
        $rules = [
            'one_time_password' => 'required|numeric|digits:6',
        ];

        if ( ! Auth::check()) {
            $rules['email']    = 'required|email|max:255';
            $rules['password'] = 'required|string|min:8|max:255';
        }

        return $rules;
    }
}
