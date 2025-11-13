<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Permitir a todos usar este request
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'dni' => 'required|string|size:8|unique:users|regex:/^[0-9]+$/',
            'celular' => 'required|string|size:9|regex:/^[9][0-9]{8}$/',
            'g-recaptcha-response' => ['required', function ($attribute, $value, $fail) {
                $this->validateRecaptcha($value, $fail);
            }]
        ];
    }

    public function messages()
    {
        return [
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos',
            'dni.unique' => 'Este DNI ya está registrado',
            'dni.regex' => 'El DNI solo debe contener números',
            'celular.required' => 'El celular es obligatorio',
            'celular.size' => 'El celular debe tener 9 dígitos',
            'celular.regex' => 'El celular debe comenzar con 9 y tener 9 dígitos numéricos'
        ];
    }

    protected function validateRecaptcha($token, $fail)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $token,
            'remoteip' => $this->ip()
        ]);

        if (!$response->successful() || !$response->json()['success']) {
            $fail('La verificación reCAPTCHA falló. Por favor inténtalo de nuevo.');
        }
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors(),
            'message' => 'Error de validación'
        ], 422));
    }
}