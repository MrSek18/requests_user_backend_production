<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function update(Request $request)
    {
        // Obtener el usuario autenticado con verificación explícita
        $user = Auth::user();
        
        // Verificar autenticación primero
        if (!$user) {
            return response()->json([
                'error' => 'Usuario no autenticado',
                'message' => 'Debes iniciar sesión para realizar esta acción'
            ], 401);
        }

        // Verificación adicional del tipo
        if (!$user instanceof User) {
            return response()->json([
                'error' => 'Tipo de usuario inválido',
                'expected_type' => User::class,
                'actual_type' => is_object($user) ? get_class($user) : gettype($user)
            ], 500);
        }

        // Validación de datos
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:8',
            'dni' => 'sometimes|string|max:20',
            'celular' => 'sometimes|string|max:20'
        ]);

        // Actualización segura
        try {
            $user->fill($validatedData);
            $user->save();

            return response()->json([
                'message' => 'Datos actualizados correctamente',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}