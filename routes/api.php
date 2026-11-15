<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Comercio;
use App\Http\Resources\ComercioResource;
use App\Http\Controllers\Api\TransaccionApiController;
use App\Http\Controllers\Api\AuthApiController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/




Route::post('/login', [AuthApiController::class, 'login']) 
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () { 
    Route::get('/yo', [AuthApiController::class, 'yo']); 
    Route::post('/logout', [AuthApiController::class, 'logout']); 
  
    // Las rutas de la Semana 13 se mueven aquí dentro: 
    Route::get('/comercios', function (Request $request){
        return ComercioResource::collection(
            $request->user()->comercios()->orderBy('nombre_comercio')->get()
        );
    });

    Route::get('/comercios/{comercio}', function (Request $request, Comercio $comercio) { 
        abort_unless($comercio->user_id === $request->user()->id, 404);    

        return new ComercioResource($comercio->load('transacciones')); 
    });

    Route::apiResource('transacciones', TransaccionApiController::class)
        ->parameters(['transacciones' => 'transaccion']);

    Route::get('/tokens', function (Request $request) { 
        return $request->user()->tokens()
        ->get(['id', 'name', 'last_used_at', 'created_at']); 
    });

    Route::delete('/tokens/{id}', function (Request $request, int $id){
        $token = $request->user()->tokens()->findOrFail($id);
        $token->delete();
        return response()->noContent();  // 204
    });

});