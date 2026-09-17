{{-- Semana 7 · Blade --}}
{{-- resources/views/comercios/show.blade.php --}}
@extends('layouts.app')

@section('titulo', $comercio->nombre_comercio)

@section('contenido')
    <p><a href="{{ route('comercios.index') }}">&larr; Volver a comercios</a></p>

    <h1>{{ $comercio->nombre_comercio }}</h1>
    <p class="meta">
        Rubro: {{ $comercio->rubro }}
        &middot;
        Teléfono: {{ $comercio->telefono ?? 'Sin teléfono registrado' }}
    </p>

    <h2>Transacciones</h2>

    @forelse ($comercio->transacciones as $t)
        <div class="transaccion">
            <strong>${{ $t->monto }}</strong> {{ $t->cliente_nombre }}
            <x-badge-estado :estado="$t->estado" />
        </div>
    @empty
        <p>Sin transacciones</p>
    @endforelse
@endsection
