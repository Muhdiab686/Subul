<?php
namespace App\Services;

use App\Repositories\ManagerRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Str;
use App\Models\Shipment;

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
        return $this->successResponse($data,' Successfuly ',200);
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




    public function get_approved_Shipments()
    {

        $shipments = $this->managerRepository->get_approved_Shipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_id' => $shipment->customer_id,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function get_unapproved_Shipments()
    {

        $shipments = $this->managerRepository->get_unapproved_Shipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_id' => $shipment->customer_id,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getCustomerShipments($customerCode)
    {

        $shipments = $this->managerRepository->getCustomerShipments($customerCode);


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function rejectShipment(array $data)
    {
        $data = $this->managerRepository->rejectShipment(
            $data['shipment_id'],
            $data['cancellation_reason']
        );

        return $this->successResponse($data,' Successfuly ',200);
    }


    public function getRejectedShipments()
    {
        $shipments = $this->managerRepository->getRejectedShipments();


        $shipments->load('customer:id,first_name,last_name');


        $data = $shipments->map(function ($shipment) {
            return [
                'tracking_number' => $shipment->tracking_number,
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'type' => $shipment->type,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'status' => $shipment->status,
                'cancellation_reason' => $shipment->cancellation_reason,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getShipmentById($shipmentId)
    {
        $shipment = $this->managerRepository->getShipmentById($shipmentId);

        
        $shipment->load('customer:id,first_name,last_name');


        $data = [
            'id' => $shipment->id,
            'tracking_number' => $shipment->tracking_number,
            'type' => $shipment->type,
            'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
            'supplier_name' => $shipment->supplier_name,
            'supplier_number' => $shipment->supplier_number,
            'status' => $shipment->status,
            'declared_parcels_count' => $shipment->declared_parcels_count,
            'actual_parcels_count' => $shipment->actual_parcels_count,
            'notes' => $shipment->notes,
            'created_at' => $shipment->created_at
        ];

        return $this->successResponse($data,' Successfuly ',200);
    }
}
