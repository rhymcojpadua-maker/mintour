<?php
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/map/features', [MapController::class, 'data'])->name('api.map.features');
});

Route::middleware(['throttle:chatbot', 'web'])->prefix('chat')->group(function () {
    Route::post('/message', [ChatController::class, 'message'])->name('api.chat.message');
    Route::post('/feedback/{message}', [ChatController::class, 'feedback'])->name('api.chat.feedback');
    Route::get('/history', [ChatController::class, 'history'])->name('api.chat.history');
});
