@extends('layouts.app')
@section('title', 'سجل تعديلات القيد')
@section('content')
@php
    $backQuery = http_build_query(request()->query());
    $backUrl = route('journal.index') . ($backQuery ? '?' . $backQuery : '');
@endphp
<style>
    .json-viewer {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        font-family: monospace;
        font-size: 12px;
        white-space: pre-wrap;
        word-break: break-word;
        max-height: 300px;
        overflow: auto;
        border: 1px solid #e9ecef;
    }
    .badge-action {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    .badge-edit { background: #fff3cd; color: #856404; }
    .badge-approve { background: #d4edda; color: #155724; }
    .badge-reverse { background: #f8d7da; color: #721c24; }
    .badge-restore { background: #d1ecf1; color: #0c5460; }
    .diff-table td {
        vertical-align: top;
    }
    .diff-section {
        margin-bottom: 20px;
    }
    
    /* === الإضافات الجديدة === */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .table-responsive .table td {
        min-width: 180px;
        vertical-align: top;
    }
    .table-responsive .table td:nth-child(4),
    .table-responsive .table td:nth-child(5) {
        min-width: 350px;  /* عمودي البيانات القديمة والجديدة يأخذان عرض أكبر */
    }
    .json-viewer table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
</style>
<div class="container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>📜 سجل تعديلات القيد: <strong>{{ $entry->reference }}</strong> (رقم {{ $entry->id }})</h5>
            <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary">🔙 رجوع</a>
        </div>
        <div class="card-body">
            @if($entry->editLogs->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%">التاريخ</th>
                                <th style="width: 12%">المستخدم</th>
                                <th style="width: 12%">الإجراء</th>
                                <th style="width: 30%">البيانات القديمة</th>
                                <th style="width: 30%">البيانات الجديدة</th>
                                <th style="width: 15%">الملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entry->editLogs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $log->user->name ?? 'نظام' }}</td>
                                <td>
                                    @php
                                        $actionClass = '';
                                        $actionIcon = '';
                                        switch($log->action) {
                                            case 'edit': $actionClass = 'badge-edit'; $actionIcon = '✏️'; $actionText = 'تعديل'; break;
                                            case 'approve': $actionClass = 'badge-approve'; $actionIcon = '✅'; $actionText = 'اعتماد'; break;
                                            case 'reverse': $actionClass = 'badge-reverse'; $actionIcon = '🔁'; $actionText = 'عكس القيد'; break;
                                            case 'restore': $actionClass = 'badge-restore'; $actionIcon = '🔄'; $actionText = 'استعادة'; break;
                                            default: $actionClass = ''; $actionIcon = ''; $actionText = $log->action;
                                        }
                                    @endphp
                                    <span class="badge-action {{ $actionClass }}">{{ $actionIcon }} {{ $actionText }}</span>
                                </td>
                                <td>
                                    @if($log->old_data)
                                        <details>
                                            <summary style="cursor: pointer; color: #0d6efd;">📄 عرض التفاصيل</summary>
                                            <div class="json-viewer mt-2">
                                                @php
                                                    $old = is_string($log->old_data) ? json_decode($log->old_data, true) : $log->old_data;
                                                @endphp
                                                @if($old)
                                                    @if(isset($old['lines']))
                                                        <strong>📌 معلومات القيد:</strong><br>
                                                        المرجع: {{ $old['reference'] ?? '-' }}<br>
                                                        التاريخ: {{ $old['entry_date'] ?? '-' }}<br>
                                                        الحالة: {{ $old['status'] ?? '-' }}<br>
                                                        <strong>📋 الأسطر المحاسبية:</strong>
                                                        <table class="table table-sm table-bordered mt-2">
                                                            <thead>
                                                                <tr><th>الحساب</th><th>مدين</th><th>دائن</th><th>البيان</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($old['lines'] as $line)
                                                                    <tr>
                                                                        <td>{{ $line['account_id'] ?? '' }} ({{ \App\Models\Account::find($line['account_id'])?->name ?? 'حساب محذوف' }})</td>
                                                                        <td class="text-success">{{ number_format($line['debit'], 2) }}</td>
                                                                        <td class="text-danger">{{ number_format($line['credit'], 2) }}</td>
                                                                        <td>{{ $line['description'] ?? '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <pre>{{ json_encode($old, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @endif
                                                @else
                                                    {{ $log->old_data }}
                                                @endif
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->new_data)
                                        <details>
                                            <summary style="cursor: pointer; color: #0d6efd;">📄 عرض التفاصيل</summary>
                                            <div class="json-viewer mt-2">
                                                @php
                                                    $new = is_string($log->new_data) ? json_decode($log->new_data, true) : $log->new_data;
                                                @endphp
                                                @if($new)
                                                    @if(isset($new['lines']))
                                                        <strong>📌 معلومات القيد:</strong><br>
                                                        المرجع: {{ $new['reference'] ?? '-' }}<br>
                                                        التاريخ: {{ $new['entry_date'] ?? '-' }}<br>
                                                        الحالة: {{ $new['status'] ?? '-' }}<br>
                                                        <strong>📋 الأسطر المحاسبية:</strong>
                                                        <table class="table table-sm table-bordered mt-2">
                                                            <thead>
                                                                <tr><th>الحساب</th><th>مدين</th><th>دائن</th><th>البيان</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($new['lines'] as $line)
                                                                    <tr>
                                                                        <td>{{ $line['account_id'] ?? '' }} ({{ \App\Models\Account::find($line['account_id'])?->name ?? 'حساب محذوف' }})</td>
                                                                        <td class="text-success">{{ number_format($line['debit'], 2) }}</td>
                                                                        <td class="text-danger">{{ number_format($line['credit'], 2) }}</td>
                                                                        <td>{{ $line['description'] ?? '' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <pre>{{ json_encode($new, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @endif
                                                @else
                                                    {{ $log->new_data }}
                                                @endif
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $log->notes ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">لا توجد تعديلات على هذا القيد.</div>
            @endif
        </div>
    </div>
</div>
@endsection