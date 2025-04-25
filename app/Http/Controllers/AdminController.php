<?php

namespace App\Http\Controllers;

use App\Models\ParcelManage;
use App\Services\AdminService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    public function getcontentpracel()
    {
        return $this->adminService->getcontentpracel();
    }
    
    public function storecontentpracel(Request $request)
    {
        $validation =  $request->validate([
            'content' => 'required|string',
            'is_allowed' => 'required|boolean',
        ]);
        return $this->adminService->storecontentpracel($validation);
    }

    public function updatePermission(Request $request,$id){
        $validation = $request->validate([
            'is_allowed' => 'required|boolean',
        ]);
        return $this->adminService->updatePermission($validation, $id);
    }

    // country //

    public function getCountry()
    {
        return $this->adminService->getCountry();
    }

    public function storeCountry(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:9|unique:countries,code',
        ]);
        
       return $this->adminService->createCountry($data);

    }

    public function toggleStatusCountry(Request $request, $id)
    {
        $request->validate([
            'is_enabled' => 'required|boolean',
        ]);

        return $this->adminService->changeStatus($id, $request->is_enabled);


    }
    // Delivery //
    public function indexDelivery()
    {
        return $this->adminService->getAllDelivery();
    }

    public function storeDelivery(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
        ]);

        return $this->adminService->createDelivery($data);
    }

    public function updateDelivery(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'phone' => 'sometimes|required|string|max:20',
        ]);

        return $this->adminService->updateDelivery($id, $data);
    }

    public function destroyDelivery($id)
    {
        return $this->adminService->deleteDelivery($id);
    }
}
