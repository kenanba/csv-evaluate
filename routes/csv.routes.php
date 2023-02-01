<?php

use App\Http\Controllers\CsvEvaluateController;
use App\Http\Controllers\CsvMakerController;
use Illuminate\Support\Facades\Route;

Route::get('csv-evaluate', [CsvEvaluateController::class, 'index']);






