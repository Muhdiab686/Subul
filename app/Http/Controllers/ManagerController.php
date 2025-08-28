<?php

namespace App\Http\Controllers;

use App\Services\ManagerService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|string|min:6',
            'phone'      => 'nullable|string|unique:users,phone',
            'gender'     => 'nullable|string',
            'address'    => 'nullable|string',
            'timezone'   => 'nullable|string',
            'profile_photo_path'=> 'nullable|image',
            'identity_photo_path'=>'nullable|image',
            'is_company' => 'required|boolean',
            'parent_company_id' => 'nullable|exists:users,id|prohibited_if:is_company,1',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->managerService->addCustomer($validator->validated());
    }

    public function getAllCustomers(Request $request)
{
    $search = $request->input('search');
    return $this->managerService->getAllCustomers($search);
}

    public function getAllCompanies(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'customer_code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->managerService->getAllCompanies($validator->validated());
    }

    public function deleteCustomer($id)
    {
        return $this->managerService->deleteCustomer($id);
    }

    public function getAllShipments()
    {
        return $this->managerService->getAllShipments();
    }

    public function get_approved_Shipments(Request $request)
{
    $search = $request->input('search');
    return $this->managerService->get_approved_Shipments($search);
}

public function get_unapproved_Shipments(Request $request)
{
    $search = $request->input('search');
    return $this->managerService->get_unapproved_Shipments($search);
}

    public function approveShipment($id)
{
    $validator = Validator::make(['shipment_id' => $id], [
        'shipment_id' => 'required|exists:shipments,id'
    ]);

    if ($validator->fails()) {
        return $this->errorResponse('Validation error', 422, $validator->errors());
    }

    return $this->managerService->approveShipment($id);
}


    public function getCustomerShipments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->managerService->getCustomerShipments($validator->validated()['customer_code']);
    }

    public function rejectShipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipment_id' => 'required|exists:shipments,id',
            'cancellation_reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->managerService->rejectShipment($validator->validated());
    }

    public function getRejectedShipments()
    {
        return $this->managerService->getRejectedShipments();
    }

    public function getShipmentById($shipment_id)
    {
        return $this->managerService->getShipmentById($shipment_id);
    }

    public function createInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id'   => 'required|exists:users,id',
            'shipment_id'   => 'required|exists:shipments,id',
            'amount'        => 'nullable|numeric|min:0',
            'includes_tax'  => 'required|boolean',
            'payable_at'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->managerService->createInvoice($validator->validated());
    }




    public function getInvoiceDetails($invoice_id)
    {
        return $this->managerService->getInvoiceDetails($invoice_id);
    }
}
