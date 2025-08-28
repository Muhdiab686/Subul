<?php

namespace App\Http\Controllers;

use App\Services\WarehousemanService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function getSuppliers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $search = $request->input('search');
        return $this->warehousemanService->getSuppliers($search);
    }

    public function createShipment(Request $request)
    {
       $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:ship_pay,ship_only,pay_only',
            'customer_id' => 'required|exists:users,id',
            'supplier_ids' => 'required|array|min:1', // تعديل هنا لقبول مصفوفة
            'supplier_ids.*' => 'exists:suppliers,id', // كل عنصر بالمصفوفة لازم يكون موجود في جدول الموردين
            'origin_country_id' => 'required|exists:countries,id',
            'destination_country_id' => 'required|exists:countries,id',
            'declared_parcels_count' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->createShipment($validator->validated());
    }

    public function updateShipmentOriginCountry($shipment_id, $origin_country_id)
    {
        $validator = Validator::make(
            ['shipment_id' => $shipment_id, 'origin_country_id' => $origin_country_id],
            [
                'shipment_id' => 'required|exists:shipments,id',
                'origin_country_id' => 'required|exists:countries,id'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->updateShipmentOriginCountry($validator->validated());
    }

    public function updateShipmentDestinationCountry($shipment_id, $destination_country_id)
    {
        $validator = Validator::make(
            ['shipment_id' => $shipment_id, 'destination_country_id' => $destination_country_id],
            [
                'shipment_id' => 'required|exists:shipments,id',
                'destination_country_id' => 'required|exists:countries,id'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->updateShipmentDestinationCountry($validator->validated());
    }

    public function getInProcessShipments()
    {
        return $this->warehousemanService->getInProcessShipments();
    }

    public function getInTheWayShipments()
    {
        return $this->warehousemanService->getInTheWayShipments();
    }

    public function updateShipmentCosts(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'sent_at' => 'nullable|date',
                'delivered_at' => 'nullable|date',
                'cost_delivery_origin' => 'nullable|numeric|min:0',
                'cost_express_origin' => 'nullable|numeric|min:0',
                'cost_customs_origin' => 'nullable|numeric|min:0',
                'cost_air_freight' => 'nullable|numeric|min:0',
                'cost_delivery_destination' => 'nullable|numeric|min:0',
                'mark_as_delivered' => 'nullable|boolean'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->updateShipmentCosts($shipment_id, $validator->validated());
    }

    public function createParcel(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'actual_weight' => 'required|numeric|min:0',
                'length' => 'required|numeric|min:0',
                'width' => 'required|numeric|min:0',
                'height' => 'required|numeric|min:0',
                'scale_photo_path' => 'nullable|string',
                'brand_type' => 'required|string',
                'is_fragile' => 'required|boolean',
                'needs_repacking' => 'required|boolean',
                'notes' => 'nullable|string',
                'print_notes' => 'nullable|string'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->createParcel($validator->validated());
    }

    public function createMultipleParcels(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'parcels' => 'required|array|min:1',
                'parcels.*.actual_weight' => 'required|numeric|min:0',
                'parcels.*.length' => 'required|numeric|min:0',
                'parcels.*.width' => 'required|numeric|min:0',
                'parcels.*.height' => 'required|numeric|min:0',
                'parcels.*.scale_photo_upload' => 'nullable|image|max:2048',
                'parcels.*.brand_type' => 'required|string',
                'parcels.*.is_fragile' => 'required|boolean',
                'parcels.*.needs_repacking' => 'required|boolean',
                'parcels.*.notes' => 'nullable|string',
                'parcels.*.print_notes' => 'nullable|string',
                'parcels.*.items' => 'nullable|array',
                'parcels.*.items.*.item_type' => 'required_with:parcels.*.items|string|max:255',
                'parcels.*.items.*.quantity' => 'required_with:parcels.*.items|integer|min:1',
                'parcels.*.items.*.value_per_item' => 'nullable|numeric|min:0',
                'parcels.*.items.*.description' => 'nullable|string'
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->warehousemanService->createMultipleParcels($validator->validated());
    }

    public function getAllParcels()
    {
        return $this->warehousemanService->getAllParcels();
    }

    public function getParcelsByShipmentId($shipment_id)
    {
        $validator = Validator::make(
            ['shipment_id' => $shipment_id],
            ['shipment_id' => 'required|exists:shipments,id']
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->getParcelsByShipmentId($shipment_id);
    }

    public function getParcelByParcelId($parcel_id)
    {
        $validator = Validator::make(
            ['parcel_id' => $parcel_id],
            ['parcel_id' => 'required|exists:parcels,id']
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->getParcelByParcelId($parcel_id);
    }

    public function createParcelItem(Request $request, $parcel_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['parcel_id' => $parcel_id]),
            [
                'parcel_id' => 'required|exists:parcels,id',
                'item_type' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'value_per_item' => 'nullable|numeric|min:0',
                'description' => 'nullable|string'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->createParcelItem($validator->validated());
    }

    public function getParcelItemsByParcelId($parcel_id)
    {
        $validator = Validator::make(
            ['parcel_id' => $parcel_id],
            ['parcel_id' => 'required|exists:parcels,id']
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->getParcelItemsByParcelId($parcel_id);
    }

    public function getAllowedParcelContent()
    {
        return $this->warehousemanService->getAllowedParcelContent();
    }

    public function getEnabledCountries()
    {
        return $this->warehousemanService->getEnabledCountries();
    }

    public function getDrivers()
    {
        return $this->warehousemanService->getDrivers();
    }

    public function updateShipmentForDelivery(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'actual_parcels_count' => 'required|integer|min:1',
                'delivery_staff_id' => 'required|exists:delivery_staff,id',
                'shipment_photo' => 'nullable|image|max:2048'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->updateShipmentForDelivery($shipment_id, $validator->validated());
    }

    public function getShipmentDetails($shipment_id)
    {
        $validator = Validator::make(
            ['shipment_id' => $shipment_id],
            ['shipment_id' => 'required|exists:shipments,id']
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->getShipmentDetails($shipment_id);
    }

    public function updateShipmentWarehouseArrival(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'airport_receipt_photo' => 'nullable|image|max:2048'
            ]
        );
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }
        return $this->warehousemanService->updateShipmentWarehouseArrival($shipment_id, $validator->validated());
    }

    public function updateParcelInfo(Request $request, $parcel_id)
    {
        $validator = Validator::make($request->all(), [
            'is_opened' => 'required|boolean',
            'opened_notes' => 'nullable|string',
            'is_damaged' => 'required|boolean',
            'damaged_notes' => 'nullable|string',
            'new_actual_weight' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'updated_scale_photo_upload' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->warehousemanService->updateParcelInfo($parcel_id, $validator->validated());
    }

    public function getDeliverableShipments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_code' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $customerCode = $request->input('customer_code');
        return $this->warehousemanService->getDeliverableShipments($customerCode);
    }

    public function markShipmentAsDelivered(Request $request, $shipment_id)
    {
        $validator = Validator::make(
            array_merge($request->all(), ['shipment_id' => $shipment_id]),
            [
                'shipment_id' => 'required|exists:shipments,id',
                'delivery_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->warehousemanService->markShipmentAsDelivered($shipment_id, $validator->validated());
    }

    public function getInvoiceDetails($shipment_id)
    {
        $validator = Validator::make(
            ['shipment_id' => $shipment_id],
            ['shipment_id' => 'required|exists:shipments,id']
        );

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->warehousemanService->getInvoiceDetails($shipment_id);
    }
    public function getInvoicemy()
    {
        $useId = Auth()->user()->id;
        return $this->warehousemanService->getInvoicemy($useId);
    }

}
