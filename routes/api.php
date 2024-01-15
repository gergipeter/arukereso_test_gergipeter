<?php

use App\Exceptions\MethodNotAllowedException;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::resource('orders', OrderController::class)->only(['store', 'listOrders', 'updateStatus']);

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::post('/list', [OrderController::class, 'listOrders']);
    Route::post('/updateStatus', [OrderController::class, 'updateOrderStatus']);
    // Route::put('/{id}', [OrderController::class, 'update']);
    // Route::delete('/{id}', [OrderController::class, 'destroy']);

    Route::get('/', function () {
        throw new MethodNotAllowedException('The GET method is not supported for this route.');
    });

    Route::get('/list', function () {
        throw new MethodNotAllowedException('The GET method is not supported for this route.');
    });

    Route::get('/{id}', function () {
        throw new MethodNotAllowedException('The GET method is not supported for this route.');
    });

});




//Route::resource('orders', OrderController::class);