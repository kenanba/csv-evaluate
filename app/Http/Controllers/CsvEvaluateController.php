<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateUrls;
use Illuminate\Http\Request;

class CsvEvaluateController extends Controller
{
    public function __construct() { }



    public function index(ValidateUrls $request)
    {
        $urls = $request->all();

        dd($urls);
    }
}
