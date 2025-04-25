<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string|unique:users,phone',
            'gender'     => 'nullable|string',
            'address'    => 'nullable|string',
            'timezone'   => 'nullable|string',
            'profile_photo_path'=> 'nullable|image',
            'identity_photo_path'=>'nullable|image',
        ]);

       return $this->authService->register($validated);

    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        return $this->authService->login($validated);
    }
}

