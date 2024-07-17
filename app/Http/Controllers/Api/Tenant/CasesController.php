<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Cases;
use Illuminate\Http\Response as HttpResponse;

class CasesController extends Controller
{
    public function index(): HttpResponse
    {
        $cases = Cases::all();

        return response($cases);
    }
}