<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // if parameter --tenants if given run seeds for tenant
        if($this->command->hasOption('tenants')){
            $this->call([]);
        }
    }
}
