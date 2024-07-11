<?php

namespace App\Http\Controllers\Api\Center;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function deleteAll(): HttpResponse
    {
        // TODO: delete function and route
        if(config('app.env') !== 'local'){
            return response([], Response::HTTP_FORBIDDEN);
        }
        /** @var Tenant[] $tenants */
        $tenants = Tenant::with(['tenantUsers'])->get();
        foreach ($tenants as $tenant) {
            $tenant->delete();
            foreach ($tenant->tenantUsers as $tenantUser) {
                $tenantUser->delete();
            }
        }

        return response([]);
    }
}
