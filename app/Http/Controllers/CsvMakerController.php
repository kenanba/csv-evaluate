<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvMakerController extends Controller
{

    public function __invoke($values)
    {
        $columns = [
            'Speaker',
            'Topic',
            'Date',
            'Words',
        ];
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=summary.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        $data = config("csvValues.$values");

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $items) {
                fputcsv($file, $items);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

}
