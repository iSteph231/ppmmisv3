<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequestRequest;
use App\Models\AccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegistrationForm(Request $request): View
    {
        $request->session()->forget(['pending_registration', 'registration_email']);

        return view('auth.register');
    }

    public function register(StoreAccountRequestRequest $request): RedirectResponse
    {
        $request->session()->forget(['pending_registration', 'registration_email']);
        AccountRequest::create($request->validated());

        return redirect()->route('register')->with('success',
            'Your account request has been submitted. After admin approval, you will receive an email link to set your password.');
    }
}
