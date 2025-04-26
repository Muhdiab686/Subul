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
}
