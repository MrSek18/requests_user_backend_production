<?php

use Illuminate\Support\Facades\Route;

// routes/web.php
Route::prefix('api')->group(function () {
    Route::get('/test', function () {
        return response()->json(['message' => '¡API funcionando!']);
    });
});
