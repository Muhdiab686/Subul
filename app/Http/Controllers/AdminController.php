<?php

namespace App\Http\Controllers;

use App\Models\Flight;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait;


class AdminController extends Controller
{
    use ApiResponseTrait;

    protected $adminService;
    public function indexflights()
        {
            $flights = Flight::with('departureAirport','arrivalAirport')->get();
            return $this->successResponse($flights , 'flights',200);
        }

    // إضافة رحلة جديدة
    public function storeflight(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_number' => 'required|string|unique:flights,flight_number',
            'departure_airport_id' => 'required|exists:airports,id',
            'arrival_airport_id' => 'required|exists:airports,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $flight = Flight::create($request->all());

        return response()->json($flight, 201);
    }
    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }


    // Parcel Content Methods
    public function getContentParcel()
    {
        return $this->adminService->getcontentpracel();
    }

    public function storeContentParcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'is_allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->storecontentpracel($validator->validated());
    }

    public function updatePermission(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_allowed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updatePermission($validator->validated(), $id);
    }

    // Country Methods
    public function getCountry()
    {
        return $this->adminService->getCountry();
    }

    public function storeCountry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:9|unique:countries,code',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createCountry($validator->validated());
    }

    public function toggleStatusCountry(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'is_enabled' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->changeStatus($id, $validator->validated()['is_enabled']);
    }


    public function createSupplir(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);
        return $this->adminService->createSupplir($validated);
    }
    // Delivery Methods
    public function indexDelivery()
    {
        return $this->adminService->getAllDelivery();
    }

    public function storeDelivery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'job_title' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createDelivery($validator->validated());
    }

    public function updateDelivery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:500',
            'phone' => 'sometimes|required|string|max:20',
            'job_title' => 'sometimes|required|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updateDelivery($id, $validator->validated());
    }

    public function destroyDelivery($id)
    {
        return $this->adminService->deleteDelivery($id);
    }

    // Fixed Cost Methods
    public function storeFixedCost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:fixed_costs,name',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->createFixedCost($validator->validated());
    }
    public function updateFixedCost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updateFixedCost($validator->validated(), $id);
    }
    public function getFixedCost()
    {
        return $this->adminService->getFixedCost();
    }

    // User Methods
    public function getUsers()
    {
        return $this->adminService->getUsers();
    }

    public function deleteUser($id)
    {
        return $this->adminService->deleteUser($id);
    }

    public function updateUserRole(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:admin,manager,warehouseman,company,customer',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->updateUserRole($id, $validator->validated()['role']);
    }


    public function getAllComplaints()
    {
        return $this->adminService->getAllComplaints();
    }

    public function createComplaint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|min:5',
            'shipment_id' => 'nullable|exists:shipments,id',
            'parcel_id'   => 'nullable|exists:parcels,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $userId = auth()->id();

        $complaint = $this->adminService->createComplaint(
            $userId,
            $validator->validated()['description'],
            $validator->validated()['shipment_id'] ?? null,
            $validator->validated()['parcel_id'] ?? null
        );

        return response()->json([
            'message' => 'Complaint created successfully',
            'data'    => $complaint,
        ], 201);
    }
    public function getComplaint($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:complaints,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->getComplaintWithResponses($id);
    }


    public function addComplaintResponse(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id' => 'required|exists:complaints,id',
            'content' => 'required|string|min:2',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $userId = auth()->id();
        return $this->adminService->addComplaintResponse($id, $userId, $validator->validated()['content']);
    }


    public function markComplaintAsSolved($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:complaints,id',
        ]);
        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        return $this->adminService->markComplaintAsSolved($id);
    }


    public function statistics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|integer|exists:users,id',
            'from_date'   => 'nullable|date',
            'to_date'     => 'nullable|date|after_or_equal:from_date',
            'status'      => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation error', 422, $validator->errors());
        }

        $filters = $validator->validated();
        return $this->adminService->getDashboardStatistics($filters);
    }

    public function getshipments(Request $request)
    {
        $status = $request->query('status'); // status=in_process or delivered etc.
        return $this->adminService->getshipments($status);
    }
    public function getmyshipments()
    {
        return $this->adminService->getmyshipments();
    }
}
