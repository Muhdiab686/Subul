<?php

namespace App\Services;

use App\Repositories\WarehousemanRepository;
use App\Traits\ApiResponseTrait;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Repositories\ManagerRepository;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class WarehousemanService
{
    use ApiResponseTrait;

    protected $warehousemanRepository;
    protected $managerRepository;
    protected $managerService;

    public function __construct(WarehousemanRepository $warehousemanRepository, ManagerRepository $managerRepository, ManagerService $managerService)
    {
        $this->warehousemanRepository = $warehousemanRepository;
        $this->managerRepository = $managerRepository;
        $this->managerService = $managerService;
    }

    public function getCustomers($customerCode = null)
    {
        $data = $this->warehousemanRepository->getCustomers($customerCode);

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No customers found.', 200);
        }
        return $this->successResponse($data, 'Customers retrieved successfully.', 200);
    }

    public function getSuppliers($search = null)
    {
        try {
            $data = $this->warehousemanRepository->getSuppliers($search);

            if ($data->isEmpty()) {
                $message = $search
                    ? "No suppliers found matching: {$search}"
                    : 'No suppliers found.';
                return $this->successResponse($data, $message, 200);
            }

            $message = $search
                ? "Suppliers retrieved successfully matching: {$search}"
                : 'Suppliers retrieved successfully.';

            return $this->successResponse($data, $message, 200);
        } catch (\Exception $e) {
            Log::error("Failed to get suppliers", [
                'error' => $e->getMessage(),
                'search' => $search
            ]);
            return $this->errorResponse('Failed to retrieve suppliers', 500);
        }
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
    $supplierIds = $data['supplier_ids'] ?? [$data['supplier_id']];
    $shipments = [];

    foreach ($supplierIds as $supplierId) {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return $this->errorResponse("Supplier with ID {$supplierId} not found", 404);
        }

        $isWarehouseman = auth()->user()->role === 'warehouseman';

        $shipmentData = [
            'type' => $data['type'],
            'customer_id' => $data['customer_id'],
            'supplier_id' => $supplierId,
            'origin_country_id' => $data['origin_country_id'],
            'destination_country_id' => $data['destination_country_id'],
            'declared_parcels_count' => $data['declared_parcels_count'],
            'notes' => $data['notes'] ?? null,
            'status' => 'in_process', // ✅ دايمًا in_process
            'created_by_user_id' => auth()->id(),
            'tracking_number' => $this->generateTrackingNumber(),
            'is_approved' => $isWarehouseman, // ✅ فقط المستودع يوافق مباشرة
        ];

        $shipment = $this->warehousemanRepository->createShipment($shipmentData);
        $shipments[] = $shipment;

        // ✅ إذا warehouseman + ship_only → إنشاء الفاتورة مباشرة
        if ($isWarehouseman && $shipment->type === 'ship_only') {
            $invoiceData = [
                'customer_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'amount' => $shipment->declared_parcels_count * 10, // مثال للسعر
                'includes_tax' => true,
                'payable_at' => now()->addDays(7)->format('Y-m-d'),
            ];
            $this->managerService->createInvoice($invoiceData);
        }
    }

    return $this->successResponse($shipments, 'Shipments created successfully.', 200);
}

    public function updateShipmentOriginCountry(array $data)
    {
        $data = $this->warehousemanRepository->updateShipment(
            $data['shipment_id'],
            ['origin_country_id' => $data['origin_country_id']]
        );

        return $this->successResponse($data, 'Origin country updated successfully.', 200);
    }

    public function updateShipmentDestinationCountry(array $data)
    {
        $data = $this->warehousemanRepository->updateShipment(
            $data['shipment_id'],
            ['destination_country_id' => $data['destination_country_id']]
        );


        return $this->successResponse($data, 'Destination country updated successfully.', 200);
    }

    public function getInProcessShipments()
    {
        $shipments = $this->warehousemanRepository->getInProcessShipments();

        if ($shipments->isEmpty()) {
            $data = collect([]);
            return $this->successResponse($data, 'No in-process shipments found.', 200);
        }

        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_name' => optional($shipment->customer)->first_name && optional($shipment->customer)->last_name
                    ? optional($shipment->customer)->first_name . ' ' . optional($shipment->customer)->last_name
                    : null,
                'supplier_name' => optional($shipment->supplier)->name,
                'supplier_number' => optional($shipment->supplier)->phone,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'origin_country' => optional($shipment->originCountry)->code,
                'destination_country' => optional($shipment->destinationCountry)->code,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data, 'In-process shipments retrieved successfully.', 200);
    }

    public function getInTheWayShipments()
    {
        $shipments = $this->warehousemanRepository->getInTheWayShipments();

        if ($shipments->isEmpty()) {
            $data = collect([]);
            return $this->successResponse($data, 'No in-the-way shipments found.', 200);
        }

        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_name' => optional($shipment->customer)->first_name && optional($shipment->customer)->last_name
                    ? optional($shipment->customer)->first_name . ' ' . optional($shipment->customer)->last_name
                    : null,
                'supplier_name' => optional($shipment->supplier)->name,
                'supplier_number' => optional($shipment->supplier)->phone,
                'status' => $shipment->status,
                'actual_parcels_count' => $shipment->actual_parcels_count,
                'origin_country' => optional($shipment->originCountry)->code,
                'destination_country' => optional($shipment->destinationCountry)->code,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data, 'In-the-way shipments retrieved successfully.', 200);
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
            $updateData['delivered_at'] = now();
        }

        $data = $this->warehousemanRepository->updateShipment($shipment_id, $updateData);

        return $this->successResponse($data, 'Shipment costs updated successfully.', 200);
    }

    public function createParcel(array $data)
    {

        $shipment = $this->warehousemanRepository->findShipmentById($data['shipment_id']);
        if (!$shipment) {
            return $this->errorResponse('Shipment not found', 404);
        }

        $currentParcelsCount = $this->warehousemanRepository->getParcelsByShipmentId($data['shipment_id'])->count();
        if ($currentParcelsCount >= (int) $shipment->declared_parcels_count) {
            return $this->errorResponse('Cannot create more parcels than the declared_parcels_count for this shipment.', 422);
        }

        $dimensionalWeight = ($data['length'] * $data['width'] * $data['height']) / 5000; // kg

        $finalWeight = max(
            $data['actual_weight'],
            $dimensionalWeight
        );

        // Handle scale photo path - copy from local path and store in project
        $ScalePhotoPath = null;
        if (isset($data['scale_photo_path']) && !empty($data['scale_photo_path'])) {
            try {
                // Check if file exists at the provided path
                if (file_exists($data['scale_photo_path'])) {
                    $filename = Str::uuid() . '.png'; // Default to png

                    // Try to determine file extension from path
                    $pathInfo = pathinfo($data['scale_photo_path']);
                    if (isset($pathInfo['extension'])) {
                        $filename = Str::uuid() . '.' . $pathInfo['extension'];
                    }

                    $localPath = public_path('/uploads/scale_photos/' . $filename);

                    // Create directory if it doesn't exist
                    if (!file_exists(dirname($localPath))) {
                        mkdir(dirname($localPath), 0755, true);
                    }

                    // Copy file from source to destination
                    if (copy($data['scale_photo_path'], $localPath)) {
                        $ScalePhotoPath = '/uploads/scale_photos/' . $filename;
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue without image
                Log::warning('Failed to copy image from: ' . $data['scale_photo_path'], ['error' => $e->getMessage()]);
            }
        }
        $data['scale_photo_upload'] = $ScalePhotoPath;

        $parcel = $this->warehousemanRepository->createParcel($data);

        $additionalCosts = [];

        if ($data['needs_repacking'] == 1) {
            $repackingCost = $this->warehousemanRepository->getRepackingCost();
            if ($repackingCost) {
                $additionalCosts['cost_of_repacking'] = $repackingCost;
            } else {
                return $this->errorResponse('Repacking cost not found in fixed costs.', 422);
            }
        }

        if ($data['is_fragile'] == 1) {
            $fragileCost = $this->warehousemanRepository->getFragileCost();
            if ($fragileCost) {
                $additionalCosts['cost_of_is_fragile'] = $fragileCost;
            } else {
                return $this->errorResponse('Fragile cost not found in fixed costs.', 422);
            }
        }

        $this->warehousemanRepository->updateInvoiceForParcel($parcel, $additionalCosts);

        $responseData = [
            'parcel' => $parcel,
            'dimensional_weight' => round($dimensionalWeight, 2),
            'final_weight' => round($finalWeight, 2)
        ];

        return $this->successResponse($responseData, 'Parcel created successfully.', 200);
    }

    public function createMultipleParcels(array $data)
    {
        $shipment = $this->warehousemanRepository->findShipmentById($data['shipment_id']);
        if (!$shipment) {
            return $this->errorResponse('Shipment not found', 404);
        }

        $currentParcelsCount = $this->warehousemanRepository->getParcelsByShipmentId($data['shipment_id'])->count();
        $requestedParcelsCount = count($data['parcels']);

        if (($currentParcelsCount + $requestedParcelsCount) > (int) $shipment->declared_parcels_count) {
            return $this->errorResponse('Cannot create more parcels than the declared_parcels_count for this shipment.', 422);
        }

        $createdParcels = [];
        $errors = [];

        foreach ($data['parcels'] as $index => $parcelData) {
            try {
                // Add shipment_id to parcel data
                $parcelData['shipment_id'] = $data['shipment_id'];

                // Calculate dimensional weight
                $dimensionalWeight = ($parcelData['length'] * $parcelData['width'] * $parcelData['height']) / 5000; // kg

                $finalWeight = max(
                    $parcelData['actual_weight'],
                    $dimensionalWeight
                );

                // Handle scale photo upload - store uploaded file
                $scalePhotoPath = null;
                if (isset($parcelData['scale_photo_upload']) && $parcelData['scale_photo_upload'] instanceof \Illuminate\Http\UploadedFile) {
                    $filename = Str::uuid() . '.' . $parcelData['scale_photo_upload']->getClientOriginalExtension();

                    // Create directory if it doesn't exist
                    $uploadPath = public_path('/uploads/scale_photos');
                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    // Move uploaded file to destination
                    if ($parcelData['scale_photo_upload']->move($uploadPath, $filename)) {
                        $scalePhotoPath = '/uploads/scale_photos/' . $filename;
                    }
                }
                $parcelData['scale_photo_upload'] = $scalePhotoPath;

                // Create parcel
                $parcel = $this->warehousemanRepository->createParcel($parcelData);

                // Handle additional costs
                $additionalCosts = [];
                if ($parcelData['needs_repacking'] == 1) {
                    $repackingCost = $this->warehousemanRepository->getRepackingCost();
                    if ($repackingCost) {
                        $additionalCosts['cost_of_repacking'] = $repackingCost;
                    }
                }

                if ($parcelData['is_fragile'] == 1) {
                    $fragileCost = $this->warehousemanRepository->getFragileCost();
                    if ($fragileCost) {
                        $additionalCosts['cost_of_is_fragile'] = $fragileCost;
                    }
                }

                if (!empty($additionalCosts)) {
                    $this->warehousemanRepository->updateInvoiceForParcel($parcel, $additionalCosts);
                }

                // Create parcel items if provided
                $parcelItems = [];
                if (isset($parcelData['items']) && is_array($parcelData['items'])) {
                    foreach ($parcelData['items'] as $itemData) {
                        $itemData['parcel_id'] = $parcel->id;
                        $parcelItem = $this->warehousemanRepository->createParcelItem($itemData);
                        $parcelItems[] = $parcelItem;
                    }
                }

                $createdParcels[] = [
                    'parcel' => [
                        'id' => $parcel->id,
                        'shipment_id' => $parcel->shipment_id,
                        'actual_weight' => $parcel->actual_weight,
                        'length' => $parcel->length,
                        'width' => $parcel->width,
                        'height' => $parcel->height,
                        'brand_type' => $parcel->brand_type,
                        'is_fragile' => (bool) $parcel->is_fragile,
                        'needs_repacking' => (bool) $parcel->needs_repacking,
                        'notes' => $parcel->notes,
                        'scale_photo_upload' => $parcel->scale_photo_upload,
                        'status' => $parcel->status,
                        'created_at' => $parcel->created_at,
                        'updated_at' => $parcel->updated_at
                    ],
                    'dimensional_weight' => round($dimensionalWeight, 2),
                    'final_weight' => round($finalWeight, 2)
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (!empty($errors)) {
            return $this->errorResponse('Some parcels failed to create', 422, [
                'created_parcels' => $createdParcels,
                'errors' => $errors
            ]);
        }

        return $this->successResponse([
            'shipment_id' => $data['shipment_id'],
            'created_parcels' => $createdParcels,
            'total_created' => count($createdParcels)
        ], 'Multiple parcels created successfully.', 200);
    }

    public function createParcelItem(array $data)
    {

        $allowedContent = $this->warehousemanRepository->getAllowedParcelContent();
        $allowedTypes = $allowedContent->pluck('content')->toArray();

        if (!in_array($data['item_type'], $allowedTypes)) {
            return $this->errorResponse('Item type is not allowed. Please choose from the allowed content types.', 422);
        }

        $data = $this->warehousemanRepository->createParcelItem($data);

        return $this->successResponse($data, 'Parcel item created successfully.', 200);
    }

    public function getParcelItemsByParcelId($parcel_id)
    {
        $data = $this->warehousemanRepository->getParcelItemsByParcelId($parcel_id);

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No parcel items found for this parcel.', 200);
        }
        return $this->successResponse($data, 'Parcel items retrieved successfully.', 200);
    }

    public function getAllowedParcelContent()
    {
        $data = $this->warehousemanRepository->getAllowedParcelContent();

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No allowed parcel content found.', 200);
        }
        return $this->successResponse($data, 'Allowed parcel content retrieved successfully.', 200);
    }

    public function getAllParcels()
    {
        $data = $this->warehousemanRepository->getAllParcels();

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No parcels found.', 200);
        }
        return $this->successResponse($data, 'Parcels retrieved successfully.', 200);
    }

    public function getParcelsByShipmentId($shipment_id)
    {
        // Fetch shipment to read declared_parcels_count
        $shipment = $this->warehousemanRepository->findShipmentById($shipment_id);
        if (!$shipment) {
            return $this->errorResponse('Shipment not found.', 404);
        }

        $parcels = $this->warehousemanRepository->getParcelsByShipmentId($shipment_id);

        // Append dimensional_weight for each parcel
        $parcelsWithDimensionalWeight = $parcels->map(function ($parcel) {
            $dimensionalWeight = ($parcel->length * $parcel->width * $parcel->height) / 5000;
            $parcelArray = $parcel->toArray();
            $parcelArray['dimensional_weight'] = round($dimensionalWeight, 2);
            if (isset($parcelArray['actual_weight'])) {
                $parcelArray['actual_weight'] = round((float) $parcelArray['actual_weight'], 2);
            }
            if (isset($parcelArray['new_actual_weight'])) {
                $parcelArray['new_actual_weight'] = round((float) $parcelArray['new_actual_weight'], 2);
            }
            return $parcelArray;
        });

        $response = [
            'shipment_id' => $shipment->id,
            'declared_parcels_count' => $shipment->declared_parcels_count,
            'created_parcels_count' => $parcels->count(),
            'parcels' => $parcelsWithDimensionalWeight,
        ];

        $message = $parcels->isEmpty()
            ? 'No parcels found for this shipment.'
            : 'Parcels for shipment retrieved successfully.';

        return $this->successResponse($response, $message, 200);
    }

    public function getParcelByParcelId($parcel_id)
    {
        $data = $this->warehousemanRepository->getParcelByParcelId($parcel_id);

        if (!$data) {
            return $this->successResponse($data, 'Parcel not found.', 200);
        }
        return $this->successResponse($data, 'Parcel retrieved successfully.', 200);
    }

    public function getEnabledCountries()
    {
        $data = $this->warehousemanRepository->getEnabledCountries();

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No enabled countries found.', 200);
        }
        return $this->successResponse($data, 'Enabled countries retrieved successfully.', 200);
    }

    public function getDrivers()
    {
        $data = $this->warehousemanRepository->getDrivers();

        if ($data->isEmpty()) {
            return $this->successResponse($data, 'No drivers found.', 200);
        }
        return $this->successResponse($data, 'Drivers retrieved successfully.', 200);
    }

    public function updateShipmentForDelivery($shipment_id, array $data)
    {

        $shipmentPhotoPath = null;
        if (isset($data['shipment_photo'])) {
            $filename = Str::uuid() . '.' . $data['shipment_photo']->getClientOriginalExtension();
            $data['shipment_photo']->move(public_path('/uploads/shipment_photos'), $filename);
            $shipmentPhotoPath = '/uploads/shipment_photos/' . $filename;
        }


        $updateData = [
            'actual_parcels_count' => $data['actual_parcels_count'],
            'delivery_staff_id' => $data['delivery_staff_id'],
            'status' => 'in_the_way'
        ];

        if ($shipmentPhotoPath) {
            $updateData['shipment_photo'] = $shipmentPhotoPath;
        }

        // Update shipment
        $shipment = $this->warehousemanRepository->updateShipment($shipment_id, $updateData);

        $deliveryDestinationCost = $this->warehousemanRepository->getDeliveryDestinationCost();

        if (!$deliveryDestinationCost) {
            return $this->errorResponse('Delivery destination cost not found in fixed costs.', 422);
        }

        $this->warehousemanRepository->updateInvoiceWithDeliveryCost($shipment_id, $deliveryDestinationCost);

        return $this->successResponse($shipment, 'Shipment updated for delivery successfully.', 200);
    }

    public function getShipmentDetails($shipment_id)
    {
        $data = $this->warehousemanRepository->getShipmentDetails($shipment_id);

        if (!$data) {
            return $this->errorResponse('Shipment not found.', 404);
        }

        return $this->successResponse($data, 'Shipment details retrieved successfully.', 200);
    }

    public function updateShipmentWarehouseArrival($shipment_id, array $data)
    {

        $existingShipment = $this->warehousemanRepository->findShipmentById($shipment_id);
        if (!$existingShipment) {
            return $this->errorResponse('Shipment not found.', 404);
        }

        $airportReceiptPhotoPath = null;
        if (isset($data['airport_receipt_photo'])) {
            $filename = Str::uuid() . '.' . $data['airport_receipt_photo']->getClientOriginalExtension();
            $data['airport_receipt_photo']->move(public_path('/uploads/airport_receipt_photos'), $filename);
            $airportReceiptPhotoPath = '/uploads/airport_receipt_photos/' . $filename;
        }

        $updateData = [
            'warehouse_received_at' => now(),
            'warehouse_receiver_id' => auth()->id(),
            'delivered_to_WH_dis' => true
        ];

        if ($airportReceiptPhotoPath) {
            $updateData['airport_receipt_photo'] = $airportReceiptPhotoPath;
        }

        $shipment = $this->warehousemanRepository->updateShipment($shipment_id, $updateData);

        // Get warehouse costs
        $costsData = $this->warehousemanRepository->getWarehouseCosts();

        if (!empty($costsData['missing_costs'])) {
            $missingCostsText = implode(', ', $costsData['missing_costs']);
            return $this->errorResponse("The following warehouse costs are not found in fixed costs: {$missingCostsText}", 422);
        }

        $this->warehousemanRepository->updateInvoiceWithWarehouseCosts($shipment_id, $costsData['costs']);

        return $this->successResponse($shipment, 'Shipment warehouse arrival updated successfully.', 200);
    }

    public function updateParcelInfo($parcel_id, array $data)
    {
        // Check if parcel exists
        $parcel = $this->warehousemanRepository->findParcelById($parcel_id);
        if (!$parcel) {
            return $this->errorResponse('Parcel not found', 404);
        }


        $updatedScalePhotoPath = null;
        if (isset($data['updated_scale_photo_upload'])) {
            $filename = Str::uuid() . '.' . $data['updated_scale_photo_upload']->getClientOriginalExtension();
            $data['updated_scale_photo_upload']->move(public_path('/uploads/scale_photos'), $filename);
            $updatedScalePhotoPath = '/uploads/scale_photos/' . $filename;
        }

        $updateData = [
            'is_opened' => $data['is_opened'],
            'opened_notes' => $data['opened_notes'] ?? null,
            'is_damaged' => $data['is_damaged'],
            'damaged_notes' => $data['damaged_notes'] ?? null,
            'new_actual_weight' => $data['new_actual_weight'] ?? null,
            'status' => $data['status']
        ];


        if ($updatedScalePhotoPath) {
            $updateData['updated_scale_photo_upload'] = $updatedScalePhotoPath;
        }

        try {
            $parcel = $this->warehousemanRepository->updateParcel($parcel_id, $updateData);
            return $this->successResponse($parcel, 'Parcel info updated successfully.', 200);
        } catch (\Exception $e) {
            Log::error("Failed to update parcel info for parcel ID: {$parcel_id}", [
                'error' => $e->getMessage(),
                'data' => $updateData
            ]);
            return $this->errorResponse('Failed to update parcel info', 500);
        }
    }

    public function getDeliverableShipments($customerCode = null)
    {
        try {
            $data = $this->warehousemanRepository->getDeliverableShipments($customerCode);

            if ($data->isEmpty()) {
                $message = $customerCode
                    ? "No deliverable shipments found for customer code: {$customerCode}"
                    : 'No deliverable shipments found.';
                return $this->successResponse($data, $message, 200);
            }

            $message = $customerCode
                ? "Deliverable shipments retrieved successfully for customer code: {$customerCode}"
                : 'Deliverable shipments retrieved successfully.';

            return $this->successResponse($data, $message, 200);
        } catch (\Exception $e) {
            Log::error("Failed to get deliverable shipments", [
                'error' => $e->getMessage(),
                'customer_code' => $customerCode
            ]);
            return $this->errorResponse('Failed to retrieve deliverable shipments', 500);
        }
    }

    public function markShipmentAsDelivered($shipment_id, array $data)
    {
        try {
            // Check if shipment exists and is deliverable
            $shipment = $this->warehousemanRepository->findShipmentById($shipment_id);
            if (!$shipment) {
                return $this->errorResponse('Shipment not found', 404);
            }

            // Check if shipment is already delivered
            if ($shipment->mark_as_delivered) {
                return $this->errorResponse('Shipment is already marked as delivered', 422);
            }

            // Check if shipment is deliverable (delivered_to_WH_dis = true and all parcels are deliverable)
            if (!$shipment->delivered_to_WH_dis) {
                return $this->errorResponse('Shipment is not ready for delivery', 422);
            }

            $hasNonDeliverableParcels = $shipment->parcels()
                ->where('status', '!=', 'deliverable')
                ->exists();

            if ($hasNonDeliverableParcels) {
                return $this->errorResponse('Shipment contains non-deliverable parcels', 422);
            }

            // Handle delivery photo upload
            $deliveryPhotoPath = null;
            if (isset($data['delivery_photo'])) {
                // Create directory if it doesn't exist
                $uploadPath = public_path('/uploads/delivery_photos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $filename = Str::uuid() . '.' . $data['delivery_photo']->getClientOriginalExtension();
                $data['delivery_photo']->move($uploadPath, $filename);
                $deliveryPhotoPath = '/uploads/delivery_photos/' . $filename;
            }

            $updateData = [];
            if ($deliveryPhotoPath) {
                $updateData['delivery_photo'] = $deliveryPhotoPath;
            }

            $shipment = $this->warehousemanRepository->markShipmentAsDelivered($shipment_id, $updateData);

            return $this->successResponse($shipment, 'Shipment marked as delivered successfully.', 200);
        } catch (\Exception $e) {
            Log::error("Failed to mark shipment as delivered", [
                'error' => $e->getMessage(),
                'shipment_id' => $shipment_id
            ]);
            return $this->errorResponse('Failed to mark shipment as delivered', 500);
        }
    }

    public function getInvoiceDetails($shipment_id)
    {
        try {
            // Check if shipment exists
            $shipment = $this->warehousemanRepository->findShipmentById($shipment_id);
            if (!$shipment) {
                return $this->errorResponse('Shipment not found', 404);
            }

            $invoiceData = $this->warehousemanRepository->getInvoiceDetails($shipment_id);

            if (!$invoiceData) {
                return $this->errorResponse('Invoice not found for this shipment', 404);
            }

            return $this->successResponse($invoiceData, 'Invoice details retrieved successfully.', 200);
        } catch (\Exception $e) {
            Log::error("Failed to get invoice details", [
                'error' => $e->getMessage(),
                'shipment_id' => $shipment_id
            ]);
            return $this->errorResponse('Failed to retrieve invoice details', 500);
        }
    }
    public function getInvoicemy($useId)
    {

        $invoiceData = $this->warehousemanRepository->getInvoicemy($useId);
        if (!$invoiceData) {
            return $this->errorResponse('Invoice not found for this shipment', 404);
        }

        return $this->successResponse($invoiceData, 'Invoice details retrieved successfully.', 200);
    }
}
