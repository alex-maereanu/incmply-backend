<?php

namespace App\Jobs;

use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class CreateTenantUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->tenant->load('tenantUsers');

        $this->tenant->run(function () {
            User::create([
                'name'     => request()->get('name'),
                'email'    => $this->tenant->tenantUsers[0]->email,
                'password' => request()->get('password'),
            ])->forceFill([
                'email_verified_at' => $this->tenant->tenantUsers[0]->email_verified_at,
            ])->save();
        });
    }
}
