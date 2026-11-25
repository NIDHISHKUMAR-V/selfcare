<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OverallReportController extends Controller
{
    public function __construct(
        protected AnalyticsReportService $reportService
    ) {}

    /**
     * Display reports and life analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $preset = $request->query('preset', 'last_7_days');

        match ($preset) {
            'today' => [
                $startDate = Carbon::today(),
                $endDate = Carbon::today(),
            ],
            'this_week' => [
                $startDate = Carbon::today()->startOfWeek(),
                $endDate = Carbon::today()->endOfWeek(),
            ],
            'last_30_days' => [
                $startDate = Carbon::today()->subDays(29),
                $endDate = Carbon::today(),
            ],
            'this_month' => [
                $startDate = Carbon::today()->startOfMonth(),
                $endDate = Carbon::today()->endOfMonth(),
            ],
            default => [
                $startDate = Carbon::today()->subDays(6),
                $endDate = Carbon::today(),
            ],
        };

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->query('start_date'));
            $endDate = Carbon::parse($request->query('end_date'));
            $preset = 'custom';
        }

        $report = $this->reportService->getPerformanceReport($user, $startDate, $endDate);

        return view('reports.index', [
            'report' => $report,
            'preset' => $preset,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
        ]);
    }
}
