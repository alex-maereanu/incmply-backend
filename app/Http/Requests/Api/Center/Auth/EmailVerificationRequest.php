<?php

namespace App\Http\Requests\Api\Center\Auth;


use App\Models\User;
use App\Rules\StrongPasswordRule;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    /**
     * @var \App\Models\User
     */
    public User $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->user = User::whereId($this->route('id'))->first();

        if (empty($this->user)) {
            return false;
        }

        if ( ! hash_equals(sha1($this->user->getEmailForVerification()), (string)$this->route('hash'))) {
            return false;
        }

        if ($this->user->hasVerifiedEmail()) {
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
        if ( ! $this->user->hasVerifiedEmail()) {
            $this->user->markEmailAsVerified();

            event(new Verified($this->user));
        }
    }
}
