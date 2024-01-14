<?php

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function (Request $request) {

    $query = Order::query();

    // Apply filters if provided
    $query->where(function ($query) use ($request) {
        $query->when($request->has('order_id'), function ($q) use ($request) {
            return $q->where('id', 2);
        })
        ->when($request->has('order_status_id'), function ($q) use ($request) {
            return $q->where('order_status_id', $request->input('order_status_id'));
        })
        ->when($request->has('start_date'), function ($q) use ($request) {
            return $q->where('start_date', '>=', $request->input('start_date'));
        })
        ->when($request->has('end_date'), function ($q) use ($request) {
            return $q->where('end_date', '<=', $request->input('end_date'));
        });
    });

    dd($query);


   // return view('welcome');
});
