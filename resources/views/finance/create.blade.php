@extends('layouts.app')

@section('title', 'Add Transaction')
@section('page-title', 'Record Transaction')
@section('page-subtitle', 'Add an income or expense record to your cash flow')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
            <div class="section-title">New Financial Entry</div>
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
                <label class="form-label">Transaction Type</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="expense" id="type_expense"
                               {{ old('type', $defaultType) === 'expense' ? 'checked' : '' }} style="display:none;"
                               onchange="switchType('expense')">
                        <div class="type-badge {{ old('type', $defaultType) === 'expense' ? 'active-expense' : '' }}" id="badge_expense"
                             style="padding:0.75rem;text-align:center;border-radius:12px;border:1.5px solid var(--border);font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.15s ease;">
                            <i data-lucide="arrow-up-right" style="width:18px;height:18px;"></i>
                            <span>Expense</span>
                        </div>
                    </label>

                    <label style="cursor:pointer;">
                        <input type="radio" name="type" value="income" id="type_income"
                               {{ old('type', $defaultType) === 'income' ? 'checked' : '' }} style="display:none;"
                               onchange="switchType('income')">
                        <div class="type-badge {{ old('type', $defaultType) === 'income' ? 'active-income' : '' }}" id="badge_income"
                             style="padding:0.75rem;text-align:center;border-radius:12px;border:1.5px solid var(--border);font-weight:700;display:flex;align-items:center;justify-content:center;gap:0.5rem;transition:all 0.15s ease;">
                            <i data-lucide="arrow-down-left" style="width:18px;height:18px;"></i>
                            <span>Income</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Title & Amount --}}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="title">Transaction Title <span style="color:#DC2626;">*</span></label>
                    <input type="text" id="title" name="title" class="form-control"
                           placeholder="e.g. Grocery Shopping, Monthly Salary, Auto Fare"
                           value="{{ old('title') }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Amount (₹) <span style="color:#DC2626;">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control"
                           step="0.01" min="0.01" placeholder="0.00" value="{{ old('amount') }}" required>
                </div>
            </div>

            {{-- Category & Payment Method --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                {{-- Expense Category Select --}}
                <div class="form-group" id="expenseCategoryGroup" style="{{ old('type', $defaultType) === 'income' ? 'display:none;' : '' }}">
                    <label class="form-label" for="expense_category_id">Expense Category <span style="color:#DC2626;">*</span></label>
                    <select id="expense_category_id" name="expense_category_id" class="form-control">
                        <option value="">Select Category</option>
                        @foreach($expenseCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Income Category Select --}}
                <div class="form-group" id="incomeCategoryGroup" style="{{ old('type', $defaultType) === 'expense' ? 'display:none;' : '' }}">
                    <label class="form-label" for="income_category_id">Income Source / Category <span style="color:#DC2626;">*</span></label>
                    <select id="income_category_id" name="income_category_id" class="form-control">
                        <option value="">Select Category</option>
                        @foreach($incomeCategories as $cat)
                            <option value="{{ $cat->id }}" {{ old('income_category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Method --}}
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method <span style="color:#DC2626;">*</span></label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        @foreach(['Cash', 'Card', 'UPI', 'Bank Transfer', 'Digital Wallet', 'Other'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', 'UPI') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Date & Notes --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="transaction_date">Transaction Date <span style="color:#DC2626;">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date" class="form-control"
                           value="{{ old('transaction_date', now()->toDateString()) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Receipt Photo / Document</label>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <label for="receipt" class="btn btn-outline btn-sm" style="cursor:pointer;">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> Upload Receipt
                        </label>
                        <input type="file" id="receipt" name="receipt" accept="image/*,application/pdf" style="display:none;" onchange="previewReceipt(this)">
                        <span id="receiptFileName" style="font-size:0.75rem;color:var(--text-muted);">Max 3MB</span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes & Remarks</label>
                <textarea id="notes" name="notes" class="form-control" rows="2"
                          placeholder="Optional notes or details about this transaction...">{{ old('notes') }}</textarea>
            </div>

            {{-- Receipt Preview Container --}}
            <div id="receiptPreviewBox" style="display:none;margin-bottom:1rem;">
                <img id="receiptPreviewImg" src="" alt="Receipt Preview" style="max-height:120px;border-radius:10px;border:1px solid var(--border);">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
                <a href="{{ route('finance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding-left:1.5rem;padding-right:1.5rem;">
                    <i data-lucide="check" style="width:14px;height:14px;"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<style>
    .type-badge.active-expense {
        background: #FEE2E2 !important;
        color: #DC2626 !important;
        border-color: #F87171 !important;
    }
    .type-badge.active-income {
        background: #DCFCE7 !important;
        color: #16A34A !important;
        border-color: #86EFAC !important;
    }
</style>
<script>
    function switchType(type) {
        const badgeExp = document.getElementById('badge_expense');
        const badgeInc = document.getElementById('badge_income');
        const groupExp = document.getElementById('expenseCategoryGroup');
        const groupInc = document.getElementById('incomeCategoryGroup');

        if (type === 'expense') {
            badgeExp.classList.add('active-expense');
            badgeInc.classList.remove('active-income');
            groupExp.style.display = 'block';
            groupInc.style.display = 'none';
        } else {
            badgeInc.classList.add('active-income');
            badgeExp.classList.remove('active-expense');
            groupExp.style.display = 'none';
            groupInc.style.display = 'block';
        }
    }

    function previewReceipt(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('receiptFileName').textContent = file.name;

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('receiptPreviewImg').src = e.target.result;
                    document.getElementById('receiptPreviewBox').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    }
</script>
@endpush
@endsection
