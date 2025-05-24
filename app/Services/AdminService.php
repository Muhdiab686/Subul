<?php
namespace App\Services;

use App\Repositories\AdminRepository;
use App\Traits\ApiResponseTrait;

class AdminService{
    use ApiResponseTrait;

    protected $adminRepo;

    public function __construct(AdminRepository $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    public function getcontentpracel(){
        $data =  $this->adminRepo->getcontentpracel();
        return $this->successResponse($data,'Successfuly',200);
    }
    public function storecontentpracel(array $data){
       $praceldata = [
            "content"=> $data["content"],
            "is_allowed"=> $data["is_allowed"]
       ];
        $data = $this->adminRepo->createcontentp($praceldata);
        return $this->successResponse($data,'Successfuly',200);
    }
    public function updatePermission(array $data,$id){
        $praceldata = [
            "is_allowed"=> $data["is_allowed"]
       ];
       $data = $this->adminRepo->findByIdpracel($id);
       $data->is_allowed = $praceldata['is_allowed'];
       $data->save();
       return $this->successResponse($data,'Successfuly',200);
    }
    // country //
    public function getCountry(){
        $data =  $this->adminRepo->getCountry();
        return $this->successResponse($data,'Successfuly',200);

    }
    public function createCountry(array $data)
    {
        $data0 =  $this->adminRepo->createCountry($data);
        return $this->successResponse($data0,'Successfuly',200);

    }

    public function changeStatus(int $id, bool $status)
    {
        $data = $this->adminRepo->updateStatusCountry($id, $status);
        return $this->successResponse($data,'Successfuly',200);
    }

    // Delivery //
    public function createDelivery(array $data)
    {
        return $this->successResponse($this->adminRepo->createDelivery($data),'Successfuly',200);

    }

    public function updateDelivery(int $id, array $data)
    {
        return $this->successResponse($this->adminRepo->updateDelivery($id, $data)
        ,'Successfuly',200);
    }

    public function deleteDelivery(int $id)
    {
        return $this->successResponse($this->adminRepo->deleteDelivery($id),'Successfuly',200);
    }

    public function getAllDelivery()
    {
        return $this->successResponse($this->adminRepo->getAllDelivery(),'Successfuly',200);
    }

    public function createFixedCost(array $data)
    {
        $data['created_by_user_id'] = auth()->id();
        $data = $this->adminRepo->createFixedCost($data);
        return $this->successResponse($data,'Successfuly',200);
    }
}
