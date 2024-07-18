<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Center\Auth\OtpRequest;
use App\Models\User;
use App\Services\Api\GoogleAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\Api\GoogleAuthService $googleAuthService
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function generateQrCode(Request $request, GoogleAuthService $googleAuthService): Response
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
     * @param \App\Http\Requests\Api\Center\Auth\OtpRequest $request
     * @param \App\Services\Api\GoogleAuthService $googleAuthService
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function enableOtp(OtpRequest $request, GoogleAuthService $googleAuthService): Response
    {
        /** @var User $user */
        $user            = Auth::user();
        $oneTimePassword = $request->get('one_time_password');

        return $googleAuthService->enableOtp($user, $oneTimePassword);
    }
}
