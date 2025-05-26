<?php
namespace App\Http\Controllers;

use App\Services\WarehousemanService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class WarehousemanController extends Controller
{
    use ApiResponseTrait;

    protected $warehousemanService;

    public function __construct(WarehousemanService $warehousemanService)
    {
        $this->warehousemanService = $warehousemanService;

    }


    public function getCustomers(Request $request)
    {
        $customerCode = $request->input('customer_code');
        return $this->warehousemanService->getCustomers($customerCode);
    }


    public function createShipment(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:ship_pay,ship_only,pay_only',
            'customer_id' => 'required|exists:users,id',
            'supplier_name' => 'required|string|max:255',
            'supplier_number' => 'required|string|max:255',
            'declared_parcels_count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        return $this->warehousemanService->createShipment($validated);
    }

    public function updateShipmentOriginCountry($shipment_id, $origin_country_id)
    {
        $validated = [
            'shipment_id' => $shipment_id,
            'origin_country_id' => $origin_country_id
        ];

        validator($validated, [
            'shipment_id' => 'required|exists:shipments,id',
            'origin_country_id' => 'required|exists:countries,id'
        ])->validate();

        return $this->warehousemanService->updateShipmentOriginCountry($validated);
    }

    public function updateShipmentDestinationCountry($shipment_id, $destination_country_id)
    {
        $validated = [
            'shipment_id' => $shipment_id,
            'destination_country_id' => $destination_country_id
        ];

        validator($validated, [
            'shipment_id' => 'required|exists:shipments,id',
            'destination_country_id' => 'required|exists:countries,id'
        ])->validate();

        return $this->warehousemanService->updateShipmentDestinationCountry($validated);
    }

    public function getInProcessShipments()
    {
        return $this->warehousemanService->getInProcessShipments();
    }


    public function updateShipmentCosts(Request $request, $shipment_id)
    {
        $validated = $request->validate([
            'sent_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
            'cost_delivery_origin' => 'nullable|numeric|min:0',
            'cost_express_origin' => 'nullable|numeric|min:0',
            'cost_customs_origin' => 'nullable|numeric|min:0',
            'cost_air_freight' => 'nullable|numeric|min:0',
            'cost_delivery_destination' => 'nullable|numeric|min:0',
            'mark_as_delivered' => 'nullable|boolean'
        ]);

        return $this->warehousemanService->updateShipmentCosts($shipment_id, $validated);
    }

    public function createParcel(Request $request, $shipment_id)
    {
        $validated = $request->validate([
            'actual_weight' => 'required|numeric|min:0',
            'special_actual_weight' => 'required|numeric|min:0',
            'normal_actual_weight' => 'required|numeric|min:0',
            'special_dimensional_weight' => 'required|numeric|min:0',
            'normal_dimensional_weight' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'scale_photo_upload' => 'nullable|image',
            'brand_type' => 'required|string',
            'is_fragile' => 'required|boolean',
            'needs_repacking' => 'required|boolean',
            'notes' => 'nullable|string',
            'print_notes' => 'nullable|string'
        ]);

        $validated['shipment_id'] = $shipment_id;

        return $this->warehousemanService->createParcel($validated);
    }

    public function getAllParcels()
    {
        return $this->warehousemanService->getAllParcels();
    }

    public function getParcelsByShipmentId($shipment_id)
    {

        validator(['shipment_id' => $shipment_id], [
            'shipment_id' => 'required|exists:shipments,id'
        ])->validate();

        return $this->warehousemanService->getParcelsByShipmentId($shipment_id);
    }

    public function getParcelByParcelId($parcel_id)
    {
        validator(['parcel_id' => $parcel_id], [
            'parcel_id' => 'required|exists:parcels,id'
        ])->validate();

        return $this->warehousemanService->getParcelByParcelId($parcel_id);
    }
}
