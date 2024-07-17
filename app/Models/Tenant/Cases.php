<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model // Cases is on purpose because Case can not be used a class name
{
    use HasFactory;

    protected $connection = 'tenant'; // Set connection on tenant database
}