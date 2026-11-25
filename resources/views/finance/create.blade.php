@extends('layouts.app')

@section('title', 'Add Transaction')
@section('page-title', 'Record Transaction')
@section('page-subtitle', 'Add an income or expense record to your cash flow')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <div class="card" style="padding:1.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
            <div>
                <h2 class="section-title" style="font-size:1.15rem;">New Financial Entry</h2>
                <p class="section-subtitle">Keep your cash flow up to date and within budget</p>
            </div>
            <a href="{{ route('finance.index') }}" class="btn btn-outline btn-sm">
                <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Back to Ledger
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-error">
                <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
                <div>
                    <ul style="margin:0;padding-left:1rem;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Type Toggle: Expense vs Income --}}
            <div class="form-group" style="margin-bottom:1.5rem;">
                <label class="form-label required">Transaction Type</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="expense" id="type_expense"
                               {{ old('type', $defaultType) === 'expense' ? 'checked' : '' }} style="display:none;"
                               onchange="window.switchTransactionType('expense')">
                        <div class="type-badge {{ old('type', $defaultType) === 'expense' ? 'active-expense' : '' }}" id="badge_expense"
                             style="padding:0.75rem;text-align:center;border-radius:12px;border:1.5px solid var(--border);font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.15s ease;background:#FAF9FD;">
                            <i data-lucide="arrow-up-right" style="width:18px;height:18px;"></i>
                            <span>Expense</span>
                        </div>
                    </label>

                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="income" id="type_income"
                               {{ old('type', $defaultType) === 'income' ? 'checked' : '' }} style="display:none;"
                               onchange="window.switchTransactionType('income')">
                        <div class="type-badge {{ old('type', $defaultType) === 'income' ? 'active-income' : '' }}" id="badge_income"
                             style="padding:0.75rem;text-align:center;border-radius:12px;border:1.5px solid var(--border);font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.15s ease;background:#FAF9FD;">
                            <i data-lucide="arrow-down-left" style="width:18px;height:18px;"></i>
                            <span>Income</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="form-grid-2">
                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label required" for="title">Title / Description</label>
                    <input type="text" id="title" name="title" class="form-control"
                           placeholder="e.g. Grocery run, Monthly paycheck, Gas"
                           value="{{ old('title') }}" required autofocus>
                </div>

                {{-- Amount --}}
                <div class="form-group">
                    <label class="form-label required" for="amount">Amount ($)</label>
                    <input type="number" id="amount" name="amount" class="form-control"
                           step="0.01" min="0.01" placeholder="0.00" value="{{ old('amount') }}" required>
                </div>
            </div>

            {{-- Dynamic Categories --}}
            <div id="expenseCategoryGroup" style="{{ old('type', $defaultType) === 'expense' ? '' : 'display:none;' }}">
                <div class="form-group">
                    <label class="form-label required" for="expense_category_id">Expense Category</label>
                    <select id="expense_category_id" name="expense_category_id" class="form-control">
                        <option value="">Select an expense category</option>
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="incomeCategoryGroup" style="{{ old('type', $defaultType) === 'income' ? '' : 'display:none;' }}">
                <div class="form-group">
                    <label class="form-label required" for="income_category_id">Income Category</label>
                    <select id="income_category_id" name="income_category_id" class="form-control">
                        <option value="">Select an income source</option>
                        @foreach($incomeCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('income_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-grid-2">
                {{-- Transaction Date --}}
                <div class="form-group">
                    <label class="form-label required" for="transaction_date">Date</label>
                    <input type="date" id="transaction_date" name="transaction_date" class="form-control"
                           value="{{ old('transaction_date', now()->toDateString()) }}" required>
                </div>

                {{-- Payment Method --}}
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        @foreach(['Credit Card', 'Debit Card', 'Cash', 'Bank Transfer', 'UPI / Digital Wallet', 'Other'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Receipt Upload --}}
            <div class="form-group">
                <label class="form-label">Receipt Image (Optional)</label>
                <label for="receipt" class="image-upload-dropzone" style="display:block;">
                    <i data-lucide="receipt" class="upload-icon"></i>
                    <div class="upload-text">Upload Receipt or Invoice</div>
                    <div class="upload-subtext" id="receiptFileName">PNG, JPG, or PDF up to 4MB</div>
                    <input type="file" id="receipt" name="receipt" accept="image/*,.pdf" style="display:none;"
                           onchange="window.SelfCare.previewImage(this, 'receiptPreviewImg')">
                </label>
                <div style="margin-top:0.75rem;text-align:center;">
                    <img id="receiptPreviewImg" src="" alt="Receipt Preview" style="display:none;max-height:160px;margin:0 auto;border-radius:12px;border:1px solid var(--border);">
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes / Memo</label>
                <textarea id="notes" name="notes" class="form-control" rows="2"
                          placeholder="Reference number, shop name, or additional details...">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);">
                <a href="{{ route('finance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.75rem;">
                    <i data-lucide="check" style="width:15px;height:15px;"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
