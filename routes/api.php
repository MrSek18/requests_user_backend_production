<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RepresentativeController;


use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ServiceOrderController;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


// Ruta pública de prueba
Route::get('/test', function () {
    return response()->json(['message' => 'API funcionando']);
});


// health-check
Route::get('/health', function () {
    try {
        DB::select('SELECT 1'); // Warm-up query
        return response()->json(['status' => 'ok']);
    } catch (\Exception $e) {
        Log::warning("Health-check falló: " . $e->getMessage());
        return response()->json(['status' => 'warming'], 503);
    }
});


// Autenticación
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas con token Bearer
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']); // 🔁 Mover logout aquí para que use el mismo guard

    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role ?? 'user',
                'created_at' => $request->user()->created_at->toISOString(),
                'monto_aportado' => $request->user()->monto_aportado ?? 0,
                'dni' => $request->user()->dni ?? '',
                'celular' => $request->user()->celular ?? ''
            ]
        ]);
    });

    Route::get('/protected-route', function () {
        return response()->json(['message' => 'Esta es una ruta protegida']);
    });

    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/representatives', [RepresentativeController::class, 'index']);
    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/units', [UnitController::class, 'index']);

    Route::get('/company_representatives/{company_id}', [RepresentativeController::class, 'byCompany']);

    Route::post('/add_request', [RequestController::class, 'store']);
    Route::put('/user/{id}', [UserController::class, 'update']); 
    Route::get('/requests/{request}/orden-servicio/pdf', [ServiceOrderController::class, 'download']);
    Route::get('/requests/recent', [RequestController::class, 'recent']);
});
