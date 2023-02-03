<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateUrls;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @property string $ulid
 */
class CsvEvaluateController extends Controller
{
    const URL = 'http://127.0.0.1:8000/csv-evaluate?url[]=http://127.0.0.1:8080/api/csv-maker/val1&url[]=http://127.0.0.1:8080/api/csv-maker/val2';

    private array $csv = [];
    private mixed $collectionCsv;

    public function __construct()
    {
        $this->csv = [];
        $this->collectionCsv = [];
        $this->ulid = (string)Str::ulid();
    }

    public function index(ValidateUrls $request): JsonResponse
    {
        foreach ($request->query('url') as $key => $url) {
            $fileName = "$this->ulid/file_$key.csv";
            $response = Http::withOptions(['timeout' => 60])->get($url);
            if ($response->successful()) {
                Storage::disk('public')->put($fileName, $response->body());
                $rows = array_map(function ($v) { return str_getcsv($v, ","); },
                    file(storage_path('/app/public/').$fileName)
                );
                $header = array_shift($rows);
                foreach ($rows as $row) {
                    $this->csv[] = array_combine($header, $row);
                }
                $validationResult = $this->validateCsvData($this->csv);
                if ($validationResult) {
                    return response()->json($validationResult->original->messages(), 400);
                }
                $this->collectionCsv = collect($this->csv);
            } else {
                return response()->json($url.' => The  URL is  not valid', 404);
            }
        }
        return response()->json([
            'mostSpeeches' => $this->MostSpeechesPolitician($this->collectionCsv),
            'mostSecurity' => $this->internalSecurityCheck($this->collectionCsv),
            'leastWordy' => $this->fewestWords($this->collectionCsv),
        ], 200);
    }

    private function validateCsvData(array $csv)
    {
        foreach ($csv as $array) {
            $validatedData = Validator::make($array, [
                'Speaker' => ['required','string'],
                'Topic' => ['required','string'],
                'Date' => ['required','date'],
                'Words' => ['required','numeric', 'regex:/^[0-9]+$/'],
            ]);
            if ($validatedData->fails()) {
                return response()->json($validatedData->errors(), 400);
            }
        }
    }

    /**
     * @param $collectionCsv
     * @return mixed
     */
    public function internalSecurityCheck($collectionCsv): mixed
    {
        $getMostSecurity = $collectionCsv
            ->values()
            ->where('Topic', 'Internal Security')
            ->first();
        return $getMostSecurity[ 'Speaker' ];
    }

    /**
     * @param $collectionCsv
     * @return string|void
     */
    public function MostSpeechesPolitician($collectionCsv)
    {
        $filtered = $collectionCsv->values()->filter(function ($value, $key) {
            return Carbon::parse($value[ 'Date' ])->format('Y') == '2013';
        });
        if (!$filtered->isEmpty()) {
            $result = $filtered->groupBy('Speaker')
                ->map
                ->count();
            return $this->checkIfOneOrMoreValues($result, $result->max());
        }
    }

    /**
     * @param $collectionCsv
     * @return string
     */
    public function fewestWords($collectionCsv): string
    {
        $result = $collectionCsv->values()->groupBy('Speaker')
            ->map(function ($carry, $item) {
                return $carry->reduce(function ($carry, $item) {
                    return $carry + $item[ 'Words' ];
                });
            });
        return $this->checkIfOneOrMoreValues($result, $result->min());
    }

    /**
     * @param $result
     * @param $itemValue
     * @return string
     */
    public function checkIfOneOrMoreValues($result, $itemValue): string
    {
        $response = '';
        $values = $result->filter(function ($value) use ($itemValue) {
            return $value == $itemValue;
        });
        if ($values->count() == 1) {
            $response = $values->keys()->first().' '.$values->values()->first();
        }
        if ($values->count() > 1) {
            foreach ($values as $key => $value) {
                $response .= ' '.$key;
            }
        }
        return $response;
    }
}
