<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComercioController extends Controller
{

    public function index() : View
    {            
        $comercios = Auth::user() 
        ->comercios() 
        ->withCount('transacciones') 
        ->orderBy('nombre_comercio') 
        ->get(); 

        return view('comercios.index', compact('comercios'));
    }

    /**
     * GET /comercios/{comercio}
     *
     * Route Model Binding: Laravel convierte automáticamente el
     * parámetro {comercio} de la ruta en una instancia de Comercio
     * (o lanza 404 si no existe).
     *
     * load('transacciones') trae la relación en una sola consulta
     * adicional (no una por cada transacción), evitando el N+1.
     */
    public function show(Comercio $comercio): View
    {
        abort_unless($comercio->user_id === Auth::id(), 404);

        $comercio->load('transacciones');

        return view('comercios.show', compact('comercio'));
    }

}
