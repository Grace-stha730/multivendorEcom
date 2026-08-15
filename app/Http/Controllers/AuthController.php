<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $email = strtolower($googleUser->getEmail());

            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                Auth::guard('web')->login($existingUser);
                request()->session()->regenerate();

                return redirect()->route('home')->with('success', 'Welcome back. You are logged in.');
            }

            $code = (string) random_int(100000, 999999);
            Mail::mailer('smtp')->raw("Your verification code is: {$code}\n\nIt expires in 10 minutes.", function ($message) use ($email) {
                $message->to($email)->subject('Verify your account');
            });

            session()->put('pending_registration', [
                'name' => $googleUser->getName() ?: strtok($email, '@'),
                'email' => $email,
                'photo' => $googleUser->getAvatar(),
                'otp' => Hash::make($code),
                'expires_at' => now()->addMinutes(10)->timestamp,
                'verified' => false,
            ]);

            return redirect()->route('user.verify-otp');
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('user.register')->with('error', 'Google sign-in could not be completed. Please try again.');
        }
    }

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
