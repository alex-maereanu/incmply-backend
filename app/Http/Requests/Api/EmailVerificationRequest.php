<?php

namespace App\Http\Requests\Api;


use App\Models\TenantUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\StrongPasswordRule;

class EmailVerificationRequest extends FormRequest
{
    /**
     * @var \App\Models\TenantUser
     */
    public TenantUser $tenantUser;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->tenantUser = TenantUser::whereId($this->route('id'))->first();

        if (empty($this->tenantUser)) {
            return false;
        }

        $hashParameters = explode('_', (string)$this->route('hash'));

        if ( ! hash_equals(sha1($this->tenantUser->tenant_id), $hashParameters[0])) {
            return false;
        }

        if ( ! hash_equals(sha1($this->tenantUser->getEmailForVerification()), $hashParameters[1])) {
            return false;
        }

        if ($this->tenantUser->hasVerifiedEmail()) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'password'              => ['required', 'string', 'min:8', 'max:255', new StrongPasswordRule],
            'password_confirmation' => ['required', 'string', 'same:password', new StrongPasswordRule],
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill(): void
    {
        if ( ! $this->tenantUser->hasVerifiedEmail()) {
            $this->tenantUser->markEmailAsVerified();

            event(new Verified($this->tenantUser));
        }
    }
}
