<?php
namespace App\Http\Controllers;

use App\Services\ManagerService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Models\User;

class ManagerController extends Controller
{
    use ApiResponseTrait;

    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    public function addCustomer(Request $request)
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
            'is_company' => 'required|boolean',
        ]);

       return $this->managerService->addCustomer($validated);
    }


    public function getAllCustomers()
    {
        return $this->managerService->getAllCustomers();
    }


    public function deleteCustomer($id)
    {
        return $this->managerService->deleteCustomer($id);
    }




}
