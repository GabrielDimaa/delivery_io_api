<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ComplementoController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\TaxaEntregaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SecretMiddleware;
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

Route::middleware([SecretMiddleware::class])->group(function () {
    Route::apiResource('user', UserController::class);

    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::post('auth/user', [AuthController::class, 'getUser']);
        Route::apiResource('categorias', CategoriaController::class);
        Route::apiResource('produtos', ProdutoController::class);
        Route::apiResource('taxas_entrega', TaxaEntregaController::class);

        Route::group(['prefix' => 'complementos'], function () {
            Route::apiResource('/', ComplementoController::class);
            Route::get('categorias_complementos', [ComplementoController::class, 'getCategoriasComplementos']);
        });

        Route::group(['prefix' => 'pedidos'], function () {
            Route::apiResource('/', PedidoController::class);
            Route::put('status/{id}', [PedidoController::class, 'alterarStatusPedido']);
            Route::put('cancelar/{id}', [PedidoController::class, 'cancelarPedido']);
        });
    });
});
