<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardarTransaccionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /*
        // Se comentó esta parte ya que la petición POST de la API, 
        //no funcionaba correctamente, debido a que el método `user()` 
        // devuelve null, y por lo tanto la regla de validación `Rule::exists`
        // no podía verificar si el comercio pertenece al usuario autenticado.
        return [
            'comercio_id' => ['required', 
                Rule::exists('comercios', 'id')->where('user_id', $this->user()->id)],
            'cliente_nombre' => 'required|string|max:255|min:3',
            'monto' => 'required|numeric|min:0.01|max:10000',
        ];
        */

        //
        $comercioRule = Rule::exists('comercios', 'id');
        $usuario = $this->user();

        if ($usuario !== null) {
            $comercioRule->where('user_id', $usuario->id);
        }

        return [
            'comercio_id' => ['required', $comercioRule],
            'cliente_nombre' => 'required|string|max:255|min:3',
            'monto' => 'required|numeric|min:0.01|max:10000',
        ];
        //
    }

    public function messages(): array
    {
        return [
            'cliente_nombre.required' => 'Debes indicar el nombre del cliente.',
            'cliente_nombre.min' => 'El nombre del cliente es demasiado corto.',
            'monto.required' => 'Debes indicar un monto.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'monto.max' => 'El monto es demasiado alto.',
        ];
    }

    public function attributes(): array
    {
        return [
            'cliente_nombre' => 'nombre del cliente',
            'monto' => 'monto de la transacción',
            'comercio_id' => 'comercio',
        ];
    }
}
