<?php

namespace App\Http\Controllers;

use App\Support\Reports\PremiumOperationalReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumReportController extends Controller
{
    public function __invoke(Request $request, string $type, PremiumOperationalReport $report): Response
    {
        abort_unless(in_array($type, PremiumOperationalReport::TYPES, true), 404);

        $payload = $report->for($request->user(), $type, $request);
        $filename = 'smartrent-'.$type.'-premium-'.now()->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('reports.premium-pdf', ['report' => $payload])
            ->setPaper('a4')
            ->stream($filename);
    }
}
