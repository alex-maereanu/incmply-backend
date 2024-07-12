<?php

namespace App\Services\Api\Center;

use App\Http\Requests\Api\Center\Auth\AuthForgetPasswordRequest;
use App\Http\Requests\Api\Center\Auth\AuthResetPasswordRequest;
use App\Http\Requests\Api\Center\Auth\AuthSignInRequest;
use App\Http\Requests\Api\Center\Auth\AuthVerifyTokenRequest;
use App\Http\Requests\Api\Center\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\Api\GoogleAuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request): HttpResponse
    {
        /** @var \App\Models\User $tenantUser */
        $user = User::create($request->only(['email', 'name', 'password']));

        $url = $user->sendEmailVerificationNotification();

        // Todo: remove $url and just give success back
        return \response($url);
    }

    /**
     * @param AuthSignInRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(AuthSignInRequest $request): HttpResponse
    {
        $credentials = $request->only('email', 'password');

        /** @var User $user */
        if ( ! $user = User::validateAndGetUserByCredentials($credentials)) {
            return response(['message' => __('auth.failed')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Verify user is active
        abort_if(! $this->userActive($user), Response::HTTP_UNPROCESSABLE_ENTITY, __('auth.failed'));

        $user->tokens()->delete();

        $response = ['request_two_factor_authentication' => $user->is_otp];

        if ( ! $user->is_otp) {
            $user->tokens()->delete();
            $response['token'] = $user->createToken(env('OAUTH_ACCESS_TOKEN_NAME'))->accessToken;
        }

        return response($response);
    }

    /**
     * @param AuthForgetPasswordRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(AuthForgetPasswordRequest $request): HttpResponse
    {
        $send = Password::sendResetLink($request->only('email'), function ($user, string $token) use ($request) {
            $user->notify(new ResetPasswordNotification($token));

            return null;
        });

        return response($send);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthVerifyTokenRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyChangePasswordToken(AuthVerifyTokenRequest $request): HttpResponse
    {
        /** @var User $user */
        $user = Password::getUser($request->only('email', 'token'));

        abort_if(! $user || ! Password::tokenExists($user, $request->get('token')),Response::HTTP_BAD_REQUEST,  __(Password::INVALID_TOKEN));

        return response(['message' => __('Valid')]);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthResetPasswordRequest $request
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function changePassword(AuthResetPasswordRequest $request): HttpResponse
    {
        /** @var User $user */
        $user = Password::getUser($request->only('email', 'password', 'password_confirmation', 'token'));

        if (Password::tokenExists($user, $request->get('token')) && $user->is_otp) {
            // Verify if user had OTP to go to verify OTP
            if ( ! $request->exists('one_time_password')) {
                return response(['request_two_factor_authentication' => $user->is_otp]);
            }

            // Verify OTP
            $googleAuthService = new GoogleAuthService();
            $response          = $googleAuthService->verifyOTP($user, $request->get('one_time_password'));

            if ( ! $response->isSuccessful()) {
                return $response;
            }
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        $statusCode = $status === Password::PASSWORD_RESET ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        return response(['message' => __($status)], $statusCode);
    }

    /**
     * @param \App\Models\Tenant $tenant
     * @param array $credentials
     *
     * @return \Illuminate\Http\Response
     */
    public function selectWorkspace(Tenant $tenant, array $credentials): HttpResponse
    {
        return $tenant->run(function () use ($credentials) {
            /** @var User $user */
            $user = User::validateAndGetUserByCredentials($credentials);

            // Verify user is active
            abort_if(! $this->userActive($user), Response::HTTP_UNPROCESSABLE_ENTITY, __('auth.failed'));

            $user->tokens()->delete();

            $response = ['request_two_factor_authentication' => $user->is_otp];

            if ( ! $user->is_otp) {
                $user->tokens()->delete();
                $response['token'] = $user->createToken(env('OAUTH_ACCESS_TOKEN_NAME'))->accessToken;
            }

            return response($response);
        });
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function signOut(): HttpResponse
    {
        Auth::user()->tokens()->delete();

        return response(['message' => __('auth.signedOut')]);
    }

    /**
     * @param \App\Models\User|null $user
     *
     * @return bool
     */
    private function userActive(User|null $user): bool
    {
        return $user && $user->hasVerifiedEmail();
    }
}
