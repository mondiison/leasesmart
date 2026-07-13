<?php

namespace App\Http\Controllers;

use App\Support\Exports\OperationalCsvExport;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __invoke(Request $request, string $type, OperationalCsvExport $export): StreamedResponse
    {
        abort_unless(in_array($type, OperationalCsvExport::TYPES, true), 404);

        return response()->streamDownload(
            fn () => $export->stream($request->user(), $type, $request),
            $export->filename($type),
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
