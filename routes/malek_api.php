<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\WarehousemanController;



Route::middleware('auth:api')->group(function () {



    Route::post('/add/Customar', [ManagerController::class, 'addCustomer']);
    Route::get('/getAll/Customers', [ManagerController::class, 'getAllCustomers']);
    Route::delete('/delete/Customer/{id}', [ManagerController::class, 'deleteCustomer']);

    Route::get('/get/approved/shipments', [ManagerController::class, 'get_approved_Shipments']);
    Route::get('/get/unapproved/shipments', [ManagerController::class, 'get_unapproved_Shipments']);
    Route::get('/get/customer/shipments', [ManagerController::class, 'getCustomerShipments']);
    Route::get('/get/one/shipment/{shipment_id}', [ManagerController::class, 'getShipmentById']);
    Route::get('/get/rejected/shipments/', [ManagerController::class, 'getRejectedShipments']);
    Route::post('/reject/shipments', [ManagerController::class, 'rejectShipment']);

    Route::post('/create/invoice', [ManagerController::class, 'createInvoice']);
    Route::get('/get/invoice/{invoice_id}', [ManagerController::class, 'getInvoiceDetails']);





    Route::get('/get/customers', [WarehousemanController::class, 'getCustomers']);
    Route::post('/create/shipments', [WarehousemanController::class, 'createShipment']);
    Route::get('/get/shipments/in-process', [WarehousemanController::class, 'getInProcessShipments']);

    Route::post('/shipments/{shipment_id}/origin-country/{origin_country_id}', [WarehousemanController::class, 'updateShipmentOriginCountry']);
    Route::post('/shipments/{shipment_id}/destination-country/{destination_country_id}', [WarehousemanController::class, 'updateShipmentDestinationCountry']);
    Route::post('/update/shipments/costs/{shipment_id}', [WarehousemanController::class, 'updateShipmentCosts']);



    Route::post('/create/parcel/{shipment_id}', [WarehousemanController::class, 'createParcel']);
    Route::get('/get/all/parcels', [WarehousemanController::class, 'getAllParcels']);
    Route::get('/get/parcels/{shipment_id}', [WarehousemanController::class, 'getParcelsByShipmentId']);
    Route::get('/get/one/parcels/{parcel_id}', [WarehousemanController::class, 'getParcelByParcelId']);




});
