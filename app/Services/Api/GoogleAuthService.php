<?php

namespace App\Services\Api;

use App\Models\Tenant\Role;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Google2FA;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthService
{
    /**
     * Generate Google 2fa Authenticator QR
     *
     * @param \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return array
     * @throws \PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function generateQrCode(User|Authenticatable $user): array
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');

        User::find($user->id)->update(['google2fa_secret' => $google2fa->generateSecretKey()]);
        $user = User::whereId($user->id)->first();

        $qrImage = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        $qrImage     = str_replace('<?xml version="1.0" encoding="UTF-8"?>\n', '', $qrImage);
        $recoveryKey = $user->google2fa_secret;

        return [
            $qrImage,
            $recoveryKey,
        ];
    }

    /**
     * @param \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $oneTimePassword
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function verifyOTP(User|Authenticatable $user, string $oneTimePassword): HttpResponse
    {
        /** @var Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');

        if ( ! $google2fa->verifyKey($user->google2fa_secret, $oneTimePassword, 1)) {
            return response(['message' => trans('auth.failed')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Reset all tokens and continue with a new token.
        $user->tokens()->delete();

        return response([
            'user'  => $user->only('email'),
            'token' => $user->createToken(env('OAUTH_ACCESS_TOKEN_NAME'))->accessToken,
        ]);
    }

    /**
     * @param \App\Models\User $user
     * @param string $oneTimePassword
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function enableOtp(User $user, string $oneTimePassword): HttpResponse
    {
        $response = $this->verifyOTP($user, $oneTimePassword);

        if ( ! $response->isSuccessful()) {
            return $response;
        }

        $user->update(['is_otp_enabled' => true]);

        return $response;
    }

    /**
     * @param \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $oneTimePassword
     *
     * @return \Illuminate\Http\Response
     * @throws \PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException
     * @throws \PragmaRX\Google2FA\Exceptions\InvalidCharactersException
     * @throws \PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException
     */
    public function disableOtp(User|Authenticatable $user, string $oneTimePassword): HttpResponse
    {
        /** @var User $authUser */
        $authUser = Auth::user();

        $response = response([
            'user'  => $user->only('email'),
            'token' => null,
        ]);

        // When user disables its own otp than verify
        if ( ! $authUser->hasRole(Role::ROLES_ADMIN) || $user->id === $authUser->id) {
            $response = $this->verifyOTP($user, $oneTimePassword);
        }

        if ( ! $response->isSuccessful()) {
            return $response;
        }

        $user->update(['is_otp_enabled' => false, 'google2fa_secret' => null]);

        return $response;
    }
}
