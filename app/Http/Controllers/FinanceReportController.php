<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display financial analytics, charts, and balance reports.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $month = $request->query('month', Carbon::today()->format('Y-m'));

        $summary = $this->financeService->getMonthlySummary($user, $month);
        $chartsData = $this->financeService->getFinancialReportsData($user, 6, $month);

        return view('finance.reports', [
            'summary' => $summary,
            'chartsData' => $chartsData,
            'currentMonth' => $month,
        ]);
    }

    /**
     * Export user financial transactions as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = auth()->user();
        $month = $request->query('month');

        $query = Transaction::where('user_id', $user->id)
            ->with(['incomeCategory', 'expenseCategory'])
            ->orderBy('transaction_date', 'asc');

        if ($month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $query->whereBetween('transaction_date', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString(),
            ]);
        }

        $filename = 'transactions_' . ($month ?: 'all_' . Carbon::today()->toDateString()) . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // Write CSV headers
            fputcsv($handle, ['ID', 'Date', 'Type', 'Title', 'Category', 'Amount', 'Payment Method', 'Notes']);

            $query->chunk(200, function ($transactions) use ($handle): void {
                foreach ($transactions as $t) {
                    $categoryName = $t->type === 'income'
                        ? ($t->incomeCategory?->name ?? 'Income')
                        : ($t->expenseCategory?->name ?? 'Expense');

                    fputcsv($handle, [
                        $t->id,
                        $t->transaction_date->toDateString(),
                        ucfirst($t->type),
                        $t->title,
                        $categoryName,
                        $t->amount,
                        $t->payment_method,
                        $t->notes ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
