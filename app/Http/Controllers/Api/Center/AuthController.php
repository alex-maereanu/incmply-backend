<?php

namespace App\Http\Controllers\Api\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthForgetPasswordRequest;
use App\Http\Requests\Api\AuthResetPasswordRequest;
use App\Http\Requests\Api\AuthSignInRequest;
use App\Http\Requests\Api\AuthVerifyTokenRequest;
use App\Http\Requests\Api\EmailVerificationRequest;
use App\Http\Requests\Api\Portal\RegisterRequest;
use App\Services\Api\Center\AuthService;
use App\Services\Api\Center\TenantService;
use Illuminate\Http\Response as HttpResponse;

class AuthController extends Controller
{
    /**
     * @param \App\Http\Requests\Api\Portal\RegisterRequest $request
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
     * @param \App\Http\Requests\Api\EmailVerificationRequest $request
     * @param \App\Services\Api\Center\TenantService $tenantService
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyMail(EmailVerificationRequest $request, TenantService $tenantService): HttpResponse
    {
        $request->fulfill();

        $tenantService->create($request->tenantUser);

        return response(['message' => __('auth.userActivated')]);
    }

    /**
     * @param \App\Http\Requests\Api\AuthSignInRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(AuthSignInRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->signIn($request);
    }

    /**
     * @param \App\Http\Requests\Api\AuthForgetPasswordRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(AuthForgetPasswordRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->forgotPassword($request);
    }

    /**
     * @param \App\Http\Requests\Api\AuthVerifyTokenRequest $request
     * @param \App\Services\Api\Center\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyChangePasswordToken(AuthVerifyTokenRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->verifyChangePasswordToken($request);
    }

    /**
     * @param \App\Http\Requests\Api\AuthResetPasswordRequest $request
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

}
