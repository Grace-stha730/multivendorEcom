<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function logout(){
        Auth::guard('vendor')->logout();
        return redirect()->route('vendor.login')->with('success','Logged out successfully');
    }

    public function userlogout(){
        Auth::guard('web')->logout();
        return redirect()->route('home')->with('success','Logged out successfully');
    }

    public function adminLogout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('success','Logged out successfully');
    }
}
