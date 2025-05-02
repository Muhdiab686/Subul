<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Shipment;
use App\Models\QrCode;
use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;

class ManagerRepository
{
    public function createCustomer(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    public function getAllCustomers()
    {
        return User::whereIn('role', ['customer', 'company_client'])->get();
    }


    public function findCustomer($id)
    {
        return User::find($id);
    }

    public function deleteCustomer(User $user)
    {
        return $user->delete();
    }

    public function get_approved_Shipments()
    {
        return Shipment::where('is_approved', true)
                       ->where('status', 'in_process')
                       ->get();
    }

    public function get_unapproved_Shipments()
    {
        return Shipment::where('is_approved', false)
                       ->where('status', 'in_process')
                       ->get();
    }

    public function getCustomerShipments($customerCode)
    {
        return Shipment::whereHas('customer', function($query) use ($customerCode) {
            $query->where('customer_code', $customerCode);
        })->get();
    }


    public function rejectShipment($shipmentId, $cancellationReason)
    {
        $shipment = Shipment::findOrFail($shipmentId);

        $shipment->update([
            'status' => 'rejected',
            'cancellation_reason' => $cancellationReason
        ]);

        return $shipment;
    }

    public function getRejectedShipments()
    {
        return Shipment::where('status', 'rejected')->get();
    }

    public function getShipmentById($shipmentId)
    {
        return Shipment::findOrFail($shipmentId);
    }

    public function createQRCode(array $data)
    {
        return QrCode::create($data);
    }

    public function createInvoice(array $data)
    {
        return Invoice::create($data);
    }

    public function updateShipmentStatus($shipmentId, $status)
    {
        return Shipment::where('id', $shipmentId)
            ->update(['status' => $status]);
    }

    public function getLastInvoice()
    {
        return Invoice::orderBy('id', 'desc')->first();
    }

    public function getInvoiceWithDetails($invoice_id)
{
    return Invoice::with(['customer', 'qrcode'])
        ->where('id', $invoice_id)
        ->first();
}
}
