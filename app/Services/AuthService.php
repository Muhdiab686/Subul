<?php
namespace App\Services;

use App\Models\User;
use App\Repositories\AuthRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;


class AuthService
{
    use ApiResponseTrait;
    protected $userRepo;

    public function __construct(AuthRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    private function generateCustomerCode()
    {
        $letters = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2));
        $numbers = rand(100, 999);
        $suffix = rand(10, 99);
        return "$letters-$numbers-$suffix";
    }
    private function generateVerifiedCode()
    {
        return rand(100000, 999999);
    }


    public function register(array $data)
    {
        $customerCode = $this->generateCustomerCode();
        $verifiedCode = $this->generateVerifiedCode();


    $profilePhotoPath = null;
    if (isset($data['profile_photo_path'])) {
        $filename = Str::uuid() . '.' . $data['profile_photo_path']->getClientOriginalExtension();
        $data['profile_photo_path']->move(public_path('/uploads/profile_photos'), $filename);
        $profilePhotoPath = '/uploads/profile_photos/' . $filename;
    }

    $identityPhotoPath = null;
    if (isset($data['identity_photo_path'])) {
        $filename = Str::uuid() . '.' . $data['identity_photo_path']->getClientOriginalExtension();
        $data['identity_photo_path']->move(public_path('/uploads/identity_photos'), $filename);
        $identityPhotoPath = '/uploads/identity_photos/' . $filename;
    }

        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address'=>$data['address'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'profile_photo_path' => $profilePhotoPath,
            'identity_photo_path' => $identityPhotoPath,
            'status' => 1,
            'customer_code' => $customerCode,
            'verified_code' => $verifiedCode,
        ];
        $data = $this->userRepo->createUser($userData);
        $role = User::where('email' ,$data['email'])->first();
        $data['role'] = $role->role ;
        return $this->successResponse($data,'Successfuly',200);
    }

    public function login(array $credentials)
    {
        // نحذف fcm_token من الcredentials قبل محاولة الدخول
        $fcmToken = $credentials['fcm_token'] ?? null;
        unset($credentials['fcm_token']);

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        // تحديث fcm_token إذا مرسل
        if ($fcmToken) {
            $user->FCM_TOKEN = $fcmToken;
            $user->save();
        }

        $data = [
            'user'  => $user,
            'role'  => $user->role,
            'token' => $token,
            'fcm_token' =>$user->FCM_TOKEN
        ];

        return $this->successResponse($data, 'Successfully', 200);
    }

}
