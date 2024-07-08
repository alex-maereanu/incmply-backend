<?php

namespace App\Http\Controllers\Api\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Portal\RegisterRequest;
use App\Http\Requests\Api\AuthSignInRequest;
use App\Models\Role;
use Illuminate\Http\Response;
use App\Services\Api\Portal\AuthService;

class AuthController extends Controller
{
    /**
     * @param \App\Http\Requests\Api\Portal\RegisterRequest $request
     * @param \App\Services\Api\Portal\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception|\Throwable
     */
    public function register(RegisterRequest $request, AuthService $authService): Response
    {
        return $authService->register($request, Role::ROLES_ADMIN);
    }

    /**
     * @param \App\Http\Requests\Api\AuthSignInRequest $request
     * @param \App\Services\Api\AuthService $authService
     *
     * @return \Illuminate\Http\Response
     */
    public function signIn(AuthSignInRequest $request, \App\Services\Api\AuthService $authService): Response
    {
        return $authService->signIn($request, Role::ROLES_PORTAL_ROLES);
    }

}
