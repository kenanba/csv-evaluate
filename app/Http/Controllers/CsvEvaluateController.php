<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateUrls;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Str;
class CsvEvaluateController extends Controller
{
    const URL = 'http://127.0.0.1:8000/csv-evaluate?url[]=http://127.0.0.1:8080/api/csv-maker/val1&url[]=http://127.0.0.1:8080/csv-maker/val2';

    public function __construct() { }

    public function index(ValidateUrls $request)
    {
        $csv = [];
        $collectionCsv = [];
        $ulid = (string) Str::ulid();

        $mostSpeeches =null;
        $mostSecurity ='';
        $leastWordy ='';

        foreach ($request->query('url') as $key => $url) {
            $fileName = "$ulid/file_$key.csv";
            $response = Http::withOptions(['timeout' => 60])->get($url);

            if ($response->successful()) {
                Storage::disk('public')->put($fileName, $response->body());

                $rows = array_map(
                    function($v) {
                    return str_getcsv($v, ",");
                    }, file(storage_path('/app/public/').$fileName));

                $header = array_shift($rows);

                foreach($rows as $row) {
                    $csv[] = array_combine($header, $row);
                }

                $collectionCsv = collect($csv);


            } else {
                return response()->json($url.' => URL is  not valid', 404);
            }
        }
        return response()->json($collectionCsv->values()->map(function ($item) {

            $getYear = Carbon::parse($item['Date'])->format('Y');
                if ($getYear == '2013'){

                }


        }));
    }
}
