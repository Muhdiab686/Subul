<?php

namespace App\Http\Controllers;

use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;


class AdminController extends Controller
{
    use ApiResponseTrait;

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }


    // Parcel Content Methods
    public function getContentParcel()
    {
        return $this->adminService->getcontentpracel();
    }

    public function storeContentParcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'is_allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->storecontentpracel($validator->validated());
    }

    public function updatePermission(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updatePermission($validator->validated(), $id);
    }

    // Country Methods
    public function getCountry()
    {
        return $this->adminService->getCountry();
    }

    public function storeCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:9|unique:countries,code',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createCountry($validator->validated());
    }

    public function toggleStatusCountry(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->changeStatus($id, $validator->validated()['is_enabled']);
    }

    // Delivery Methods
    public function indexDelivery()
    {
        return $this->adminService->getAllDelivery();
    }

    public function storeDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'job_title' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createDelivery($validator->validated());
    }

    public function updateDelivery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'phone' => 'sometimes|required|string|max:20',
            'job_title' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updateDelivery($id, $validator->validated());
    }

    public function destroyDelivery($id)
    {
        return $this->adminService->deleteDelivery($id);
    }

    // Fixed Cost Methods
    public function storeFixedCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fixed_costs,name',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createFixedCost($validator->validated());
    }


    // User Methods
    public function getUsers()
    {
        return $this->adminService->getUsers();
    }

    public function deleteUser($id)
    {
        return $this->adminService->deleteUser($id);
    }

    public function updateUserRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,manager,warehouseman,company,customer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updateUserRole($id, $validator->validated()['role']);
    }
}
