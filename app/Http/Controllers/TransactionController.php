<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Transaction;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display a listing of user transactions with filters.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $month = $request->query('month', Carbon::today()->format('Y-m'));
        $type = $request->query('type', 'all');
        $search = $request->query('search');
        $categoryId = $request->query('category_id');

        $summary = $this->financeService->getMonthlySummary($user, $month);

        $query = Transaction::where('user_id', $user->id)
            ->with(['incomeCategory', 'expenseCategory'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if ($type !== 'all' && in_array($type, ['income', 'expense'], true)) {
            $query->where('type', $type);
        }

        if ($month) {
            $date = Carbon::createFromFormat('Y-m', $month);
            $query->whereBetween('transaction_date', [
                $date->copy()->startOfMonth()->toDateString(),
                $date->copy()->endOfMonth()->toDateString(),
            ]);
        }

        if ($categoryId) {
            $query->where(function ($q) use ($categoryId): void {
                $q->where('expense_category_id', $categoryId)
                  ->orWhere('income_category_id', $categoryId);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        $incomeCategories = IncomeCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();
        $expenseCategories = ExpenseCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();

        return view('finance.index', [
            'transactions' => $transactions,
            'summary' => $summary,
            'currentMonth' => $month,
            'currentType' => $type,
            'search' => $search,
            'categoryId' => $categoryId,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $defaultType = $request->query('type', 'expense');
        $incomeCategories = IncomeCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();
        $expenseCategories = ExpenseCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();
        return view('finance.create', [
            'defaultType' => $defaultType,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        Transaction::create($data);

        return redirect()->route('finance.index', [
            'month' => Carbon::parse($data['transaction_date'])->format('Y-m'),
        ])->with('success', ucfirst($data['type']) . ' recorded successfully!');
    }

    /**
     * Show the form for editing an existing transaction.
     */
    public function edit(Transaction $transaction): View
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $user = auth()->user();
        $incomeCategories = IncomeCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();
        $expenseCategories = ExpenseCategory::where('user_id', $user->id)->orWhereNull('user_id')->get();

        return view('finance.edit', [
            'transaction' => $transaction,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Update an existing transaction.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to transaction.');
        }

        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            if ($transaction->receipt_path && Storage::disk('public')->exists($transaction->receipt_path)) {
                Storage::disk('public')->delete($transaction->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        $transaction->update($data);

        return redirect()->route('finance.index', [
            'month' => Carbon::parse($transaction->transaction_date)->format('Y-m'),
        ])->with('success', 'Transaction updated successfully!');
    }

    /**
     * Delete an existing transaction.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to transaction.');
        }

        if ($transaction->receipt_path && Storage::disk('public')->exists($transaction->receipt_path)) {
            Storage::disk('public')->delete($transaction->receipt_path);
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction removed successfully.');
    }
}
