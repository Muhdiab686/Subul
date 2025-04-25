<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManagerController;


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::post('addCustomar', [ManagerController::class, 'addCustomer']);
Route::get('getAllCustomers', [ManagerController::class, 'getAllCustomers']);
Route::delete('deleteCustomer/{id}', [ManagerController::class, 'deleteCustomer']);


Route::middleware('auth:api')->group(function () {


});
