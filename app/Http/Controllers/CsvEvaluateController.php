<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CsvEvaluateController extends Controller
{
    public function __invoke(Request $request)
    {
        dd($request->all());
    }
}
