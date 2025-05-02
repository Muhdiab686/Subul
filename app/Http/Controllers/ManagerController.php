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


    public function get_approved_Shipments()
    {
        return $this->managerService->get_approved_Shipments();
    }

    public function get_unapproved_Shipments()
    {
        return $this->managerService->get_unapproved_Shipments();
    }


    public function getCustomerShipments(Request $request)
    {
        $validated = $request->validate([
            'customer_code' => 'required|string|max:255'
        ]);

        return $this->managerService->getCustomerShipments($validated['customer_code']);
    }

    public function rejectShipment(Request $request)
    {
        $validated = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'cancellation_reason' => 'required|string|max:255'
        ]);

        return $this->managerService->rejectShipment($validated);
    }

    public function getRejectedShipments()
{
    return $this->managerService->getRejectedShipments();
}

    public function getShipmentById($shipment_id)
    {

        $validated = ['shipment_id' => $shipment_id];

        return $this->managerService->getShipmentById($shipment_id);
    }

    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'shipment_id' => 'required|exists:shipments,id',
            'amount' => 'required|numeric|min:0',
            'includes_tax' => 'required|boolean',
            'tax_amount' => 'required_if:includes_tax,true|numeric|min:0',
            'payable_at' => 'required|date'
        ]);

        return $this->managerService->createInvoice($validated);
    }

    public function getInvoiceDetails($invoice_id)
{
    return $this->managerService->getInvoiceDetails($invoice_id);
}

}
