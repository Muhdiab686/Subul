<?php
namespace App\Services;

use App\Repositories\ManagerRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;


class ManagerService
{
    use ApiResponseTrait;
    protected $managerRepository;

    public function __construct(ManagerRepository $managerRepository)
    {
        $this->managerRepository = $managerRepository;
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

    public function addCustomer(array $data)
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
            'last_name' => $data['last_name'],
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
            'role' => $data['is_company'] ? 'company_client' : 'customer',
        ];

        $data = $this->managerRepository->createCustomer($userData);
        return $this->successResponse($data,'user Successfuly created',200);


    }


        public function getAllCustomers()
    {
        $data = $this->managerRepository->getAllCustomers();
        return $this->successResponse($data,'user Successfuly created',200);
    }


    public function deleteCustomer($id)
    {
        $customer = $this->managerRepository->findCustomer($id);

        if (!$customer) {
            return $this->errorResponse('customar not found', 404);
        }

        if (!in_array($customer->role, ['customer', 'company_client'])) {
            return $this->errorResponse('you are not Authorized', 403);
        }

        $this->managerRepository->deleteCustomer($customer);
        return $this->successResponse(null,'user Successfuly removed',200);
    }




}
