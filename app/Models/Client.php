<?php

namespace App\Models;

class Client extends \Laravel\Passport\Client
{

    protected $connection = 'mysql'; // Master connection
}
