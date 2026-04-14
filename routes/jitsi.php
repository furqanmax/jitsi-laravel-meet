<?php

use Illuminate\Support\Facades\Route;
use Furqanamx\JitsiLaravelMeet\Http\Controllers\JitsiController;

Route::get('/meeting/{code}/time-remaining', [JitsiController::class, 'timeRemaining'])
    ->name('jitsi.time-remaining');
