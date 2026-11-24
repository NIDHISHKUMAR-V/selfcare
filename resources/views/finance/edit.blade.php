@extends('layouts.app')

@section('title', 'Edit Transaction')
@section('page-title', 'Edit Transaction')
@section('page-subtitle', 'Update financial transaction details')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <div class="card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:0.75rem;border-bottom:1px solid var(--border);">
            <div class="section-title">Edit {{ ucfirst($transaction->type) }} Entry</div>
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

        <form method="POST" action="{{ route('finance.update', $transaction) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Type (Hidden, or displayed as badge) --}}
            <input type="hidden" name="type" value="{{ $transaction->type }}">

            <div style="margin-bottom:1.25rem;">
                <span class="badge" style="font-size:0.85rem;padding:0.35rem 0.75rem;background:{{ $transaction->type === 'income' ? 'var(--sage-light)' : 'var(--peach-light)' }};color:{{ $transaction->type === 'income' ? 'var(--sage)' : '#C2500C' }};">
                    {{ ucfirst($transaction->type) }} Transaction
                </span>
            </div>

            {{-- Title & Amount --}}
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="title">Transaction Title <span style="color:#DC2626;">*</span></label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="{{ old('title', $transaction->title) }}" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="amount">Amount (₹) <span style="color:#DC2626;">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control"
                           step="0.01" min="0.01" value="{{ old('amount', $transaction->amount) }}" required>
                </div>
            </div>

            {{-- Category & Payment Method --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @if($transaction->type === 'expense')
                    <div class="form-group">
                        <label class="form-label" for="expense_category_id">Expense Category <span style="color:#DC2626;">*</span></label>
                        <select id="expense_category_id" name="expense_category_id" class="form-control">
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id', $transaction->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="form-group">
                        <label class="form-label" for="income_category_id">Income Source / Category <span style="color:#DC2626;">*</span></label>
                        <select id="income_category_id" name="income_category_id" class="form-control">
                            <option value="">Select Category</option>
                            @foreach($incomeCategories as $cat)
                                <option value="{{ $cat->id }}" {{ old('income_category_id', $transaction->income_category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Payment Method --}}
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method <span style="color:#DC2626;">*</span></label>
                    <select id="payment_method" name="payment_method" class="form-control" required>
                        @foreach(['Cash', 'Card', 'UPI', 'Bank Transfer', 'Digital Wallet', 'Other'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', $transaction->payment_method) === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Date & Receipt --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label" for="transaction_date">Transaction Date <span style="color:#DC2626;">*</span></label>
                    <input type="date" id="transaction_date" name="transaction_date" class="form-control"
                           value="{{ old('transaction_date', $transaction->transaction_date->toDateString()) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Receipt Photo / Document</label>
                    @if($transaction->receipt_path)
                        <div style="margin-bottom:0.5rem;display:flex;align-items:center;gap:0.5rem;">
                            <a href="{{ Storage::disk('public')->url($transaction->receipt_path) }}" target="_blank" class="btn btn-outline btn-sm">
                                <i data-lucide="eye" style="width:13px;height:13px;"></i> View Current Receipt
                            </a>
                        </div>
                    @endif
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <label for="receipt" class="btn btn-outline btn-sm" style="cursor:pointer;">
                            <i data-lucide="upload" style="width:14px;height:14px;"></i> {{ $transaction->receipt_path ? 'Replace Receipt' : 'Upload Receipt' }}
                        </label>
                        <input type="file" id="receipt" name="receipt" accept="image/*,application/pdf" style="display:none;" onchange="previewReceipt(this)">
                        <span id="receiptFileName" style="font-size:0.75rem;color:var(--text-muted);">Max 3MB</span>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes & Remarks</label>
                <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $transaction->notes) }}</textarea>
            </div>

            <div id="receiptPreviewBox" style="display:none;margin-bottom:1rem;">
                <img id="receiptPreviewImg" src="" alt="Receipt Preview" style="max-height:120px;border-radius:10px;border:1px solid var(--border);">
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;">
                <a href="{{ route('finance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding-left:1.5rem;padding-right:1.5rem;">
                    <i data-lucide="save" style="width:14px;height:14px;"></i> Update Transaction
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
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
