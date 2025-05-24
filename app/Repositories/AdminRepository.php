<?php
namespace App\Repositories;

use App\Models\Country;
use App\Models\DeliveryStaff;
use App\Models\ParcelManage;
use App\Models\FixedCost;

class AdminRepository{

    public function getcontentpracel(){
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
}
