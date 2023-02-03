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
        $ulid = (string)Str::ulid();
        $mostSpeeches = null;
        $mostSecurity = '';
        $leastWordy = '';
        foreach ($request->query('url') as $key => $url) {
            $fileName = "$ulid/file_$key.csv";
            $response = Http::withOptions(['timeout' => 60])->get($url);
            if ($response->successful()) {
                Storage::disk('public')->put($fileName, $response->body());
                $rows = array_map(
                    function ($v) {
                        return str_getcsv($v, ",");
                    },
                    file(storage_path('/app/public/').$fileName)
                );
                $header = array_shift($rows);
                foreach ($rows as $row) {
                    $csv[] = array_combine($header, $row);
                }
                $collectionCsv = collect($csv);
            } else {
                return response()->json($url.' => URL is  not valid', 404);
            }
        }
        //2. Which politician gave the most speeches on the topic „Internal Security"?
        $getMostSecurity = $collectionCsv
            ->values()
            ->where('Topic', 'Internal Security')
            ->first();
        $mostSecurity = $getMostSecurity[ 'Speaker' ];
        //2. Which politician gave the most speeches on the topic „Internal Security"?
        //1. Which politician gave the most speeches in 2013?
        $filtered = $collectionCsv->values()->filter(function ($value, $key) {
            return Carbon::parse($value[ 'Date' ])->format('Y') >= '2013';
        });
        if (!$filtered->isEmpty()) {
            $result = $filtered->groupBy('Speaker')
                ->map
                ->count();
            $max = $result->max();
            $maxValues = $result->filter(function ($value) use ($max) {
                return $value == $max;
            });
            if ($maxValues->count() == 1) {
                $mostSpeeches = $maxValues->keys()->first().' '.$maxValues->values()->first();
            }
            if ($maxValues->count() > 1) {
                foreach ($maxValues as $key => $value) {
                    $mostSpeeches .= ' '.$key;
                }
            }
            //1. Which politician gave the most speeches in 2013?
        }
        //3. Which politician used the fewest words (in total)?
        $cold = collect();
        $result = $collectionCsv->values()->groupBy('Speaker')
            ->map(function ($carry, $item) {
                return $carry->reduce(function ($carry, $item) {
                    return $carry + $item[ 'Words' ];
                });
            });
        $min = $result->min();
        $minValues = $result->filter(function ($value) use ($min) {
            return $value == $min;
        });
        if ($minValues->count() == 1) {
            $leastWordy = $minValues->keys()->first().' '.$minValues->values()->first();
        }
        if ($minValues->count() > 1) {
            foreach ($minValues as $key => $value) {
                $leastWordy .= ' '.$key;
            }
        }
        //3. Which politician used the fewest words (in total)?
        return response()->json([
            'mostSpeeches' => $mostSpeeches,
            'mostSecurity' => $mostSecurity,
            'leastWordy' => $leastWordy,
        ], 200);
    }
}
