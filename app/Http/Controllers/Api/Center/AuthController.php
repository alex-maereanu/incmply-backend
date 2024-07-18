<?php

namespace App\Http\Controllers\Api\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Center\Auth\AuthForgetPasswordRequest;
use App\Http\Requests\Api\Center\Auth\AuthResetPasswordRequest;
use App\Http\Requests\Api\Center\Auth\AuthSignInRequest;
use App\Http\Requests\Api\Center\Auth\AuthVerifyTokenRequest;
use App\Http\Requests\Api\Center\Auth\EmailVerificationRequest;
use App\Http\Requests\Api\Center\Auth\OtpRequest;
use App\Http\Requests\Api\Center\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Api\Center\AuthService;
use App\Services\Api\Center\TenantService;
use App\Services\Api\GoogleAuthService;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @param \App\Http\Requests\Api\Center\Auth\RegisterRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception|\Throwable
     */
    public function register(RegisterRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->register($request);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\EmailVerificationRequest $request
     * @param \App\Services\Api\Center\TenantService $tenantService
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyMail(EmailVerificationRequest $request, TenantService $tenantService): HttpResponse
    {
        $request->fulfill();

        $tenantService->create($request->user);

        return response(['message' => __('auth.userActivated')]);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthSignInRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(AuthSignInRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->signIn($request);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthForgetPasswordRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(AuthForgetPasswordRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->forgotPassword($request);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthVerifyTokenRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyChangePasswordToken(AuthVerifyTokenRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->verifyChangePasswordToken($request);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\AuthResetPasswordRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function changePassword(AuthResetPasswordRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->changePassword($request);
    }

    /**
     * @param \App\Http\Requests\Api\Center\Auth\OtpRequest $request
     * @param \App\Services\Api\GoogleAuthService $googleAuthService
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function verifyOTP(OtpRequest $request, GoogleAuthService $googleAuthService): HttpResponse
    {
        $credentials = $request->only('email', 'password');

        /** @var User $user */
        if ( ! $user = User::validateAndGetUserByCredentials($credentials)) {
            return response(['message' => __('auth.failed')], Response::HTTP_UNAUTHORIZED);
        }

        $oneTimePassword = $request->get('one_time_password');

        return $googleAuthService->verifyOTP($user, $oneTimePassword);
    }

}
