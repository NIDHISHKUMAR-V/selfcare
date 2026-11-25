@extends('layouts.app')

@section('title', 'Edit Transaction')
@section('page-title', 'Edit Transaction')
@section('page-subtitle', 'Update financial transaction details')

@section('content')
<div style="max-width:760px;margin:0 auto;">
    <div class="card" style="padding:1.75rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);">
            <div>
                <h2 class="section-title" style="font-size:1.15rem;">Edit {{ ucfirst($transaction->type) }} Entry</h2>
                <p class="section-subtitle">Keep your financial records accurate</p>
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

        <form method="POST" action="{{ route('finance.update', $transaction) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" name="type" value="{{ $transaction->type }}">

            <div style="margin-bottom:1.5rem;">
                <span class="badge" style="font-size:0.85rem;padding:0.4rem 0.85rem;background:{{ $transaction->type === 'income' ? '#DCFCE7' : '#FEE2E2' }};color:{{ $transaction->type === 'income' ? '#16A34A' : '#DC2626' }};">
                    <i data-lucide="{{ $transaction->type === 'income' ? 'arrow-down-left' : 'arrow-up-right' }}" style="width:15px;height:15px;"></i>
                    {{ ucfirst($transaction->type) }} Transaction
                </span>
            </div>

            <div class="form-grid-2">
                {{-- Title --}}
                <div class="form-group">
                    <label class="form-label required" for="title">Transaction Title</label>
                    <input type="text" id="title" name="title" class="form-control"
                           value="{{ old('title', $transaction->title) }}" required autofocus>
                </div>

                {{-- Amount --}}
                <div class="form-group">
                    <label class="form-label required" for="amount">Amount ($)</label>
                    <input type="number" id="amount" name="amount" class="form-control"
                           step="0.01" min="0.01" value="{{ old('amount', $transaction->amount) }}" required>
                </div>
            </div>

            {{-- Category --}}
            <div class="form-group">
                <label class="form-label required" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    @php $categories = $transaction->type === 'income' ? $incomeCategories : $expenseCategories; @endphp
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $transaction->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-grid-2">
                {{-- Transaction Date --}}
                <div class="form-group">
                    <label class="form-label required" for="transaction_date">Date</label>
                    <input type="date" id="transaction_date" name="transaction_date" class="form-control"
                           value="{{ old('transaction_date', $transaction->transaction_date->toDateString()) }}" required>
                </div>

                {{-- Payment Method --}}
                <div class="form-group">
                    <label class="form-label" for="payment_method">Payment Method</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        @foreach(['Credit Card', 'Debit Card', 'Cash', 'Bank Transfer', 'UPI / Digital Wallet', 'Other'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', $transaction->payment_method) === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Receipt Upload --}}
            <div class="form-group">
                <label class="form-label">Receipt Image</label>
                <label for="receipt" class="image-upload-dropzone" style="display:block;">
                    <i data-lucide="receipt" class="upload-icon"></i>
                    <div class="upload-text">Click to replace receipt image</div>
                    <div class="upload-subtext" id="receiptFileName">PNG, JPG, or PDF up to 4MB</div>
                    <input type="file" id="receipt" name="receipt" accept="image/*,.pdf" style="display:none;"
                           onchange="window.SelfCare.previewImage(this, 'receiptPreviewImg')">
                </label>
                <div style="margin-top:0.75rem;text-align:center;">
                    <img id="receiptPreviewImg" src="{{ $transaction->receipt_url ?: '' }}" alt="Receipt Preview" style="{{ $transaction->receipt_url ? 'display:block;' : 'display:none;' }}max-height:160px;margin:0 auto;border-radius:12px;border:1px solid var(--border);">
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label" for="notes">Notes / Memo</label>
                <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $transaction->notes) }}</textarea>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:0.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);">
                <a href="{{ route('finance.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding:0.6rem 1.75rem;">
                    <i data-lucide="check" style="width:15px;height:15px;"></i> Update Transaction
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
