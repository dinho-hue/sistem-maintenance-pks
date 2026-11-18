<?php

namespace App\Http\Controllers;

use App\Exports\HmReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function index()
    {
        return view('reports.hm');
    }

    public function monthly(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
        ]);

        $year = (int) $validated['year'];
        $month = (int) $validated['month'];

        return Excel::download(
            new HmReportExport($year, $month),
            sprintf('monitoring-hm-%04d-%02d.xlsx', $year, $month)
        );
    }
}
