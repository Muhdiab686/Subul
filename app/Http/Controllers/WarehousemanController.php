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
}
