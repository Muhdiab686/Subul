<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Shipment;
use App\Repositories\AdminRepository;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Exception;


class AdminService
{
    use ApiResponseTrait;

    protected $adminRepo;

    public function __construct(AdminRepository $adminRepo)
    {
        $this->adminRepo = $adminRepo;
    }

    public function getcontentpracel()
    {
        $data =  $this->adminRepo->getcontentpracel();
        return $this->successResponse($data, 'Successfuly', 200);
    }
    public function storecontentpracel(array $data)
    {
        $praceldata = [
            "content" => $data["content"],
            "is_allowed" => $data["is_allowed"]
        ];
        $data = $this->adminRepo->createcontentp($praceldata);
        return $this->successResponse($data, 'Successfuly', 200);
    }
    public function updatePermission(array $data, $id)
    {
        $praceldata = [
            "is_allowed" => $data["is_allowed"]
        ];
        $data = $this->adminRepo->findByIdpracel($id);
        $data->is_allowed = $praceldata['is_allowed'];
        $data->save();
        return $this->successResponse($data, 'Successfuly', 200);
    }
    // country //
    public function getCountry()
    {
        $data =  $this->adminRepo->getCountry();
        return $this->successResponse($data, 'Successfuly', 200);
    }
    public function createCountry(array $data)
    {
        $data0 =  $this->adminRepo->createCountry($data);
        return $this->successResponse($data0, 'Successfuly', 200);
    }

    public function changeStatus(int $id, bool $status)
    {
        $data = $this->adminRepo->updateStatusCountry($id, $status);
        return $this->successResponse($data, 'Successfuly', 200);
    }

    // Delivery //
    public function createDelivery(array $data)
    {
        return $this->successResponse($this->adminRepo->createDelivery($data), 'Successfuly', 200);
    }
    public function createSupplir($data)
    {
        return $this->successResponse($this->adminRepo->createSupplier($data), 'Successfuly', 200);
    }

    public function updateDelivery(int $id, array $data)
    {
        return $this->successResponse(
            $this->adminRepo->updateDelivery($id, $data),
            'Successfuly',
            200
        );
    }

    public function deleteDelivery(int $id)
    {
        return $this->successResponse($this->adminRepo->deleteDelivery($id), 'Successfuly', 200);
    }

    public function getAllDelivery()
    {
        return $this->successResponse($this->adminRepo->getAllDelivery(), 'Successfuly', 200);
    }

    public function createFixedCost(array $data)
    {
        $data['created_by_user_id'] = auth()->id();
        $data = $this->adminRepo->createFixedCost($data);
        return $this->successResponse($data, 'Successfuly', 200);
    }
    public function updateFixedCost(array $data, $id)
    {
        $data = $this->adminRepo->updateFixedCost($data, $id);
        return $this->successResponse($data, 'Successfuly', 200);
    }
    public function getFixedCost()
    {
        $data = $this->adminRepo->getFixedCost();
        return $this->successResponse($data, 'Successfuly', 200);
    }

    public function getUsers()
    {
        $data = $this->adminRepo->getUsers();
        return $this->successResponse($data, 'Successfully', 200);
    }

    public function deleteUser($id)
    {
        $result = $this->adminRepo->deleteUser($id);
        if ($result) {
            return $this->successResponse(null, 'User deleted successfully', 200);
        }
        return $this->errorResponse('User not found', 404);
    }

    public function updateUserRole(int $id, string $role)
    {
        $user = $this->adminRepo->updateUserRole($id, $role);
        if ($user) {
            return $this->successResponse($user, 'User role updated successfully', 200);
        }
        return $this->errorResponse('User not found', 404);
    }


    public function getAllComplaints()
    {
        $complaints = $this->adminRepo->getAllComplaints();

        if ($complaints->isEmpty()) {
            return $this->successResponse($complaints, 'No complaints found.', 200);
        }

        return $this->successResponse($complaints, 'Complaints retrieved successfully.', 200);
    }

    public function getshipments($status = null)
    {
        $shipments = $this->adminRepo->getshipments($status);

        if ($shipments->isEmpty()) {
            return $this->errorResponse('No shipments found.', 200);
        }

        $shipments->load('customer:id,first_name,last_name');

        $data = $shipments->map(function ($shipment) {
            return [
                'id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'type' => $shipment->type,
                'customer_name' => optional($shipment->customer)->first_name && optional($shipment->customer)->last_name
                    ? optional($shipment->customer)->first_name . ' ' . optional($shipment->customer)->last_name
                    : null,
                'status' => $shipment->status,
                'declared_parcels_count' => $shipment->declared_parcels_count,
                'created_at' => $shipment->created_at
            ];
        });

        return $this->successResponse($data, 'All shipments retrieved successfully', 200);
    }

   public function getMyShipments()
{
    $shipments = Shipment::with('parcels:id,shipment_id,status')
        ->where('customer_id', auth()->id())
        ->get();

    $data = $shipments->map(function ($shipment) {
        return [
            'id' => $shipment->id,
            'tracking_number' => $shipment->tracking_number,
            'type' => $shipment->type,
            'status' => $shipment->status,
            'declared_parcels_count' => $shipment->declared_parcels_count,
            'created_at' => $shipment->created_at,
            'parcels' => $shipment->parcels->map(function ($parcel) {
                return [
                    'id' => $parcel->id,
                    'weight' => $parcel->weight,
                    'status' => $parcel->status,
                ];
            }),
        ];
    });

    return $this->successResponse($data, 'My shipments with parcels retrieved successfully.', 200);
}

    public function getComplaintWithResponses($id)
    {
        $complaint = $this->adminRepo->getComplaintWithResponses($id);

        if (!$complaint) {
            return $this->successResponse($complaint, 'Complaint not found.', 200);
        }


        $responses = $complaint->responses->map(function ($response) {
            return [
                'id'      => $response->id,
                'content' => $response->content,
                'user_id' => $response->user_id,
                'user_name' => optional($response->user)->first_name,
                'user_role' => optional($response->user)->role,
                'created_at' => $response->created_at,
            ];
        });

        $data = [
            'id' => $complaint->id,
            'user_id' => $complaint->user_id,
            'user_name' => optional($complaint->user)->first_name,
            'description' => $complaint->description,
            'is_solved' => $complaint->is_solved,
            'resolution_notes' => $complaint->resolution_notes,
            'resolved_at' => $complaint->resolved_at,
            'created_at' => $complaint->created_at,
            'responses' => $responses,
        ];

        return $this->successResponse($data, 'Complaint details retrieved successfully.', 200);
    }

    public function createComplaint($userId, $description, $shipmentId = null, $parcelId = null)
    {
        return Complaint::create([
            'user_id' => $userId,
            'shipment_id' => $shipmentId,
            'parcel_id' => $parcelId,
            'description' => $description,
            'is_solved' => false,
        ]);
    }

    public function addComplaintResponse($complaintId, $userId, $content)
    {
        $response = $this->adminRepo->addComplaintResponse($complaintId, $userId, $content);

        if (!$response) {
            return $this->successResponse($response, 'Complaint not found.', 200);
        }

        return $this->successResponse($response, 'Response added successfully.', 200);
    }


    public function markComplaintAsSolved($complaintId)
    {
        $complaint = $this->adminRepo->markComplaintAsSolved($complaintId);

        if (!$complaint) {
            return $this->successResponse($complaint, 'Complaint not found.', 200);
        }

        return $this->successResponse($complaint, 'Complaint marked as solved.', 200);
    }






    public function getDashboardStatistics(array $filters = [])
    {
        try {
            $driversCount       = $this->adminRepo->countDrivers();
            $shipmentsToday     = $this->adminRepo->countShipmentsToday($filters);
            $shipmentsThisWeek  = $this->adminRepo->countShipmentsThisWeek($filters);
            $completedShipments = $this->adminRepo->countCompletedShipments($filters);

            $monthlyCounts      = $this->adminRepo->getShipmentsByMonth($filters);

            $shipmentsByMonth = [];
            for ($m = 1; $m <= 12; $m++) {
                $shipmentsByMonth[] = [
                    'month' => Carbon::create()->month($m)->format('F'),
                    'count' => $monthlyCounts[$m] ?? 0
                ];
            }

            $shipmentsByWeight = $this->adminRepo->getShipmentsByWeightRange($filters);
            $shipmentsByStatus = $this->adminRepo->getShipmentsByStatus($filters);

            $data = [
                'drivers_count'             => $driversCount,
                'shipments_today'           => $shipmentsToday,
                'shipments_this_week'       => $shipmentsThisWeek,
                'completed_shipments'       => $completedShipments,
                'shipments_by_month'        => $shipmentsByMonth,
                'shipments_by_weight_range' => $shipmentsByWeight,
                'shipments_by_status_pie'   => $shipmentsByStatus,
            ];


            return $this->successResponse($data, 'Dashboard statistics retrieved successfully.', 200);
        } catch (Exception $e) {

            return $this->errorResponse(
                'Failed to retrieve dashboard statistics.',
                500,
                ['exception' => $e->getMessage()]
            );
        }
    }
}
