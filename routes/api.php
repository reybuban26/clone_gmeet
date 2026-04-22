<?php

use App\Http\Controllers\Api\LogController;
use App\Http\Controllers\Api\MeetingController;
use Illuminate\Support\Facades\Route;

Route::prefix('meetings')->group(function () {
    Route::post('create', [MeetingController::class, 'create']);
    Route::post('verify', [MeetingController::class, 'verify']);
    Route::post('{code}/peer', [MeetingController::class, 'savePeerId']);
    Route::get('{code}/peer', [MeetingController::class, 'getPeerId']);
    Route::patch('{code}/end', [MeetingController::class, 'end']);
    Route::post('{code}/chat',      [MeetingController::class, 'storeChat']);
    Route::post('{code}/recording', [MeetingController::class, 'storeRecording']);
});

Route::post('logs', [LogController::class, 'store']);
