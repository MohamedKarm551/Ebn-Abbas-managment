{{-- filepath: c:\xampp\htdocs\Ebn-Abbas-managment\resources\views\admin\availabilities\_form.blade.php --}}
{{-- Shared form for creating and editing availabilities --}}
{{-- Requires Alpine.js. Assumes Select2 and DatePicker are initialized globally in app.blade.php --}}

@props([
    'availability' => null, // Current availability model (for editing) or null (for creating)
    'hotels',               // Collection of all hotels
    'agents',               // Collection of all agents
    'employees',            // Collection of all employees
    'roomTypes',            // Collection of all RoomType models (id, room_type_name)
])

{{-- Alpine.js component for dynamic room types --}}
<div x-data="availabilityForm({
        initialRoomTypes: {{ $availability ? json_encode($availability->availabilityRoomTypes->map(fn($art) => ['id' => $art->id, 'room_type_id' => $art->room_type_id, 'cost_price' => $art->cost_price, 'sale_price' => $art->sale_price, 'allotment' => $art->allotment])) : '[]' }},
        allRoomTypes: {{ json_encode($roomTypes->pluck('room_type_name', 'id')) }} // Pass all possible room types {id: name}
     })"
     x-init="init()" {{-- Initialize Alpine component --}}
     class="p-3 border rounded">

    {{-- Main Availability Fields --}}
    <div class="row g-3">
        <div class="col-md-4">
            <label for="hotel_id" class="form-label">الفندق <span class="text-danger">*</span></label>
            <select name="hotel_id" id="hotel_id" class="form-select select2 @error('hotel_id') is-invalid @enderror" required data-placeholder="اختر الفندق...">
                {{-- <option value="">اختر الفندق...</option> --}} {{-- Placeholder handled by Select2 --}}
                @foreach ($hotels as $hotel)
                    <option value="{{ $hotel->id }}"
                        {{ old('hotel_id', $availability?->hotel_id) == $hotel->id ? 'selected' : '' }}>
                        {{ $hotel->name }} {{ $hotel->location ? "({$hotel->location})" : '' }}
                    </option>
                @endforeach
            </select>
            @error('hotel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="agent_id" class="form-label">جهة الحجز (الوكيل) <span class="text-danger">*</span></label>
            <select name="agent_id" id="agent_id" class="form-select select2 @error('agent_id') is-invalid @enderror" required data-placeholder="اختر جهة الحجز...">
                {{-- <option value="">اختر جهة الحجز...</option> --}}
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}"
                        {{ old('agent_id', $availability?->agent_id) == $agent->id ? 'selected' : '' }}>
                        {{ $agent->name }}
                    </option>
                @endforeach
            </select>
            @error('agent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="employee_id" class="form-label">الموظف المسؤول <span class="text-danger">*</span></label>
            <select name="employee_id" id="employee_id" class="form-select select2 @error('employee_id') is-invalid @enderror" required data-placeholder="اختر الموظف...">
                {{-- <option value="">اختر الموظف...</option> --}}
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}"
                        {{-- Default to logged-in user ONLY on create, otherwise use existing or old value --}}
                        {{ old('employee_id', $availability?->employee_id ?? ($availability === null ? auth()->id() : null)) == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
            @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="start_date" class="form-label">تاريخ البداية <span class="text-danger">*</span></label>
            <input type="text" name="start_date" id="start_date"
            class="form-control datepicker @error('start_date') is-invalid @enderror"
            value="{{ old('start_date', $availability?->start_date?->format('Y-m-d')) }}" required data-date-format="dd/mm/yyyy"

                placeholder="dd/mm/yyyy" autocomplete="off"> {{-- Use text type for datepicker --}}
            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="end_date" class="form-label">تاريخ النهاية <span class="text-danger">*</span></label>
            <input type="text" name="end_date" id="end_date"
            class="form-control datepicker @error('end_date') is-invalid @enderror"
            value="{{ old('end_date', $availability?->end_date?->format('Y-m-d')) }}" required data-date-format="dd/mm/yyyy"
                placeholder="dd/mm/yyyy" autocomplete="off"> {{-- Use text type for datepicker --}}
            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-4">
            <label for="status" class="form-label">الحالة <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', $availability?->status) == 'active' ? 'selected' : '' }}>نشطة</option>
                <option value="inactive" {{ old('status', $availability?->status) == 'inactive' ? 'selected' : '' }}>غير نشطة</option>
                {{-- Show 'expired' only if it's the current status (cannot set it manually) --}}
                @if ($availability?->status == 'expired')
                    <option value="expired" selected>منتهية (لا يمكن تغييرها)</option>
                @endif
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label for="notes" class="form-label">ملاحظات داخلية (اختياري)</label>
            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $availability?->notes) }}</textarea>
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <hr class="my-4">

    {{-- Dynamic Room Types Section --}}
    <h5 class="mb-3">أنواع الغرف والأسعار لهذه الإتاحة</h5>
    {{-- General error for room_types array --}}
    @error('room_types') <div class="alert alert-danger">{{ $message }}</div> @enderror
    {{-- Error for specific fields within room_types --}}
    @error('room_types.*') <div class="alert alert-danger">يرجى مراجعة بيانات أسعار الغرف المدخلة والتأكد من عدم تكرار نوع الغرفة.</div> @enderror

    <div id="room-types-container" class="mb-3">
        {{-- Alpine template for dynamic rows --}}
        <template x-for="(room, index) in roomTypes" :key="index">
            <div class="row g-3 align-items-center border-bottom pb-3 mb-3">
                {{-- Hidden input for existing AvailabilityRoomType ID (for updates) --}}
                <input type="hidden" :name="'room_types[' + index + '][id]'" :value="room.id">

                <div class="col-md-3">
                    <label :for="'room_type_id_' + index" class="form-label">نوع الغرفة <span class="text-danger">*</span></label>
                    {{-- Select populated by Alpine using allRoomTypes --}}
                    <select class="form-select" :name="'room_types[' + index + '][room_type_id]'"
                            x-model.number="room.room_type_id" required @change="checkDuplicate(index)">
                        <option value="" disabled>اختر نوع الغرفة</option>
                        <template x-for="(name, id) in allRoomTypes" :key="id">
                            <option :value="id" x-text="name"></option>
                        </template>
                    </select>
                    <small class="text-danger" x-show="room.duplicate" x-cloak>تم اختيار هذا النوع مسبقاً!</small>
                </div>

                <div class="col-md-2">
                    <label :for="'cost_price_' + index" class="form-label">سعر التكلفة <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control"
                        :name="'room_types[' + index + '][cost_price]'" x-model.number="room.cost_price"
                        placeholder="0.00" required min="0">
                </div>

                <div class="col-md-2">
                    <label :for="'sale_price_' + index" class="form-label">سعر البيع <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control"
                        :name="'room_types[' + index + '][sale_price]'" x-model.number="room.sale_price"
                        placeholder="0.00" required min="0">
                </div>

                <div class="col-md-2">
                    <label :for="'allotment_' + index" class="form-label">عدد الغرف</label>
                    <input type="number" step="1" min="0"
                        :name="'room_types[' + index + '][allotment]'" :id="'allotment_' + index"
                        class="form-control" placeholder="اختياري" x-model.number="room.allotment">
                </div>

                <div class="col-md-1 d-flex align-items-end"> {{-- Align button to bottom --}}
                    {{-- Delete button - allow deleting last row if needed, controller handles min:1 --}}
                    <button type="button" class="btn btn-danger btn-sm" @click="removeRoomType(index)"
                         title="إزالة هذا النوع">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Add Room Type Button --}}
    <button type="button" class="btn btn-success btn-sm" @click="addRoomType">
        <i class="bi bi-plus-circle"></i> إضافة نوع غرفة آخر
    </button>

</div> {{-- End x-data div --}}

{{-- Alpine.js Logic (Keep this script here, it's specific to this form) --}}
<script>
    function availabilityForm(config) {
        return {
            // Initialize roomTypes from config or empty array
            roomTypes: config.initialRoomTypes.map(rt => ({ ...rt, duplicate: false })) || [],
            // All possible room types {id: name}
            allRoomTypes: config.allRoomTypes || {},

            // Initialize the component
            init() {
                // Add an empty row if creating a new availability
                if (this.roomTypes.length === 0) {
                    this.addRoomType();
                }
                // Watch for changes in roomTypes to validate duplicates
                this.$watch('roomTypes', () => this.validateDuplicates(), { deep: true });
                // Initial validation check on load
                this.validateDuplicates();
            },

            // Add a new empty room type row
            addRoomType() {
                this.roomTypes.push({
                    id: null,           // Null ID for new AvailabilityRoomType
                    room_type_id: '',   // Start with empty selection
                    cost_price: '',
                    sale_price: '',
                    allotment: null,
                    duplicate: false    // Flag for duplicate validation
                });
                // Optional: Reinitialize Select2 for the new row if needed,
                // but Alpine should handle the rendering.
                // this.$nextTick(() => { $('.select2').select2({...}); });
            },

            // Remove a room type row by index
            removeRoomType(index) {
                // Prevent removing the last row if you want to enforce at least one
                // if (this.roomTypes.length <= 1) {
                //     alert("يجب أن يكون هناك نوع غرفة واحد على الأقل.");
                //     return;
                // }
                this.roomTypes.splice(index, 1);
                // Re-validate duplicates after removal
                this.validateDuplicates();
            },

            // Trigger validation when a room type selection changes
            checkDuplicate(selectedIndex) {
                this.validateDuplicates(); // Re-validate all rows for simplicity
            },

            // Validate all rows for duplicate room type selections
            validateDuplicates() {
                const selectedIds = new Set();
                let hasDuplicates = false;
                this.roomTypes.forEach((room, index) => {
                    // Check if the current room's ID exists in the set
                    if (room.room_type_id && selectedIds.has(room.room_type_id)) {
                        this.roomTypes[index].duplicate = true; // Mark as duplicate
                        hasDuplicates = true;
                    } else {
                        this.roomTypes[index].duplicate = false; // Mark as not duplicate
                        // Add the ID to the set if it's valid
                        if (room.room_type_id) {
                            selectedIds.add(room.room_type_id);
                        }
                    }
                });
                // You could potentially disable the submit button if hasDuplicates is true
                // document.getElementById('submitButton').disabled = hasDuplicates;
            }
        }
    }
</script>

{{-- DO NOT ADD any @push, library loading (<script src="...">), or $(document).ready here --}}
{{-- All library loading and global initializations should be in app.blade.php --}}
