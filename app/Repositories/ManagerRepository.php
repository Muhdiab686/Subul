<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Shipment;
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
}
