<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Parcel;
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

    public function createParcel(array $data)
    {
        return Parcel::create($data);
    }

    public function updateShipment($shipmentId, array $data)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $shipment->update($data);
        return $shipment->fresh();
    }


    public function getInProcessShipments()
    {
        return Shipment::where('status', 'in_the_way')
            ->where('delivered_to_WH_dis', false)
            ->with(['customer', 'supplier', 'originCountry', 'destinationCountry', 'creator'])
            ->get();
    }

    public function getAllParcels()
    {
        return Parcel::with([
            'shipment',
            'shipment.customer',
            'shipment.supplier',
            'shipment.originCountry',
            'shipment.destinationCountry',
        ])->get();
    }

    public function getParcelsByShipmentId($shipment_id)
    {
        return Parcel::with([
            'shipment',
            'shipment.customer',
            'shipment.supplier',
            'shipment.originCountry',
            'shipment.destinationCountry',
        ])->where('shipment_id', $shipment_id)->get();
    }

    public function getParcelByParcelId($parcel_id)
    {
        return Parcel::with([
            'shipment',
            'shipment.customer',
            'shipment.supplier',
            'shipment.originCountry',
            'shipment.destinationCountry',
        ])->findOrFail($parcel_id);
    }

}
