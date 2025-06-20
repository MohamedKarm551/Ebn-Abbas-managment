
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-1"></i> سجل المعاملات المالية
            <span class="badge bg-info ms-2">{{ $transactions->total() }} معاملة</span>
        </h6>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="toggleView()">
                <i class="fas fa-th-large me-1"></i> <span id="view-toggle-text">عرض كارد</span>
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Table View (Default) -->
        <div id="table-view">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 10%">التاريخ</th>
                            <th style="width: 15%">من/إلى</th>
                            <th style="width: 12%">المبلغ</th>
                            <th style="width: 8%">العملة</th>
                            <th style="width: 10%">النوع</th>
                            <th style="width: 12%">التصنيف</th>
                            <th style="width: 8%">مرفق</th>
                            <th style="width: 15%">ملاحظات</th>
                            <th style="width: 10%">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr class="transaction-row {{ $transaction->type }}">
                            <td>
                                <span class="fw-bold">{{ $transaction->transaction_date->format('Y-m-d') }}</span>
                                <br>
                                <small class="text-muted">{{ $transaction->transaction_date->format('l') }}</small>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $transaction->from_to ?: 'غير محدد' }}</span>
                            </td>
                            <td>
                                <span class="fw-bold {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'withdrawal' ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge currency-badge bg-{{ $transaction->currency == 'SAR' ? 'primary' : ($transaction->currency == 'KWD' ? 'success' : 'info') }}">
                                    {{ $transaction->currency }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                    {{ $transaction->type_arabic }}
                                </span>
                            </td>
                            <td>
                                @if($transaction->category)
                                    <span class="badge bg-secondary">{{ $transaction->category }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($transaction->link_or_image)
                                    <a href="{{ Storage::url($transaction->link_or_image) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-info" title="عرض المرفق">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->notes)
                                    <span title="{{ $transaction->notes }}">
                                        {{ Str::limit($transaction->notes, 30) }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                       class="btn btn-sm btn-info" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.transactions.edit', $transaction) }}" 
                                       class="btn btn-sm btn-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.transactions.destroy', $transaction) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          id="delete-form-{{ $transaction->id }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                <h5>لا توجد معاملات مالية</h5>
                                <p>لم يتم العثور على معاملات مطابقة للفلاتر المحددة</p>
                                <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> إضافة معاملة جديدة
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card View (Hidden by default) -->
        <div id="card-view" style="display: none;">
            <div class="row">
                @forelse($transactions as $transaction)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card transaction-card {{ $transaction->type }} h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                {{ $transaction->type_arabic }}
                            </span>
                            <small class="text-muted">{{ $transaction->transaction_date->format('Y-m-d') }}</small>
                        </div>
                        
                        <div class="card-body">
                            <!-- Amount Display -->
                            <div class="text-center mb-3">
                                <h4 class="mb-1 {{ $transaction->type == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->type == 'withdrawal' ? '-' : '+' }}{{ number_format($transaction->amount, 2) }}
                                    <small class="text-muted">{{ $transaction->currency_symbol }}</small>
                                </h4>
                                <span class="badge bg-{{ $transaction->currency == 'SAR' ? 'primary' : ($transaction->currency == 'KWD' ? 'success' : 'info') }}">
                                    {{ $transaction->currency }}
                                </span>
                            </div>

                            <!-- Transaction Details -->
                            <div class="mb-2">
                                <strong class="text-muted">من/إلى:</strong>
                                <span>{{ $transaction->from_to ?: 'غير محدد' }}</span>
                            </div>

                            @if($transaction->category)
                            <div class="mb-2">
                                <strong class="text-muted">التصنيف:</strong>
                                <span class="badge bg-secondary">{{ $transaction->category }}</span>
                            </div>
                            @endif

                            @if($transaction->notes)
                            <div class="mb-3">
                                <strong class="text-muted">ملاحظات:</strong>
                                <p class="small mb-0">{{ Str::limit($transaction->notes, 50) }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <div>
                                @if($transaction->link_or_image)
                                    <a href="{{ Storage::url($transaction->link_or_image) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-paperclip"></i>
                                    </a>
                                @endif
                            </div>
                            <div class="action-buttons">
                                <a href="{{ route('admin.transactions.show', $transaction) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.transactions.edit', $transaction) }}" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.transactions.destroy', $transaction) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المعاملة؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">لا توجد معاملات مالية</h4>
                        <p class="text-muted">لم يتم العثور على معاملات مطابقة للفلاتر المحددة</p>
                        <a href="{{ route('admin.transactions.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> إضافة معاملة جديدة
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                عرض {{ $transactions->firstItem() ?? 0 }} إلى {{ $transactions->lastItem() ?? 0 }} 
                من أصل {{ $transactions->total() }} معاملة
            </div>
            <div>
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function toggleView() {
    const tableView = document.getElementById('table-view');
    const cardView = document.getElementById('card-view');
    const toggleText = document.getElementById('view-toggle-text');
    
    if (tableView.style.display === 'none') {
        // Show table view
        tableView.style.display = 'block';
        cardView.style.display = 'none';
        toggleText.textContent = 'عرض كارد';
    } else {
        // Show card view
        tableView.style.display = 'none';
        cardView.style.display = 'block';
        toggleText.textContent = 'عرض جدول';
    }
}
</script>