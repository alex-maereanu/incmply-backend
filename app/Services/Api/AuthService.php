<?php

namespace App\Services\Api;

use App\Http\Requests\Api\AuthForgetPasswordRequest;
use App\Http\Requests\Api\AuthResetPasswordRequest;
use App\Http\Requests\Api\AuthSignInRequest;
use App\Http\Requests\Api\AuthVerifyTokenRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * @param AuthSignInRequest $request
     * @param array $roles
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(AuthSignInRequest $request, array $roles): HttpResponse
    {
        $credentials = $request->only('email', 'password');

        /** @var User $user */
        if ( ! $user = User::validateAndGetUserByCredentials($credentials)) {
            return response(['message' => __('auth.failed')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ( ! $user->hasVerifiedEmail()) {
            return response(['message' => __('auth.mailNotVerified')], Response::HTTP_FORBIDDEN);
        }

        if ( ! $user->hasRole($roles)) {
            return response(['message' => __('auth.failed')], Response::HTTP_FORBIDDEN);
        }

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
        $send = Password::sendResetLink($request->only('email'), function (User $user, string $token) use ($request) {
            $user->notify(new ResetPasswordNotification($token));

            return null;
        });

        return response($send);
    }

    /**
     * @param \App\Http\Requests\Api\AuthVerifyTokenRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyChangePasswordToken(AuthVerifyTokenRequest $request): HttpResponse
    {
        /** @var User $user */
        $user = Password::getUser($request->only('email', 'token'));
        if ( ! $user || ! Password::tokenExists($user, $request->get('token'))) {
            return response(['message' => __(Password::INVALID_TOKEN)], Response::HTTP_BAD_REQUEST);
        }

        return response(['message' => __('success.')]);
    }

    /**
     * @param \App\Http\Requests\Api\AuthResetPasswordRequest $request
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
     * @return \Illuminate\Http\Response
     */
    public function signOut(): HttpResponse
    {
        Auth::user()->tokens()->delete();

        return response(['message' => __('auth.signedOut')]);
    }
}
