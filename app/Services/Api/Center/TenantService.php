<?php

namespace App\Services\Api\Center;


use App\Models\Tenant;
use App\Models\User;

class TenantService
{
    /**
     * @param \App\Models\User $user
     *
     * @return \App\Models\Tenant
     */
    public function create(User $user): Tenant
    {
        $tenant = Tenant::create();

        $tenant->users()->sync([$user->id => ['created_at' => now(), 'updated_at' => now()]]);

        return $tenant;
    }
}
