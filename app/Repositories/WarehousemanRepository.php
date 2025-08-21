<?php
namespace App\Repositories;

use App\Models\User;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Parcel;
use App\Models\FixedCost;
use App\Models\ParcelItem;
use App\Models\ParcelManage;
use App\Models\Country;
use App\Models\DeliveryStaff;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class WarehousemanRepository
{
    public function getCustomers($customerCode = null)
    {
        $query = User::whereIn('role', ['customer', 'company']);

        if ($customerCode) {
            $query->where('customer_code', $customerCode);
        }

        return $query->get();
    }

    public function getSuppliers($search = null)
    {
        $query = Supplier::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }


  


    public function createShipment(array $data)
    {
        return Shipment::create($data);
    }

    public function getRepackingCost()
    {
        $fixedCost = FixedCost::where('name', 'cost_of_repacking')
                              ->where('is_active', true)
                              ->first();
        return $fixedCost ? $fixedCost->value : null;
    }

    public function getFragileCost()
    {
        $fixedCost = FixedCost::where('name', 'cost_of_is_fragile')
                              ->where('is_active', true)
                              ->first();
        return $fixedCost ? $fixedCost->value : null;
    }

    public function createParcel(array $data)
    {
        return Parcel::create($data);
    }

    public function createParcelItem(array $data)
    {
        return ParcelItem::create($data);
    }

    public function getParcelItemsByParcelId($parcel_id)
    {
        return ParcelItem::where('parcel_id', $parcel_id)->get();
    }

    public function getAllowedParcelContent()
    {
        return ParcelManage::where('is_allowed', true)->get();
    }

    public function updateShipment($shipmentId, array $data)
    {
        $shipment = Shipment::findOrFail($shipmentId);
        $shipment->update($data);
        return $shipment->fresh();
    }

    public function findShipmentById($shipmentId)
    {
        return Shipment::find($shipmentId);
    }


    public function getInProcessShipments()
    {
        $currentUser = auth()->user();

        return Shipment::where('status', 'in_process')
            ->where('delivered_to_WH_dis', false)
            ->whereHas('originCountry', function($query) use ($currentUser) {
                $query->where('name', $currentUser->address);
            })
            ->with(['customer', 'supplier', 'originCountry', 'destinationCountry', 'creator'])
            ->get();
    }

    public function getInTheWayShipments()
    {
        $currentUser = auth()->user();

        return Shipment::where('status', 'in_the_way')
            // ->where('delivered_to_WH_dis', true)
            ->whereNull('warehouse_received_at') // Exclude shipments that have been received at warehouse
            ->whereHas('destinationCountry', function($query) use ($currentUser) {
                $query->where('name', $currentUser->address);
            })
            ->with(['customer', 'supplier', 'originCountry', 'destinationCountry', 'creator'])
            ->get();
    }

    public function getAllParcels()
    {
        return Parcel::select([
            'parcels.id',
            'parcels.shipment_id',
            'parcels.actual_weight',
            'parcels.length',
            'parcels.width',
            'parcels.height',
            'users.id as customer_id',
            'users.first_name',
            'users.last_name'
        ])
        ->join('shipments', 'parcels.shipment_id', '=', 'shipments.id')
        ->join('users', 'shipments.customer_id', '=', 'users.id')
        ->withCount('items')
        ->get();
    }

    public function getParcelsByShipmentId($shipment_id)
    {
        return Parcel::select([
            'parcels.id',
            // 'parcels.shipment_id',
            'parcels.actual_weight',
            'parcels.length',
            'parcels.width',
            'parcels.height',
            'parcels.status',
            'users.id as customer_id',
            'users.first_name',
            'users.last_name'
        ])
        ->join('shipments', 'parcels.shipment_id', '=', 'shipments.id')
        ->join('users', 'shipments.customer_id', '=', 'users.id')
        ->where('parcels.shipment_id', $shipment_id)
        ->withCount('items')
        ->get();
    }

    public function getParcelByParcelId($parcel_id)
    {
        return Parcel::with([
            'shipment',
            'shipment.customer',
        ])->findOrFail($parcel_id);
    }

    public function findParcelById($parcel_id)
    {
        return Parcel::find($parcel_id);
    }

    public function getEnabledCountries()
    {
        return Country::where('is_enabled', 1)->get();
    }

    public function updateInvoiceWithAdditionalCosts($invoice, array $additionalCosts)
    {
        $updateData = [];

        if (isset($additionalCosts['cost_of_repacking'])) {
            $updateData['cost_of_repacking'] = ($invoice->cost_of_repacking ?? 0) + $additionalCosts['cost_of_repacking'];
        }
        if (isset($additionalCosts['cost_of_is_fragile'])) {
            $updateData['cost_of_is_fragile'] = ($invoice->cost_of_is_fragile ?? 0) + $additionalCosts['cost_of_is_fragile'];
        }


        return $invoice->update($updateData);
    }

    public function updateInvoiceForParcel($parcel, array $additionalCosts)
    {
        if (!empty($additionalCosts)) {
            $shipment = $parcel->shipment;
            if ($shipment && $shipment->invoice) {
                $invoice = $shipment->invoice;
                return $this->updateInvoiceWithAdditionalCosts($invoice, $additionalCosts);
            }
        }
        return false;
    }

    public function getDrivers()
    {
        return DeliveryStaff::select('id', 'name')->get();
    }

    public function getDeliveryDestinationCost()
    {
        $fixedCost = FixedCost::where('name', 'cost_delivery_destination')
                              ->where('is_active', true)
                              ->first();
        return $fixedCost ? $fixedCost->value : null;
    }

    public function updateInvoiceWithDeliveryCost($shipment_id, $deliveryCost)
    {
        $shipment = Shipment::with('invoice')->findOrFail($shipment_id);

        if ($shipment->invoice) {
            $invoice = $shipment->invoice;

            $updateData = [
                'cost_delivery_destination' => $deliveryCost,

            ];

            return $invoice->update($updateData);
        }

        return false;
    }

    public function getShipmentDetails($shipment_id)
    {
        $shipment = Shipment::with(['customer', 'supplier', 'parcels', 'invoice'])
            ->findOrFail($shipment_id);

        // Calculate total weight from parcels (kg), using the greater of actual/new_actual and dimensional weight
        $totalWeight = 0.0;
        foreach ($shipment->parcels as $parcel) {
            $dimensionalWeight = ($parcel->length * $parcel->width * $parcel->height) / 5000;
            $finalWeight = max(
                $parcel->new_actual_weight ?? $parcel->actual_weight ?? 0,
                $dimensionalWeight
            );
            $totalWeight += $finalWeight;
        }

        return [
            'shipment_date' => $shipment->created_at->format('Y-m-d'),
            'customer_name' => $shipment->customer ? ($shipment->customer->first_name . ' ' . $shipment->customer->last_name) : null,
            'customer_phone' => $shipment->customer->phone ?? null,
            'supplier_name' => optional($shipment->supplier)->name,
            'supplier_number' => optional($shipment->supplier)->phone,
            'receipt_number' => $shipment->invoice ? $shipment->invoice->id : null,
            'shipment_code' => $shipment->tracking_number,
            'parcels_count' => $shipment->actual_parcels_count ?? $shipment->declared_parcels_count,
            'total_weight' => round($totalWeight, 2),
            'notes' => $shipment->notes
        ];
    }

        public function getWarehouseCosts()
    {
        $costs = [];
        $missingCosts = [];

        $costNames = [
            'cost_delivery_origin',
            'cost_express_origin',
            'cost_customs_origin',
            'cost_air_freight'
        ];

        foreach ($costNames as $costName) {
            $fixedCost = FixedCost::where('name', $costName)
                                  ->where('is_active', true)
                                  ->first();
            if ($fixedCost) {
                $costs[$costName] = $fixedCost->value;
            } else {
                $missingCosts[] = $costName;
            }
        }

        return [
            'costs' => $costs,
            'missing_costs' => $missingCosts
        ];
    }

    public function updateInvoiceWithWarehouseCosts($shipment_id, array $costs)
    {
        $shipment = Shipment::with('invoice')->findOrFail($shipment_id);

        if ($shipment->invoice) {
            $invoice = $shipment->invoice;

            $updateData = [];


            foreach ($costs as $costName => $costValue) {
                $updateData[$costName] = $costValue;

            }



            return $invoice->update($updateData);
        }

        return false;
    }

    public function updateParcel($parcelId, array $data)
    {
        $parcel = Parcel::findOrFail($parcelId);
        $parcel->update($data);
        return $parcel->fresh();
    }

    public function getDeliverableShipments($customerCode = null)
    {
        $query = Shipment::with(['customer', 'parcels'])
            ->where('delivered_to_WH_dis', true)
            ->where('mark_as_delivered', false) // Exclude delivered shipments
            ->whereHas('parcels', function($parcelQuery) {
                $parcelQuery->where('status', 'deliverable');
            })
            ->whereDoesntHave('parcels', function($parcelQuery) {
                $parcelQuery->where('status', '!=', 'deliverable');
            });

        if ($customerCode) {
            $query->whereHas('customer', function($customerQuery) use ($customerCode) {
                $customerQuery->where('customer_code', $customerCode);
            });
        }

        return $query->get()->map(function ($shipment) {
            return [
                'customer_name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'customer_code' => $shipment->customer->customer_code,
                'shipment_created_at' => $shipment->created_at->format('Y-m-d H:i:s'),
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number
            ];
        });
    }

    public function markShipmentAsDelivered($shipmentId, array $data)
    {
        $shipment = Shipment::findOrFail($shipmentId);

        $updateData = [
            'mark_as_delivered' => true,
            'delivered_at' => now(),
            'status' => 'delivered'
        ];

        if (isset($data['delivery_photo'])) {
            $updateData['delivery_photo'] = $data['delivery_photo'];
        }

        $shipment->update($updateData);
        return $shipment->fresh();
    }

    public function getInvoiceDetails($shipmentId)
    {
        $shipment = Shipment::with(['invoice', 'customer', 'parcels'])->findOrFail($shipmentId);

        if (!$shipment->invoice) {
            return null;
        }

        $invoice = $shipment->invoice;

        // Calculate total weight from parcels
        $totalWeight = 0;
        foreach ($shipment->parcels as $parcel) {
            // Calculate dimensional weight
            $dimensionalWeight = ($parcel->length * $parcel->width * $parcel->height) / 5000;

            // Use the greater of new_actual_weight or dimensional weight
            $finalWeight = max(
                $parcel->new_actual_weight ?? $parcel->actual_weight ?? 0,
                $dimensionalWeight
            );

            $totalWeight += $finalWeight;
        }

        // Calculate air freight cost (using kg)
        $airFreightCost = $totalWeight * ($invoice->cost_air_freight ?? 0);

        // Calculate total costs
        $totalCosts = [
            'amount' => $invoice->amount ?? 0,
            'tax_amount' => $invoice->tax_amount ?? 0,
            'cost_of_repacking' => $invoice->cost_of_repacking ?? 0,
            'cost_of_is_fragile' => $invoice->cost_of_is_fragile ?? 0,
            'cost_delivery_origin' => $invoice->cost_delivery_origin ?? 0,
            'cost_express_origin' => $invoice->cost_express_origin ?? 0,
            'cost_customs_origin' => $invoice->cost_customs_origin ?? 0,
            'cost_delivery_destination' => $invoice->cost_delivery_destination ?? 0,
            'air_freight_cost' => $airFreightCost
        ];

        $grandTotal = array_sum($totalCosts);

        return [
            'invoice_details' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'currency' => $invoice->currency,
                'status' => $invoice->status,
                // 'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                'payable_at' => $invoice->payable_at ? Carbon::parse($invoice->payable_at)->format('Y-m-d') : null,
                'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d') : null,
                'payment_method' => $invoice->payment_method,
                'includes_tax' => $invoice->includes_tax,
                'adjustment_reason' => $invoice->adjustment_reason,
                'adjusted_amount' => $invoice->adjusted_amount,
                // 'file_path' => $invoice->file_path
            ],
            'shipment_details' => [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'status' => $shipment->status,
                'created_at' => $shipment->created_at->format('Y-m-d H:i:s')
            ],
            'customer_details' => [
                'id' => $shipment->customer->id,
                'name' => $shipment->customer->first_name . ' ' . $shipment->customer->last_name,
                'customer_code' => $shipment->customer->customer_code,
                'email' => $shipment->customer->email,
                'phone' => $shipment->customer->phone
            ],
            'costs_breakdown' => $totalCosts,
            'total_weight' => round($totalWeight, 2),
            'grand_total' => $grandTotal
        ];
    }

}
