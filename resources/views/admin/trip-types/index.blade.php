@extends('layouts.app')

@section('title', 'إدارة أنواع الرحلات')

@push('styles')
<style>
    .trip-type-list {
        max-height: 600px;
        overflow-y: auto;
    }
    
    .trip-type-card {
        transition: all 0.3s;
    }
    
    .trip-type-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .trip-type-form {
        position: sticky;
        top: 1rem;
    }
    
    .trips-count {
        font-size: 0.875rem;
    }
    
    @media (max-width: 991.98px) {
        .trip-type-form {
            position: static;
            margin-bottom: 2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4" x-data="tripTypesManager()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-tags me-2 text-primary"></i> إدارة أنواع الرحلات</h1>
        <a href="{{ route('admin.land-trips.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> العودة للرحلات
        </a>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <!-- نموذج إضافة/تعديل نوع رحلة -->
        <div class="col-lg-4">
            <div class="card shadow-sm trip-type-form">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0" x-text="formMode === 'create' ? 'إضافة نوع رحلة جديد' : 'تعديل نوع رحلة'"></h5>
                </div>
                <div class="card-body">
                    <form :action="formAction" method="POST" id="tripTypeForm">
                        @csrf
                        <template x-if="formMode === 'edit'">
                            @method('PUT')
                        </template>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">اسم نوع الرحلة <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" x-model="typeData.name" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">وصف النوع</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" x-model="typeData.description"></textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="color" class="form-label">اللون (اختياري)</label>
                            <input type="color" class="form-control form-control-color w-100 @error('color') is-invalid @enderror" 
                                   id="color" name="color" x-model="typeData.color">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="icon" class="form-label">الأيقونة (اختياري)</label>
                            <select class="form-select @error('icon') is-invalid @enderror" id="icon" name="icon" x-model="typeData.icon">
                                <option value="">بدون أيقونة</option>
                                <option value="bus">حافلة</option>
                                <option value="car">سيارة</option>
                                <option value="plane">طائرة</option>
                                <option value="train">قطار</option>
                                <option value="ship">سفينة</option>
                                <option value="hiking">مشي</option>
                                <option value="campground">تخييم</option>
                                <option value="mountain">جبال</option>
                                <option value="umbrella-beach">شاطئ</option>
                                <option value="landmark">معالم</option>
                            </select>
                            <div class="mt-2" x-show="typeData.icon">
                                <i :class="`fas fa-${typeData.icon} fa-2x text-primary`"></i>
                                <span class="ms-2">معاينة الأيقونة</span>
                            </div>
                            @error('icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="is_active" class="form-label d-block">الحالة</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" x-model="typeData.is_active">
                                <label class="form-check-label" for="is_active" x-text="typeData.is_active ? 'نشط' : 'غير نشط'"></label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas" :class="formMode === 'create' ? 'fa-plus-circle' : 'fa-save'"></i>
                                <span x-text="formMode === 'create' ? 'إضافة' : 'حفظ التعديلات'"></span>
                            </button>
                            <button type="button" class="btn btn-secondary" @click="resetForm" x-show="formMode === 'edit'">
                                <i class="fas fa-times"></i> إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- قائمة أنواع الرحلات -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0">قائمة أنواع الرحلات</h5>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="text" class="form-control form-control-sm" placeholder="بحث عن نوع..." 
                               x-model="searchTerm" @input="filterTripTypes">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="trip-type-list">
                        <template x-if="filteredTypes.length === 0">
                            <div class="text-center py-5">
                                <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                                <h5>لا توجد أنواع رحلات</h5>
                                <p class="text-muted">قم بإضافة نوع جديد من النموذج المجاور</p>
                            </div>
                        </template>
                        
                        <div class="list-group list-group-flush">
                            <template x-for="type in filteredTypes" :key="type.id">
                                <div class="list-group-item list-group-item-action trip-type-card">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3" x-show="type.icon">
                                                <i class="fas" :class="`fa-${type.icon}`" :style="`color: ${type.color || '#0d6efd'}`"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0" x-text="type.name"></h6>
                                                <p class="text-muted mb-0 small" x-text="type.description || 'بدون وصف'"></p>
                                                <span class="badge" :class="type.is_active ? 'bg-success' : 'bg-warning text-dark'">
                                                    <span x-text="type.is_active ? 'نشط' : 'غير نشط'"></span>
                                                </span>
                                                <span class="badge bg-info ms-1 trips-count">
                                                    <span x-text="`${type.trips_count || 0} رحلة`"></span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-warning" @click="editType(type)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger" @click="confirmDelete(type)" 
                                                    :disabled="type.trips_count > 0">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- مودال تأكيد الحذف -->
    <div class="modal fade" id="deleteTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تأكيد الحذف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>هل أنت متأكد من حذف نوع الرحلة "<span x-text="selectedType.name"></span>"؟</p>
                    <p class="text-danger">هذا الإجراء لا يمكن التراجع عنه.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <form :action="`{{ route('admin.trip-types.index') }}/${selectedType.id}`" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">نعم، حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function tripTypesManager() {
        return {
            tripTypes: @json($tripTypes ?? []),
            formMode: 'create',
            formAction: "{{ route('admin.trip-types.store') }}",
            typeData: {
                id: null,
                name: '',
                description: '',
                color: '#0d6efd',
                icon: '',
                is_active: true
            },
            searchTerm: '',
            selectedType: {},
            deleteModal: null,
            
            get filteredTypes() {
                if (!this.searchTerm.trim()) {
                    return this.tripTypes;
                }
                
                const term = this.searchTerm.toLowerCase();
                return this.tripTypes.filter(type => 
                    type.name.toLowerCase().includes(term) || 
                    (type.description && type.description.toLowerCase().includes(term))
                );
            },
            
            init() {
                this.deleteModal = new bootstrap.Modal(document.getElementById('deleteTypeModal'));
            },
            
            filterTripTypes() {
                // فلترة البيانات تلقائيًا بسبب الخاصية المحسوبة filteredTypes
            },
            
            editType(type) {
                this.formMode = 'edit';
                this.formAction = `{{ url('trip-types') }}/${type.id}`;
                this.typeData = {
                    id: type.id,
                    name: type.name,
                    description: type.description || '',
                    color: type.color || '#0d6efd',
                    icon: type.icon || '',
                    is_active: type.is_active
                };
                
                // انتقل إلى النموذج في الشاشات الصغيرة
                if (window.innerWidth < 992) {
                    document.querySelector('.trip-type-form').scrollIntoView({ behavior: 'smooth' });
                }
            },
            
            resetForm() {
                this.formMode = 'create';
                this.formAction = "{{ route('admin.trip-types.store') }}";
                this.typeData = {
                    id: null,
                    name: '',
                    description: '',
                    color: '#0d6efd',
                    icon: '',
                    is_active: true
                };
            },
            
            confirmDelete(type) {
                if (type.trips_count > 0) {
                    alert('لا يمكن حذف هذا النوع لأنه مرتبط برحلات.');
                    return;
                }
                
                this.selectedType = type;
                this.deleteModal.show();
            }
        }
    }
</script>
@endpush