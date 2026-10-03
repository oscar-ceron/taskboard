<?php

namespace App\Http\Controllers;

use App\Models\Comercio;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\View\View;


class TransaccionController extends Controller
{
    public function create(Comercio $comercio): View
    {
        return view('transacciones.create', compact('comercio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'comercio_id' => 'required|exists:comercios,id',
            'cliente_nombre' => 'required|string|max:255|min:3',
            'monto' => 'required|numeric|min:0.01|max:10000',
        ], [
            'cliente_nombre.required' => 'Debes indicar el nombre del cliente.',
            'cliente_nombre.min' => 'El nombre del cliente es demasiado corto.',
            'monto.required' => 'Debes indicar un monto.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'monto.max' => 'El monto es demasiado alto.',
        ]);

        $transaccion = Transaccion::create($request->only([
            'comercio_id',
            'cliente_nombre',
            'monto'
        ]));
        return redirect()
            ->route('comercios.show', $transaccion->comercio_id)
            ->with('mensaje', 'Transacción registrada con éxito.');
    }


}
