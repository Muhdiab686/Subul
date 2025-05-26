<?php
namespace App\Services;

use App\Repositories\WarehousemanRepository;
use App\Traits\ApiResponseTrait;
use App\Models\Shipment;
use App\Models\Supplier;
use Illuminate\Support\Str;

class WarehousemanService
{
    use ApiResponseTrait;
    protected $warehousemanRepository;

    public function __construct(WarehousemanRepository $warehousemanRepository)
    {
        $this->warehousemanRepository = $warehousemanRepository;
    }


    public function getCustomers($customerCode = null)
    {
        $data = $this->warehousemanRepository->getCustomers($customerCode);
        return $this->successResponse($data,' Successfuly ',200);
    }


    private function generateTrackingNumber()
    {
        $prefix = 'SBL';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(4));

        $trackingNumber = $prefix . $timestamp . $random;


        while (Shipment::where('tracking_number', $trackingNumber)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingNumber = $prefix . $timestamp . $random;
        }

        return $trackingNumber;
    }


    public function createShipment(array $data)
    {
        $existingSupplier = Supplier::where('name', $data['supplier_name'])->first();

        if (!$existingSupplier) {
            $supplier = $this->warehousemanRepository->createSupplier([
                'name' => $data['supplier_name'],
                'phone' => $data['supplier_number']
            ]);

            $supplierId = $supplier->id;
        } else {
            $supplierId = $existingSupplier->id;
        }


        $isWarehouseman = auth()->user()->role === 'warehouseman';

        $shipmentData = [
            'type' => $data['type'],
            'customer_id' => $data['customer_id'],
            'supplier_id' => $supplierId,
            'supplier_name' => $data['supplier_name'],
            'supplier_number' => $data['supplier_number'],
            'declared_parcels_count' => $data['declared_parcels_count'],
            'notes' => $data['notes'] ?? null,
            'status' => 'in_process',
            'created_by_user_id' => auth()->id(),
            'tracking_number' => $this->generateTrackingNumber(),
            'is_approved' => $isWarehouseman,
        ];

        $data = $this->warehousemanRepository->createShipment($shipmentData);
        return $this->successResponse($data,' Successfuly ',200);
    }


    public function updateShipmentOriginCountry(array $data)
    {
        $data = $this->warehousemanRepository->updateShipment(
            $data['shipment_id'],
            ['origin_country_id' => $data['origin_country_id']]
        );

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function updateShipmentDestinationCountry(array $data)
    {
        $data = $this->warehousemanRepository->updateShipment(
            $data['shipment_id'],
            ['destination_country_id' => $data['destination_country_id']]
        );

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getInProcessShipments()
    {
        $data = $this->warehousemanRepository->getInProcessShipments();
        return $this->successResponse($data,' Successfuly ',200);
    }

    public function updateShipmentCosts($shipment_id, array $data)
    {
        $updateData = array_filter([
            'sent_at' => $data['sent_at'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? null,
            'cost_delivery_origin' => $data['cost_delivery_origin'] ?? null,
            'cost_express_origin' => $data['cost_express_origin'] ?? null,
            'cost_customs_origin' => $data['cost_customs_origin'] ?? null,
            'cost_air_freight' => $data['cost_air_freight'] ?? null,
            'cost_delivery_destination' => $data['cost_delivery_destination'] ?? null,
        ]);


        if (!empty($data['mark_as_delivered']) && $data['mark_as_delivered'] === true) {
            $updateData['status'] = 'delivered';
        }

        $data = $this->warehousemanRepository->updateShipment($shipment_id, $updateData);

        return $this->successResponse($data,' Successfuly ',200);
    }

    public function createParcel(array $data)
    {

        $dimensionalWeight = ($data['length'] * $data['width'] * $data['height']) / 5000;


        $finalWeight = max(
            $data['actual_weight'],
            $dimensionalWeight
        );


        $data['calculated_dimensional_weight'] = $dimensionalWeight;
        $data['calculated_final_weight'] = $finalWeight;


        $ScalePhotoPath = null;
        if (isset($data['scale_photo_upload'])) {
            $filename = Str::uuid() . '.' . $data['scale_photo_upload']->getClientOriginalExtension();
            $data['scale_photo_upload']->move(public_path('/uploads/scale_photos'), $filename);
            $ScalePhotoPath = '/uploads/scale_photos/' . $filename;
        }
        $data['scale_photo_upload'] = $ScalePhotoPath;


        if ($data['needs_repacking']) {
            $repackingCost = $this->warehousemanRepository->getRepackingCost();
            if ($repackingCost === null) {
                return $this->errorResponse('تكلفة إعادة التعبئة غير محددة في النظام', 404);
            }
            $data['cost_of_repacking'] = $repackingCost;
        } else {
            $data['cost_of_repacking'] = null;
        }


        $data = $this->warehousemanRepository->createParcel($data);

        return $this->successResponse($data,' Successfuly ',200);
    }


    public function getAllParcels()
    {
        $data = $this->warehousemanRepository->getAllParcels();
        return $this->successResponse($data,' Successfuly ',200);
    }

    public function getParcelsByShipmentId($shipment_id)
    {
        $data = $this->warehousemanRepository->getParcelsByShipmentId($shipment_id);
        return $this->successResponse($data,' Successfuly ',200);
    }
    public function getParcelByParcelId($parcel_id)
    {
        $data = $this->warehousemanRepository->getParcelByParcelId($parcel_id);
        return $this->successResponse($data,' Successfuly ',200);
    }
}
