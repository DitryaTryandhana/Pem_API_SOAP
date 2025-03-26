<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SoapController;
use App\Http\Controllers\SoapClientController;

Route::post('/users', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/current', [UserController::class, 'get']);
    Route::patch('/users/current', [UserController::class, 'update']);
    Route::delete('/users/logout', [UserController::class, 'logout']);
});

// Route untuk menampilkan WSDL (Contact & Address)
Route::get('/soap', [SoapController::class, 'wsdl']); // Ini akan menangani WSDL

// Route untuk menangani permintaan SOAP request
Route::post('/api/soap/address', [SoapController::class, 'handleSoapRequest']);
Route::post('/api/soap/contact', [SoapController::class, 'handleSoapRequest']);

// Alternatif jika ingin tetap pisah WSDL Contact & Address
Route::get('/soap/contact', [SoapController::class, 'contactService']);
Route::get('/soap/address', [SoapController::class, 'addressService']);

// Route untuk client SOAP
Route::get('/contact/{id}', [SoapClientController::class, 'getContact']);
Route::get('/address/{id}', [SoapClientController::class, 'getAddress']);

Route::post('/api/contact', [SoapController::class, 'getContact']);

Route::get('/soap.wsdl', [SoapController::class, 'wsdl']);
Route::post('/api/soap', [SoapController::class, 'getContact']);

