<?php

namespace App\Services\Api\Center;

use App\Http\Requests\Api\AuthForgetPasswordRequest;
use App\Http\Requests\Api\AuthResetPasswordRequest;
use App\Http\Requests\Api\AuthSignInRequest;
use App\Http\Requests\Api\AuthVerifyTokenRequest;
use App\Http\Requests\Api\Portal\RegisterRequest;
use App\Models\Tenant;
use App\Models\Tenant\User;
use App\Models\TenantUser;
use App\Notifications\ResetPasswordNotification;
use App\Services\Api\GoogleAuthService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
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
        $request->merge(['tenant_id' => (string)Str::uuid()]);

        /** @var \App\Models\Tenant\User $tenantUser */
        $tenantUser = TenantUser::create($request->only(['email', 'tenant_id']));

        $url = $tenantUser->sendEmailVerificationNotification();

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
        $tenantUsers = $this->getTenantsByEMail($request->get('email'));

        $credentials = $request->only('email', 'password');

        // Verify for every tenant if credentials match
        $tenants = [];
        foreach ($tenantUsers as $tenantUser) {
            $validTenant = $tenantUser->tenant->run(function () use ($credentials) {
                /** @var User $user */
                $user = User::validateAndGetUserByCredentials($credentials);

                // Verify if user is active
                return $this->userActive($user);
            });

            if ($validTenant) {
                $tenants[] = $tenantUser->tenant;
            }
        }

        // User not active in tenant(s)
        abort_if(count($tenants) === 0, Response::HTTP_UNPROCESSABLE_ENTITY, __('auth.failed'));

        if (count($tenants) === 1) {
            $tenant = reset($tenants);

            return $this->selectWorkspace($tenant, $credentials);
        }

        $response = Arr::pluck($tenants, 'id');

        return \response($response);
    }

    /**
     * @param AuthForgetPasswordRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(AuthForgetPasswordRequest $request): HttpResponse
    {
        // override users and use the center db tenant_users table
        config(['auth.passwords.users.provider' => 'tenant_users']);
        $send = Password::sendResetLink($request->only('email'), function ($user, string $token) use ($request) {
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
        // override users and use the center db tenant_users table
        config(['auth.passwords.users.provider' => 'tenant_users']);

        /** @var User $user */
        $user = Password::getUser($request->only('email', 'token'));

        abort_if(! $user || ! Password::tokenExists($user, $request->get('token')),Response::HTTP_BAD_REQUEST,  __(Password::INVALID_TOKEN));

        $tenants = TenantUser::whereEmail($user->email)->get();

        return response($tenants->pluck('tenant_id'));
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

    private function getTenantsByEmail(string $email)
    {
        $tenantUsers = TenantUser::whereEmail($email)->withWhereHas('tenant')->get();

        // Verify if email exits in central workspaces
        abort_if($tenantUsers->count() === 0, Response::HTTP_UNPROCESSABLE_ENTITY, __('auth.failed'));

        return $tenantUsers;
    }

    /**
     * @param \App\Models\Tenant\User|null $user
     *
     * @return bool
     */
    private function userActive(User|null $user): bool
    {
        return $user && $user->hasVerifiedEmail();
    }
}
