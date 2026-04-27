<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioController;

Route::redirect('/', '/admin');

Route::get('/audio/{path}', [AudioController::class, 'serve'])
    ->where('path', '.*')
    ->name('audio.serve');