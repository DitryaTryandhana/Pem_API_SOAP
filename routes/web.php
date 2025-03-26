<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/contact.wsdl', function () {
    $path = public_path('wsdl/contact.wsdl');

    if (!File::exists($path)) {
        abort(404, "WSDL file not found");
    }

    return Response::make(File::get($path), 200, [
        'Content-Type' => 'text/xml'
    ]);
});