<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CsvMakerController extends Controller
{

    public function __invoke($values): BinaryFileResponse
    {
        $columns = [
            'Speaker',
            'Topic',
            'Date',
            'Words',
        ];
        $now = Carbon::now()->format('d-m-Y');
        $filename = "actions-{$now}";
        return $this->csv_file($columns, config("csvValues.$values"), $filename);
    }

    function csv_file($columns, $data, string $filename = 'export'): BinaryFileResponse
    {
        $file = fopen('php://memory', 'wb');
        $csvHeader = [...$columns];
        fputcsv($file, $csvHeader);
        foreach ($data as $line) {
            fputcsv($file, $line);
        }
        fseek($file, 0);
        $uid = Str::uuid();
        Storage::disk('public')->put("/$uid", $file);
        return response()->download(storage_path('app/public/'.$uid), "$filename.csv");
    }
}
