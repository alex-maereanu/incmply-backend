<?php

namespace Database\Seeders;

use Database\Seeders\tenant\PersonalClientSeeder;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // if parameter --tenants if givin run seeds for tenant
        if($this->command->hasOption('tenants')){
            $this->call([
                PersonalClientSeeder::class,
            ]);
        }
    }
}
