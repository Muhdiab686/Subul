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


    Route::get('/get/customers', [WarehousemanController::class, 'getCustomers']);
    Route::post('/create/shipments', [WarehousemanController::class, 'createShipment']);



});
