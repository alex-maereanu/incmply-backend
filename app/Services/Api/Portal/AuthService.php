<?php

namespace App\Services\Api\Portal;

use App\Http\Requests\Api\Portal\RegisterRequest;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{
    /**
     * @param RegisterRequest $request
     * @param string $role
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     * @throws \Throwable
     */
    public function register(RegisterRequest $request, string $role): HttpResponse
    {
        if ( ! in_array($role, [Role::ROLES_ADMIN])) {
            return response(['error' => __('auth.accessDenied')], Response::HTTP_UNAUTHORIZED);
        }

        return DB::transaction(function () use ($request, $role) {
            /** @var User $user */
            $user = User::create($request->only([
                'name',
                'email',
                'password',
            ]));

            $user->assignRole($role);
            $token = $user->createToken(env('OAUTH_ACCESS_TOKEN_NAME'))->accessToken;

            return response(['user' => $user->only('email'), 'token' => $token]);
        });
    }
}
