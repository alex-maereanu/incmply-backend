<?php

namespace App\Services\Api\Center;


use App\Models\Tenant;
use App\Models\TenantUser;

class TenantService
{
    /**
     * @param \App\Models\TenantUser $tenantUsers
     *
     * @return \App\Models\Tenant
     */
    public function create(TenantUser $tenantUsers): Tenant
    {
        return Tenant::create([
            'id' => $tenantUsers->tenant_id,
        ]);
    }
}
