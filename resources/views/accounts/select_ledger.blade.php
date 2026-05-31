@extends('layouts.app')
@section('title', 'اختر حساباً لعرض كشف الحساب')
@section('content')
<style>
/* تخصيص pagination مع RTL */
.pagination {
    gap: 6px;
}

.page-link {
    border-radius: 30px !important;
    padding: 8px 15px;
    color: #0d6efd;
    background-color: #fff;
    border: 1px solid #dee2e6;
    transition: all 0.2s;
}

.page-link:hover {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
    transform: translateY(-2px);
}

.active > .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
}

.disabled > .page-link {
    color: #adb5bd;
    background-color: #f8f9fa;
}
</style>
<div class="container" style="direction: rtl; margin-top: 30px;">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📊 اختر الحساب لعرض كشف الحساب</h5>
            <form method="GET" action="{{ route('accounts.select.ledger') }}" class="form-inline">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="بحث بالكود أو الاسم..."
                           value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-light" type="submit">🔍 بحث</button>
                    </div>
                    @if(request('search'))
                        <a href="{{ route('accounts.select.ledger') }}" class="btn btn-outline-light">✖️ إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body">
            @if($accounts->isEmpty())
                <div class="alert alert-warning">لا توجد حسابات نهائية (leaf) مطابقة لمعايير البحث.</div>
            @else
                <div class="row">
                    @foreach($accounts as $account)
                        <div class="col-md-4 col-sm-6 mb-3">
                            <div class="card h-100 @if(!$account->is_active) border-danger @endif">
                                <div class="card-body text-center">
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">{{ $account->code }}</span>
                                    </div>
                                    <h6 class="card-title">{{ $account->name }}</h6>
                                    <p class="card-text small text-muted">
                                        {{ $account->type_name }}
                                        @if($account->normal_balance == 'debit') (مدين) @else (دائن) @endif
                                    </p>
                                    <a href="{{ route('accounts.ledger', $account) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> عرض كشف الحساب
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $accounts->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection