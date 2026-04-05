<?php

use App\Http\Controllers\ProposalController;
use Illuminate\Support\Facades\Route;

Route::post('/proposal', [ProposalController::class, 'store'])->middleware('throttle:60,1');
