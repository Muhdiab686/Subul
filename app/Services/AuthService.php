<?php
namespace App\Services;

use App\Repositories\AuthRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    use ApiResponseTrait;
    protected $userRepo;

    public function __construct(AuthRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data)
    {
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'profile_photo_path' => $data['profile_photo_path'] ?? null,
            'identity_photo_path' => $data['identity_photo_path'] ?? null,
            'status' => 0,
        ];
        $data = $this->userRepo->createUser($userData);
        return $this->successResponse($data,'Successfuly',200);
    }

    public function login(array $credentials): string
    {
        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
       
        return $this->successResponse($token,'Successfuly',200);
    }
}
