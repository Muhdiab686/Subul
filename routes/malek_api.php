<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\WarehousemanController;
use App\Http\Middleware\AdminRole;


Route::middleware('auth:api')->group(function () {
    Route::get('/get/invoice-details/{shipment_id}', [WarehousemanController::class, 'getInvoiceDetails']);


    Route::post('/create/shipments', [WarehousemanController::class, 'createShipment']);
    Route::get('/get/enabled-countries', [WarehousemanController::class, 'getEnabledCountries']);
    Route::get('/get/one/shipment/{shipment_id}', [ManagerController::class, 'getShipmentById']);
    Route::get('/get/invoice/{invoice_id}', [ManagerController::class, 'getInvoiceDetails']);


    Route::get('/get/customers', [WarehousemanController::class, 'getCustomers']);
    Route::get('/get/suppliers', [WarehousemanController::class, 'getSuppliers']);
});


Route::middleware(['auth:api', AdminRole::class . ':manager'])->group(function () {

    Route::post('/add/Customar', [ManagerController::class, 'addCustomer']);
    Route::get('/getAll/Customers', [ManagerController::class, 'getAllCustomers']);
    Route::get('/getAll/companies', [ManagerController::class, 'getAllCompanies']);
    Route::delete('/delete/Customer/{id}', [ManagerController::class, 'deleteCustomer']);


    Route::get('/get/All/shipments', [ManagerController::class, 'getAllShipments']);
    Route::get('/get/approved/shipments', [ManagerController::class, 'get_approved_Shipments']);
    Route::get('/get/unapproved/shipments', [ManagerController::class, 'get_unapproved_Shipments']);
    Route::post('/approve/shipment/{id}', [ManagerController::class, 'approveShipment']);
    Route::get('/get/customer/shipments', [ManagerController::class, 'getCustomerShipments']);
    Route::get('/get/rejected/shipments/', [ManagerController::class, 'getRejectedShipments']);
    Route::post('/reject/shipments', [ManagerController::class, 'rejectShipment']);

    Route::post('/create/invoice', [ManagerController::class, 'createInvoice']);
});
Route::middleware(['auth:api', AdminRole::class . ':warehouseman'])->group(function () {

    Route::get('/get/shipments/in-process', [WarehousemanController::class, 'getInProcessShipments']);


    Route::post('/shipments/{shipment_id}/origin-country/{origin_country_id}', [WarehousemanController::class, 'updateShipmentOriginCountry']);
    Route::post('/shipments/{shipment_id}/destination-country/{destination_country_id}', [WarehousemanController::class, 'updateShipmentDestinationCountry']);
    Route::post('/update/shipments/costs/{shipment_id}', [WarehousemanController::class, 'updateShipmentCosts']);




    Route::post('/create/parcel/{shipment_id}', [WarehousemanController::class, 'createParcel']);
    Route::post('/create/multiple-parcels/{shipment_id}', [WarehousemanController::class, 'createMultipleParcels']);
    Route::get('/get/all/parcels', [WarehousemanController::class, 'getAllParcels']);
    Route::get('/get/parcels/{shipment_id}', [WarehousemanController::class, 'getParcelsByShipmentId']);
    Route::get('/get/one/parcels/{parcel_id}', [WarehousemanController::class, 'getParcelByParcelId']);
    Route::post('/create/parcel-item/{parcel_id}', [WarehousemanController::class, 'createParcelItem']);
    Route::get('/get/parcel-items/{parcel_id}', [WarehousemanController::class, 'getParcelItemsByParcelId']);
    Route::get('/get/allowed-parcel-content', [WarehousemanController::class, 'getAllowedParcelContent']);


    Route::get('/get/drivers', [WarehousemanController::class, 'getDrivers']);
    Route::post('/update/shipments/for-delivery/{shipment_id}', [WarehousemanController::class, 'updateShipmentForDelivery']);
    Route::get('/get/shipment-details/{shipment_id}', [WarehousemanController::class, 'getShipmentDetails']);



    Route::get('/get/shipments/in-the-way', [WarehousemanController::class, 'getInTheWayShipments']);
    Route::post('/update/shipments/warehouse-arrival/{shipment_id}', [WarehousemanController::class, 'updateShipmentWarehouseArrival']);
    Route::post('/update/parcel-info/{parcel_id}', [WarehousemanController::class, 'updateParcelInfo']);
    Route::get('/get/deliverable-shipments', [WarehousemanController::class, 'getDeliverableShipments']);
    Route::post('/mark-shipment-delivered/{shipment_id}', [WarehousemanController::class, 'markShipmentAsDelivered']);
});
