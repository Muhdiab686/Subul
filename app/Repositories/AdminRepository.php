<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\DeliveryStaff;
use App\Models\ParcelManage;
use App\Models\FixedCost;
use App\Models\Complaint;
use App\Models\ComplaintResponse;
use App\Models\User;
use App\Models\Shipment;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class AdminRepository
{

    public function getcontentpracel()
    {
        return ParcelManage::get();
    }
    public function createcontentp(array $data)
    {
        return ParcelManage::create($data);
    }
    public function findByIdpracel($id)
    {
        return ParcelManage::findOrFail($id);
    }
    // country //
    public function getCountry()
    {
        return Country::get();
    }

    public function createCountry(array $data)
    {
        return Country::create($data);
    }

    public function updateStatusCountry(int $id, bool $status)
    {
        $country = Country::find($id);
        if ($country) {
            $country->is_enabled = $status;
            $country->save();
        }
        return $country;
    }
    // موظفي التسلسم السائقسن //

    public function createDelivery(array $data)
    {
        return DeliveryStaff::create($data);
    }

    public function createSupplier(array $data)
    {
        return Supplier::create($data);
    }

    public function updateDelivery(int $id, array $data)
    {
        $staff = DeliveryStaff::find($id);
        if ($staff) {
            $staff->update($data);
        }
        return $staff;
    }

    public function deleteDelivery(int $id)
    {
        $staff = DeliveryStaff::find($id);
        if ($staff) {
            return $staff->delete();
        }
        return false;
    }

    public function getAllDelivery()
    {
        return DeliveryStaff::get();
    }

    public function findDelivery(int $id)
    {
        return DeliveryStaff::find($id);
    }

    public function createFixedCost(array $data)
    {
        return FixedCost::create($data);
    }
    public function updateFixedCost(array $data, $id)
    {
        $fixedCost = FixedCost::findOrFail($id);
        $fixedCost->update($data);
        return $fixedCost;
    }
    public function getFixedCost()
    {
        return FixedCost::get();
    }

    // User Methods
    public function getUsers()
    {
        return User::select('id', 'role', 'first_name', 'last_name', 'email', 'phone')
            ->get();
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }

    public function updateUserRole(int $id, string $role)
    {
        $user = User::find($id);
        if ($user) {
            $user->role = $role;
            $user->save();
            return $user;
        }
        return false;
    }

    public function getAllComplaints()
    {
        return Complaint::get();
    }


   public function getshipments($status = null)
    {
        $query = Shipment::query();

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function getmyshipments()
    {
        return Shipment::where('customer_id', Auth()->user()->id)->get();

    }
    public function getComplaintWithResponses($id)
    {
        return Complaint::with(['responses.user', 'user'])
            ->find($id);
    }



    public function addComplaintResponse($complaintId, $userId, $content)
    {
        $complaint = Complaint::find($complaintId);
        if (!$complaint) {
            return null;
        }

        return ComplaintResponse::create([
            'complaint_id' => $complaintId,
            'user_id' => $userId,
            'content' => $content,
        ]);
    }



    public function markComplaintAsSolved($complaintId)
    {
        $complaint = Complaint::find($complaintId);
        if (!$complaint) {
            return null;
        }
        $complaint->is_solved = true;
        $complaint->save();
        return $complaint;
    }


    public function countDrivers(): int
    {
        return DeliveryStaff::count();
    }

    public function countShipmentsToday(array $filters): int
    {
        $query = Shipment::query();
        $query = $this->applyShipmentFilters($query, $filters);

        $query->whereDate('created_at', Carbon::today());
        return $query->count();
    }


    public function countShipmentsThisWeek(array $filters): int
    {
        $query = Shipment::query();
        $query = $this->applyShipmentFilters($query, $filters);

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek   = Carbon::now();
        $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
        return $query->count();
    }


    public function countCompletedShipments(array $filters): int
    {
        $query = Shipment::query();
        $query = $this->applyShipmentFilters($query, $filters);
        $query->where('status', 'delivered');
        return $query->count();
    }


    public function getShipmentsByMonth(array $filters): array
    {
        $query = Shipment::query();
        $currentYear = Carbon::now()->year;
        $query->whereYear('created_at', $currentYear);
        $query = $this->applyShipmentFilters($query, $filters);

        $results = $query->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();

        return $results->pluck('count', 'month')->all();
    }


    public function getShipmentsByWeightRange(array $filters): array
    {

        $weightsQuery = DB::table('shipments')
            ->leftJoin('parcels', 'shipments.id', '=', 'parcels.shipment_id')
            // تطبيق عوامل التصفية على الشحنات
            ->when(isset($filters['customer_id']), function ($q) use ($filters) {
                $q->where('shipments.customer_id', $filters['customer_id']);
            })
            ->when(isset($filters['status']), function ($q) use ($filters) {
                $q->where('shipments.status', $filters['status']);
            })
            ->when(isset($filters['from_date']), function ($q) use ($filters) {
                $q->whereDate('shipments.created_at', '>=', $filters['from_date']);
            })
            ->when(isset($filters['to_date']), function ($q) use ($filters) {
                $q->whereDate('shipments.created_at', '<=', $filters['to_date']);
            })
            ->selectRaw('shipments.id, COALESCE(SUM(parcels.actual_weight), 0) as total_weight')
            ->groupBy('shipments.id');

        $weights = $weightsQuery->get();

        // حساب عدد الشحنات في كل فئة وزن
        $under2kg = 0;
        $between2and5kg = 0;
        $over5kg = 0;
        foreach ($weights as $record) {
            $totalWeight = $record->total_weight;
            if ($totalWeight < 2) {
                $under2kg++;
            } elseif ($totalWeight <= 5) {
                $between2and5kg++;
            } else {
                $over5kg++;
            }
        }

        // إعداد المصفوفة النهائية لنطاقات الوزن
        return [
            ['range' => '<2kg',  'count' => $under2kg],
            ['range' => '2-5kg', 'count' => $between2and5kg],
            ['range' => '>5kg',  'count' => $over5kg],
        ];
    }


    public function getShipmentsByStatus(array $filters): array
    {
        $query = Shipment::query();
        $query = $this->applyShipmentFilters($query, $filters);
        $results = $query->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $results->map(function ($item) {
            return [
                'status' => $item->status,
                'count'  => $item->count
            ];
        })->toArray();
    }


    private function applyShipmentFilters($query, array $filters)
    {
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        return $query;
    }
}
