<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\OptInUser;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'uid' => 'required',
        ]);

        $user = OptInUser::where('email', $request->email)
            ->where('uid', $request->uid)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Invalid email or UID. Please check your information.',
            ]);
        }

        Session::put('opt_in_user_id', $user->id);

        return back();
    }

    public function logout()
    {
        Session::forget('opt_in_user_id');
        return redirect()->route('landing');
    }
}
