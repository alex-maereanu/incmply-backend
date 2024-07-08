<?php

namespace App\Http\Requests\Api;


use App\Models\User;
use App\Rules\StrongPasswordRule;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class EmailVerificationRequest extends FormRequest
{
    protected User $user;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = User::whereId($this->route('id'))->first();

        if (empty($user)) {
            return false;
        }

        $this->user = $user;

        if ( ! hash_equals(sha1($user->getEmailForVerification()), (string)$this->route('hash'))) {
            return false;
        }

        if ($this->user->hasVerifiedEmail()) {
            return false;
        }

        Auth::login($this->user);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
    public function fulfill()
    {
        if ( ! $this->user->hasVerifiedEmail()) {
            $this->user->markEmailAsVerified();

            event(new Verified($this->user));
        }
    }
}
