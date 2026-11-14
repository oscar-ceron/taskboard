<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaccion;
use App\Http\Resources\TransaccionResource;
use App\Http\Requests\GuardarTransaccionRequest;

class TransaccionApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $estado = request()->query('estado');

        return TransaccionResource::collection( 
        Transaccion::when($estado, fn ($q) => $q->where('estado', $estado))->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GuardarTransaccionRequest $request)
    {
        $transaccion = Transaccion::create($request->validated()); 
  
        return (new TransaccionResource($transaccion)) 
            ->response() 
            ->setStatusCode(201); 
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaccion $transaccion)
    {
        return new TransaccionResource($transaccion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaccion $transaccion)
    {
        $transaccion->delete(); 
  
        return response()->noContent();   // 204 
    }
}
