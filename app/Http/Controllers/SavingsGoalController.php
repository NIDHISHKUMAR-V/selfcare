<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavingsGoalRequest;
use App\Models\SavingsGoal;
use App\Services\FinanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsGoalController extends Controller
{
    public function __construct(
        protected FinanceService $financeService
    ) {}

    /**
     * Display savings goals dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        $summary = $this->financeService->getSavingsSummary($user);

        return view('finance.savings', [
            'goals' => $summary['goals'],
            'totalTarget' => $summary['total_target'],
            'totalSaved' => $summary['total_saved'],
            'overallProgress' => $summary['overall_progress'],
        ]);
    }

    /**
     * Store a new savings goal.
     */
    public function store(SavingsGoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['current_saved_amount'] = $data['current_saved_amount'] ?? 0;
        $data['status'] = $data['status'] ?? 'active';

        SavingsGoal::create($data);

        return redirect()->route('savings.index')->with('success', 'Savings goal created successfully!');
    }

    /**
     * Update an existing savings goal.
     */
    public function update(SavingsGoalRequest $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to savings goal.');
        }

        $data = $request->validated();
        $savingsGoal->update($data);

        return redirect()->route('savings.index')->with('success', 'Savings goal updated successfully!');
    }

    /**
     * Add funds to a savings goal.
     */
    public function addFunds(Request $request, SavingsGoal $savingsGoal): RedirectResponse
    {
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to savings goal.');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $newTotal = $savingsGoal->current_saved_amount + (float) $request->input('amount');
        $status = $savingsGoal->status;

        if ($newTotal >= $savingsGoal->target_amount && $status === 'active') {
            $status = 'reached';
        }

        $savingsGoal->update([
            'current_saved_amount' => $newTotal,
            'status' => $status,
        ]);

        return redirect()->route('savings.index')->with('success', "Added ₹{$request->input('amount')} to {$savingsGoal->goal_name}!");
    }

    /**
     * Delete a savings goal.
     */
    public function destroy(SavingsGoal $savingsGoal): RedirectResponse
    {
        if ($savingsGoal->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to savings goal.');
        }

        $savingsGoal->delete();

        return redirect()->route('savings.index')->with('success', 'Savings goal removed.');
    }
}
