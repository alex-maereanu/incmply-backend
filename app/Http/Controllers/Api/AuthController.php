<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AuthForgetPasswordRequest;
use App\Http\Requests\Api\AuthResetPasswordRequest;
use App\Http\Requests\Api\AuthVerifyTokenRequest;
use App\Http\Requests\Api\EmailVerificationRequest;
use App\Http\Requests\Api\OtpRequest;
use App\Models\Tenant\User;
use App\Services\Api\AuthService;
use App\Services\Api\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @param \App\Http\Requests\Api\AuthForgetPasswordRequest $request
     * @param \App\Services\Api\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(AuthForgetPasswordRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->forgotPassword($request);
    }

    /**
     * @param \App\Http\Requests\Api\AuthVerifyTokenRequest $request
     * @param \App\Services\Api\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyChangePasswordToken(AuthVerifyTokenRequest $request, AuthService $authService): HttpResponse
    {
        return $authService->verifyChangePasswordToken($request);
    }

    /**
     * @param \App\Http\Requests\Api\AuthResetPasswordRequest $request
     * @param \App\Services\Api\AuthService $authService
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
     * @param \App\Services\Api\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function signOut(AuthService $authService): Response
    {
        return $authService->signOut();
    }

    /**
     * @param \App\Http\Requests\Api\OtpGenerateQrRequest $request
     * @param \App\Services\Api\GoogleAuthService $googleAuthService
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function generateQrCode(Request $request, GoogleAuthService $googleAuthService): HttpResponse
    {
        /** @var User $user */
        $user = Auth::user();

        [$qrImage, $recoveryKey] = $googleAuthService->generateQrCode($user);

        if ((bool)$request->query('qr') === true) {
            return response($qrImage);
        }

        return response(['qr_image' => $qrImage, 'recovery_key' => $recoveryKey]);
    }


    /**
     * @param \App\Http\Requests\Api\OtpRequest $request
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

    /**
     * @param \App\Http\Requests\Api\EmailVerificationRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyMail(EmailVerificationRequest $request): HttpResponse
    {
        $request->fulfill();

        /** @var User $user */
        $user = Auth::user();

        $user->forceFill($request->only([
            'name',
            'password',
        ]));

        $user->save();

        return response(['message' => __('auth.userActivated')]);
    }
}
