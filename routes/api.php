<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);




Route::middleware('auth:api')->group(function () {
    Route::get('/get/content/parcels', [AdminController::class, 'getcontentpracel']);
    Route::post('/create/content/parcels', [AdminController::class, 'storecontentpracel']);
    Route::post('/update/content/parcels/permission/{id}', [AdminController::class, 'updatePermission']);

    Route::get('/get/countries', [AdminController::class, 'getCountry']);
    Route::post('/create/countries', [AdminController::class, 'storeCountry']);
    Route::post('/update/countries/status/{id}', [AdminController::class, 'toggleStatusCountry']);

    Route::get('/get/delivery-staff', [AdminController::class, 'indexDelivery']);
    Route::post('/create/delivery-staff', [AdminController::class, 'storeDelivery']);
    Route::put('/update/delivery-staff/{id}', [AdminController::class, 'updateDelivery']);
    Route::delete('/dalete/delivery-staff/{id}', [AdminController::class, 'destroyDelivery']);

    Route::post('/add/Customar', [ManagerController::class, 'addCustomer']);
    Route::get('/getAll/Customers', [ManagerController::class, 'getAllCustomers']);
    Route::delete('/delete/Customer/{id}', [ManagerController::class, 'deleteCustomer']);
});
