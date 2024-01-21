<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;



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

Route::post('add-product',[ProductController::class,'AddProducts']);
Route::get('all-products',[ProductController::class,'ViewAllProducts']);
Route::get('edit-product/{productid}',[ProductController::class,'EditProductById']);
Route::post('update-product/{productid}',[ProductController::class,'UpdateProductById']);
Route::post('delete-product/{productid}',[ProductController::class,'DeleteProductById']);
Route::post('update-product-status/{productid}',[ProductController::class,'UpdateProductStatusById']);