<?php

use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\SubcategoriaController;
use App\Http\Controllers\Api\TaxaEntregaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('categorias', CategoriaController::class);
Route::apiResource('subcategorias', SubcategoriaController::class);
Route::apiResource('produtos', ProdutoController::class);
Route::apiResource('taxas_entrega', TaxaEntregaController::class);
Route::apiResource('pedidos', PedidoController::class);
