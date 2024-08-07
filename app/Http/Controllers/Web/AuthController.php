<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signIn(Request $request){
        $credetials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if(Auth::attempt($credetials)){
            return redirect()->route('docs');
        }

        return view('signIn');
    }
}
