<?php

use App\Http\Controllers\CsvEvaluateController;
use App\Http\Controllers\CsvMakerController;
use Illuminate\Support\Facades\Route;

Route::post('csv-evaluate', CsvEvaluateController::class);




Route::get('csv-maker', CsvMakerController::class);


