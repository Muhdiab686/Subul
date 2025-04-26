<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Shipment;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;


class WarehousemanRepository
{
    public function getCustomers($customerCode = null)
    {
        $query = User::whereIn('role', ['customer', 'company_client']);

        if ($customerCode) {
            $query->where('customer_code', $customerCode);
        }

        return $query->get();
    }


    public function createSupplier(array $data)
    {
        return Supplier::create($data);
    }


    public function createShipment(array $data)
    {
        return Shipment::create($data);
    }
}
