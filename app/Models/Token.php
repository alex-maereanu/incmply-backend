<?php

namespace App\Models;

class Token extends \Laravel\Passport\Token
{

    protected $connection = 'mysql'; // Master connection
}
