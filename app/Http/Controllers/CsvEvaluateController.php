<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateUrls;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class CsvEvaluateController extends Controller
{
    const URL = 'http://127.0.0.1:8000/csv-evaluate?url[]=http://127.0.0.1:8000/csv-maker/val1&url[]=http://127.0.0.1:8000/csv-maker/val2';

    public function __construct() { }



    public function index(ValidateUrls $request)
    {


        $urls = $request->query('url');
        foreach ($urls as $url){

            $response = Http::get($url);
dd($response->body());

        }



        dd($urls);
    }
}
