<?php

namespace Database\Seeders\tenant;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class PersonalClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $client = new ClientRepository();
        $client->createPasswordGrantClient(null, 'Default password grant client', env('FRONTEND_URL'));
        $client->createPersonalAccessClient(null, 'Default personal access client', env('FRONTEND_URL'));
    }
}
