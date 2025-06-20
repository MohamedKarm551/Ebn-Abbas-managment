
<div class="card shadow mb-4">
    <div class="card-header filter-section py-3">
        <h6 class="m-0 font-weight-bold text-white">
            <i class="fas fa-filter me-1"></i> فلترة وبحث متقدم
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.transactions.index') }}" id="filterForm">
            <div class="row g-3">
                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-plus text-primary me-1"></i>
                        من تاريخ
                    </label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label fw-bold">
                        <i class="fas fa-calendar-minus text-primary me-1"></i>
                        إلى تاريخ
                    </label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>

                <!-- Currency Filter -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-globe text-info me-1"></i>
                        العملة
                    </label>
                    <select name="currency" class="form-control select2">
                        <option value="">جميع العملات</option>
                        @foreach($currencies as $currency)
                        <option value="{{ $currency }}" {{ request('currency') == $currency ? 'selected' : '' }}>
                            {{ $currency }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">
                        <i class="fas fa-tag text-warning me-1"></i>
                        نوع العملية
                    </label>
                    <select name="type" class="form-control select2">
                        <option value="">جميع الأنواع</option>
                        <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>💰 إيداع</option>
                        <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>💸 سحب</option>
                        <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>💱 تحويل</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>📋 أخرى</option>
                    </select>
                </div>

                <!-- Search and Actions -->
                <div class="col-md-2 d-flex align-items-end">
                    <div class="w-100">
                        <button type="submit" class="btn btn-primary w-100 mb-1">
                            <i class="fas fa-search me-1"></i> بحث
                        </button>
                        <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-refresh me-1"></i> إعادة تعيين
                        </a>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div class="collapse mt-3" id="advancedFilters">
                <hr>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-bookmark text-success me-1"></i>
                            التصنيف
                        </label>
                        <input type="text" name="category" class="form-control" 
                               placeholder="ابحث في التصنيفات..."
                               value="{{ request('category') }}"
                               list="categoriesList">
                        <datalist id="categoriesList">
                            @foreach($categories as $category)
                            <option value="{{ $category }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-user text-info me-1"></i>
                            من/إلى
                        </label>
                        <input type="text" name="from_to" class="form-control" 
                               placeholder="ابحث في الأشخاص..."
                               value="{{ request('from_to') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            <i class="fas fa-money-bill text-warning me-1"></i>
                            نطاق المبلغ
                        </label>
                        <div class="input-group">
                            <input type="number" name="min_amount" class="form-control" 
                                   placeholder="الحد الأدنى" value="{{ request('min_amount') }}">
                            <span class="input-group-text">-</span>
                            <input type="number" name="max_amount" class="form-control" 
                                   placeholder="الحد الأقصى" value="{{ request('max_amount') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Filters Toggle -->
            <div class="text-center mt-3">
                <button class="btn btn-outline-info btn-sm" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                    <i class="fas fa-cog me-1"></i>
                    فلاتر متقدمة
                    <i class="fas fa-chevron-down ms-1"></i>
                </button>
            </div>
        </form>

        <!-- Quick Filters -->
        <div class="mt-3">
            <small class="text-muted fw-bold">فلاتر سريعة:</small>
            <div class="btn-group mt-1" role="group">
                <a href="{{ route('admin.transactions.index', ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" 
                   class="btn btn-outline-primary btn-sm">
                    📅 هذا الشهر
                </a>
                <a href="{{ route('admin.transactions.index', ['start_date' => now()->subDays(7)->format('Y-m-d')]) }}" 
                   class="btn btn-outline-success btn-sm">
                    📊 آخر 7 أيام
                </a>
                <a href="{{ route('admin.transactions.index', ['type' => 'deposit']) }}" 
                   class="btn btn-outline-success btn-sm">
                    💰 الإيداعات فقط
                </a>
                <a href="{{ route('admin.transactions.index', ['type' => 'withdrawal']) }}" 
                   class="btn btn-outline-danger btn-sm">
                    💸 السحوبات فقط
                </a>
            </div>
        </div>
    </div>
</div>