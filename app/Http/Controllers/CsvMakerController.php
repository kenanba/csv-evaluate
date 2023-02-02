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
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="file.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['column1', 'column2', 'column3']);
            $data = [
                ['Speaker' => 'Matrix Abel', 'Topic' => 'Education Policy', 'Date' => '2012-10-12', 'Words' => '6052'],
                ['Speaker' => 'Bernhard Belling', 'Topic' => 'Coal Subsidies', 'Date' => '2012-11-25', 'Words' => '4515'],
                ['Speaker' => 'LKenan Collins', 'Topic' => 'Coal Subsidies', 'Date' => '2012-11-14', 'Words' => '8898'],
                ['Speaker' => 'Alexander Abel', 'Topic' => 'Internal Security', 'Date' => '2012-12-23', 'Words' => '258']
            ];

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::streamDownload($callback, 'file.csv', $headers);
    }
//    public function __invoke($values)
//    {
//        $columns = [
//            'Speaker',
//            'Topic',
//            'Date',
//            'Words',
//        ];
//        $headers = array(
//            "Content-type" => "text/csv",
//            "Content-Disposition" => "attachment; filename=summary.csv",
//        );
//        $data = config("csvValues.$values");
//
//        $callback = function () use ($data, $columns) {
//            $file = fopen('php://output', 'w');
//            fputcsv($file, $columns);
//            foreach ($data as $items) {
//                fputcsv($file, $items);
//            }
//            fclose($file);
//        };
//        return response()->stream($callback, 200, $headers);
//    }

}
