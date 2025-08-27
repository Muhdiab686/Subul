<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Services\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\WarehousemanController;

use App\Http\Middleware\AdminRole;

Route::post('/stripe/webhook', [AuthController::class, 'handleStripeWebhook']);

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get('/shipments', [AdminController::class, 'getshipments']);

Route::middleware(['auth:api', AdminRole::class . ':customer,company'])->group(function () {
    Route::get('/get/invoicemy', [WarehousemanController::class, 'getInvoicemy']);
    Route::post('/create/Complaint', [AdminController::class, 'createComplaint']);

    Route::get('my/shipment', [AdminController::class, 'getmyshipments']);
    Route::post('/create/shipment', [WarehousemanController::class, 'createShipment']);
    
    Route::get('/wallet', [AuthController::class, 'myBalance']);
    Route::get('/payments', [AuthController::class, 'myPayments']);

    Route::get('/invoice/{invoice_id}/checkout', [AuthController::class, 'createInvoiceCheckoutLink']);

});


Route::middleware(['auth:api', AdminRole::class . ':admin'])->group(function () {

    Route::get('/get/content/parcels', [AdminController::class, 'getContentParcel']);
    Route::post('/create/content/parcels', [AdminController::class, 'storeContentParcel']);
    Route::post('/update/content/parcels/permission/{id}', [AdminController::class, 'updatePermission']);

    Route::get('/get/countries', [AdminController::class, 'getCountry']);
    Route::post('/create/countries', [AdminController::class, 'storeCountry']);
    Route::post('/update/countries/status/{id}', [AdminController::class, 'toggleStatusCountry']);

    Route::get('/get/delivery-staff', [AdminController::class, 'indexDelivery']);
    Route::post('/create/delivery-staff', [AdminController::class, 'storeDelivery']);
    Route::put('/update/delivery-staff/{id}', [AdminController::class, 'updateDelivery']);
    Route::delete('/dalete/delivery-staff/{id}', [AdminController::class, 'destroyDelivery']);


    Route::post('/store/fixed-costs', [AdminController::class, 'storeFixedCost']);
    Route::post('/update/fixed-costs/{id}', [AdminController::class, 'updateFixedCost']);

    Route::get('/get/fixed-costs', [AdminController::class, 'getFixedCost']);


    Route::get('/get/users', [AdminController::class, 'getUsers']);
    Route::delete('/delete/users/{id}', [AdminController::class, 'deleteUser']);
    Route::post('/update/users/role/{id}', [AdminController::class, 'updateUserRole']);


    Route::get('/get/all/complaints', [AdminController::class, 'getAllComplaints']);
    Route::get('/get/one/complaints/{id}', [AdminController::class, 'getComplaint']);
    Route::post('/complaints/responses/{id}', [AdminController::class, 'addComplaintResponse']);
    Route::post('/complaints/solved/{id}', [AdminController::class, 'markComplaintAsSolved']);

    Route::get('/get/statistics', [AdminController::class, 'statistics']);

    Route::get('/suppliers', [WarehousemanController::class, 'getSuppliers']);
    Route::post('/createsupplir', [AdminController::class, 'createSupplir']);
});
