<x-app-layout>
    @php
    $roomTypeArabic = [
        'single'    => 'فردية',
        'double'    => 'ثنائية',
        'triple'    => 'ثلاثية',
        'quad'      => 'رباعية',
        'quintuple' => 'خماسية',
        'sextuple'  => 'سداسية',
    ];
@endphp
<div style="min-height:100vh;background:var(--color-background-tertiary, #f5f4f0);font-family:'Segoe UI',Tahoma,sans-serif;" dir="rtl">

    {{-- ===== شريط العنوان ===== --}}
    <div style="background:white;border-bottom:0.5px solid #e5e7eb;padding:14px 24px;">
        <div style="max-width:1400px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <div>
                <h2 style="margin:0;font-size:18px;font-weight:600;color:#111827;">
                    🛏️ إدارة التسكين
                </h2>
                <p style="color:#6b7280;margin:3px 0 0;font-size:13px;">
                    رحلة: <strong>{{ $trip->name }}</strong>
                    ({{ $trip->from }} ← {{ $trip->to }})
                </p>
            </div>
            <div style="display:flex;gap:10px;">
                <button onclick="openAddRoomModal()"
                    style="background:#7c3aed;color:white;padding:9px 18px;
                           border:none;border-radius:8px;cursor:pointer;
                           font-size:14px;font-weight:500;">
                    ➕ إضافة غرفة
                </button>
                <a href="{{ route('trips.show', $trip) }}"
                   style="background:#6b7280;color:white;padding:9px 16px;
                          border-radius:8px;text-decoration:none;font-size:14px;">
                    ← رجوع للرحلة
                </a>
            </div>
        </div>
    </div>

    {{-- ===== إشعارات ===== --}}
    @if(session('success') || $errors->any())
    <div style="max-width:1400px;margin:12px auto;padding:0 24px;">
        @if(session('success'))
        <div style="background:#d1fae5;color:#065f46;padding:10px 14px;border-radius:8px;font-size:14px;">
            {{ session('success') }}
        </div>
        @endif
        @if($errors->any())
        <div style="background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:8px;font-size:14px;">
            @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
        </div>
        @endif
    </div>
    @endif

    <div style="max-width:1400px;margin:0 auto;padding:20px 24px;">

        {{-- ===== بطاقات الإحصائيات ===== --}}
        @php
            $totalRooms     = $rooms->count();
            $totalCapacity  = $rooms->sum('capacity');
            $assignedCount  = $rooms->sum(fn($r) => $r->bookings->count());
            $unassignedCount = $unassigned->count();
            $fullRooms      = $rooms->filter(fn($r) => $r->availableSpots() <= 0)->count();
        @endphp
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:24px;">
            <div style="background:white;border-radius:10px;padding:14px 16px;border:0.5px solid #e5e7eb;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#7c3aed;">{{ $totalRooms }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">عدد الغرف</div>
            </div>
            <div style="background:white;border-radius:10px;padding:14px 16px;border:0.5px solid #e5e7eb;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#2563eb;">{{ $totalCapacity }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">إجمالي الطاقة</div>
            </div>
            <div style="background:white;border-radius:10px;padding:14px 16px;border:0.5px solid #e5e7eb;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#059669;">{{ $assignedCount }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">متسكنين</div>
            </div>
            <div style="background:white;border-radius:10px;padding:14px 16px;border:0.5px solid #e5e7eb;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#dc2626;">{{ $unassignedCount }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">غير متسكنين</div>
            </div>
            <div style="background:white;border-radius:10px;padding:14px 16px;border:0.5px solid #e5e7eb;text-align:center;">
                <div style="font-size:22px;font-weight:700;color:#ef4444;">{{ $fullRooms }}</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px;">غرف ممتلئة</div>
            </div>
        </div>

        {{-- ===== الغرف (عرض كامل) ===== --}}
        @if($rooms->isEmpty())
        <div style="background:white;border-radius:10px;padding:50px;
                    text-align:center;color:#9ca3af;border:0.5px solid #e5e7eb;
                    margin-bottom:24px;">
            لا توجد غرف بعد — اضغط «إضافة غرفة» من الأعلى
        </div>
        @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;margin-bottom:28px;">
            @foreach($rooms as $room)
            @php
                $isFull    = $room->availableSpots() <= 0;
                $genders   = $room->bookings->pluck('gender')->unique();
                $hasMixed  = $genders->contains('male') && $genders->contains('female');
                $fillPct   = $room->capacity > 0 ? round(($room->bookings->count() / $room->capacity) * 100) : 0;
            @endphp
            <div style="background:white;border-radius:10px;
                        border:0.5px solid #e5e7eb;overflow:hidden;
                        border-top:3px solid {{ $isFull ? '#ef4444' : '#059669' }};">

                {{-- رأس الغرفة --}}
                <div style="padding:10px 14px;background:{{ $isFull ? '#fef2f2' : '#f0fdf4' }};
                            border-bottom:0.5px solid #e5e7eb;
                            display:flex;justify-content:space-between;align-items:center;">
                    <div style="flex:1;">
                        <form method="POST"
                              action="{{ route('room-assignments.update-room', $room) }}"
                              style="display:inline;">
                            @csrf @method('PATCH')
                            <input type="text" name="room_number"
                                   value="{{ $room->room_number }}"
                                   style="font-weight:600;font-size:15px;
                                          border:none;background:transparent;
                                          width:70px;border-bottom:1px dashed #9ca3af;
                                          outline:none;color:#111827;"
                                   onchange="this.form.submit()">
                        </form>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                            {{ $room->bookings->count() }}/{{ $room->capacity }}
                            {{ $roomTypeArabic[$room->room_type] ?? $room->room_type }}
                            @if($hasMixed)
                            <span style="color:#f59e0b;margin-right:4px;">👨‍👩 أسرة</span>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        {{-- شريط التعبئة --}}
                        <div style="display:flex;gap:2px;">
                            @for($i = 0; $i < $room->capacity; $i++)
                            <div style="width:10px;height:10px;border-radius:50%;
                                        background:{{ $i < $room->bookings->count() ? '#2563eb' : '#e5e7eb' }};"></div>
                            @endfor
                        </div>
                        {{-- حذف --}}
                        <form method="POST"
                              action="{{ route('room-assignments.delete-room', $room) }}"
                              onsubmit="return confirm('حذف الغرفة وإلغاء تسكين من فيها؟')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="background:none;border:none;color:#ef4444;
                                       cursor:pointer;font-size:15px;padding:2px;">🗑️</button>
                        </form>
                    </div>
                </div>

                {{-- محتوى الغرفة --}}
                <div style="padding:10px 12px;">
                    @forelse($room->bookings as $b)
                    <div style="display:flex;justify-content:space-between;align-items:center;
                                background:#f8fafc;border-radius:6px;padding:6px 10px;
                                margin-bottom:5px;font-size:13px;border:0.5px solid #e5e7eb;">
                        <span>
                            @switch($b->gender)
                                @case('male')   👨 @break
                                @case('female') 👩 @break
                                @case('child')  👦 @break
                                @case('infant') 👶 @break
                            @endswitch
                            {{ $b->client_name }}
                            @if($b->is_family)
                            <span style="font-size:10px;background:#fde68a;color:#92400e;
                                         padding:1px 5px;border-radius:10px;margin-right:3px;">أسرة</span>
                            @endif
                        </span>
                        <form method="POST" action="{{ route('room-assignments.unassign', $b) }}">
                            @csrf @method('PATCH')
                            <button type="submit" title="إلغاء التسكين"
                                style="background:none;border:none;color:#ef4444;
                                       cursor:pointer;font-size:13px;padding:0 2px;">✕</button>
                        </form>
                    </div>
                    @empty
                    <div style="text-align:center;color:#9ca3af;font-size:12px;padding:8px;">فارغة</div>
                    @endforelse

                    @if($isFull)
                    <div style="text-align:center;background:#fee2e2;color:#dc2626;
                                border-radius:6px;padding:5px;font-size:11px;margin-top:4px;">
                        🔴 ممتلئة
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- ===== قسم غير المتسكنين (أسفل — عرض كامل) ===== --}}
        <div style="background:white;border-radius:10px;border:0.5px solid #e5e7eb;overflow:hidden;">
            <div style="background:#dc2626;color:white;padding:12px 18px;
                        display:flex;justify-content:space-between;align-items:center;">
                <span style="font-weight:600;font-size:15px;">⚠️ غير متسكنين</span>
                <span style="background:white;color:#dc2626;border-radius:20px;
                             padding:2px 10px;font-weight:700;font-size:14px;">
                    {{ $unassigned->count() }}
                </span>
            </div>

            @if($unassigned->isEmpty())
            <div style="text-align:center;padding:30px;color:#6b7280;font-size:15px;">
                🎉 الكل اتسكن!
            </div>
            @else
            <div style="padding:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
               
                @foreach($unassigned as $booking)
                @php
                    $isChildOrInfant = in_array($booking->accommodation_type, ['طفل', 'رضيع']);
                @endphp
                <div style="background:#fef2f2;border:1px solid #fecaca;
                            border-radius:8px;padding:12px;">
                    <div style="font-weight:600;margin-bottom:4px;font-size:14px;">
                        @switch($booking->gender)
                            @case('male')   👨 @break
                            @case('female') 👩 @break
                            @case('child')  👦 @break
                            @case('infant') 👶 @break
                        @endswitch
                        {{ $booking->client_name }}
                    </div>
                    <div style="font-size:12px;color:#6b7280;margin-bottom:8px;">
                        🛏️ {{ $booking->accommodation_type }}
                    </div>
                    <div style="display:flex;gap:6px;">
                        <select id="room_select_{{ $booking->id }}"
                                data-accommodation="{{ $booking->accommodation_type }}"
                                style="flex:1;padding:7px;border:1px solid #ddd;
                                       border-radius:6px;font-size:12px;background:white;">
                            <option value="">-- اختر غرفة --</option>
                            @foreach($rooms as $room)
                               @if($room->availableSpots() > 0 && ($isChildOrInfant || $room->room_type === $booking->accommodation_type))
                                <option value="{{ $room->id }}">
                                    غرفة {{ $room->room_number }} ({{ $room->room_type }}) - فاضل {{ $room->availableSpots() }}
                                </option>
                            @endif
                            @endforeach
                        </select>
                        <button onclick="assignPerson({{ $booking->id }}, '{{ $booking->accommodation_type }}')"
                            style="background:#059669;color:white;padding:7px 12px;
                                   border:none;border-radius:6px;cursor:pointer;
                                   font-size:12px;white-space:nowrap;">
                            ✅ سكّن
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>

{{-- ===== Modal إضافة غرفة ===== --}}
<div id="addRoomModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            z-index:2000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:28px;
                width:360px;max-width:90%;box-shadow:0 20px 40px rgba(0,0,0,.2);" dir="rtl">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;color:#7c3aed;font-size:17px;">➕ إضافة غرفة جديدة</h3>
            <button onclick="closeAddRoomModal()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#6b7280;">✕</button>
        </div>

        <form method="POST" action="{{ route('room-assignments.add-room', $trip) }}">
            @csrf
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;color:#374151;margin-bottom:5px;font-weight:500;">
                    رقم الغرفة
                </label>
                <input type="text" name="room_number" placeholder="مثلاً: 101" required
                       style="width:100%;padding:9px 10px;border:1px solid #d1d5db;
                              border-radius:7px;box-sizing:border-box;font-size:14px;">
            </div>

            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:13px;color:#374151;margin-bottom:5px;font-weight:500;">
                    نوع الغرفة
                </label>
                <select name="room_type" id="roomTypeSelect" onchange="updateCapacity(this)"
                        style="width:100%;padding:9px 10px;border:1px solid #d1d5db;
                               border-radius:7px;font-size:14px;background:white;">
                    <option value="single">فردية </option>
                    <option value="double">ثنائية </option>
                    <option value="triple">ثلاثية </option>
                    <option value="quad">رباعية </option>
                    <option value="quintuple">خماسية </option>
                    <option value="sextuple">سداسية </option>
                </select>
            </div>

            <div style="margin-bottom:20px;background:#f3f4f6;border-radius:8px;padding:10px 14px;
                        display:flex;justify-content:space-between;align-items:center;font-size:14px;">
                <span style="color:#6b7280;">السعة المحددة تلقائياً:</span>
                <span id="capacityDisplay" style="font-weight:700;color:#7c3aed;font-size:16px;">2</span>
                <input type="hidden" name="capacity" id="capacityInput" value="2">
            </div>

            <button type="submit"
                style="width:100%;background:#7c3aed;color:white;padding:11px;
                       border:none;border-radius:8px;cursor:pointer;font-size:15px;font-weight:500;">
                ➕ إضافة الغرفة
            </button>
        </form>
    </div>
</div>

{{-- ===== Modal تحذير الجنس المختلط ===== --}}
<div id="familyModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            z-index:3000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:30px;
                max-width:380px;width:90%;text-align:center;" dir="rtl">
        <div style="font-size:40px;margin-bottom:14px;">⚠️</div>
        <h3 style="margin:0 0 10px;color:#92400e;font-size:17px;">تحذير — أنواع مختلفة</h3>
        <p style="color:#6b7280;margin-bottom:20px;font-size:14px;">
            أنت بتضيف أنواع مختلفة مع بعض في نفس الغرفة.
            <br><strong>هل دول أسرة؟</strong>
        </p>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button onclick="confirmAssign(true)"
                style="background:#059669;color:white;padding:10px 22px;
                       border:none;border-radius:8px;cursor:pointer;font-size:14px;">
                ✅ نعم، أسرة
            </button>
            <button onclick="closeModal()"
                style="background:#6b7280;color:white;padding:10px 22px;
                       border:none;border-radius:8px;cursor:pointer;font-size:14px;">
                ❌ إلغاء
            </button>
        </div>
    </div>
</div>

{{-- ===== Modal تحذير نوع الغرفة غير متطابق ===== --}}
<div id="typeWarningModal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
            z-index:3000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:30px;
                max-width:380px;width:90%;text-align:center;" dir="rtl">
        <div style="font-size:40px;margin-bottom:14px;">🚫</div>
        <h3 style="margin:0 0 10px;color:#dc2626;font-size:17px;">نوع الغرفة غير متطابق</h3>
        <p id="typeWarningMsg" style="color:#6b7280;margin-bottom:20px;font-size:14px;"></p>
        <button onclick="closeTypeWarning()"
            style="background:#dc2626;color:white;padding:10px 28px;
                   border:none;border-radius:8px;cursor:pointer;font-size:14px;">
            حسناً
        </button>
    </div>
</div>

<script>
const capacityMap = {
    single:    1,
    double:    2,
    triple:    3,
    quad:      4,
    quintuple: 5,
    sextuple:  6,
};

const roomTypeLabels = {
    single:    'فردية',
    double:    'ثنائية',
    triple:    'ثلاثية',
    quad:      'رباعية',
    quintuple: 'خماسية',
    sextuple:  'سداسية',
};

function updateCapacity(sel) {
    const cap = capacityMap[sel.value] ?? 2;
    document.getElementById('capacityDisplay').textContent = cap;
    document.getElementById('capacityInput').value = cap;
}

function openAddRoomModal() {
    document.getElementById('addRoomModal').style.display = 'flex';
}

function closeAddRoomModal() {
    document.getElementById('addRoomModal').style.display = 'none';
}

let pendingBookingId  = null;
let pendingRoomId     = null;

function assignPerson(bookingId, accommodationType) {
    const sel    = document.getElementById('room_select_' + bookingId);
    const roomId = sel.value;

    if (!roomId) {
        const typeLabel = roomTypeLabels[accommodationType] || accommodationType;
        document.getElementById('typeWarningMsg').innerHTML =
            'اختر غرفة الأول!<br>ملاحظة: بتظهر بس الغرف من نوع <strong>' + typeLabel + '</strong> المناسبة لهذا الحجز.';
        document.getElementById('typeWarningModal').style.display = 'flex';
        return;
    }

    pendingBookingId = bookingId;
    pendingRoomId    = roomId;
    doAssign(false);
}

async function doAssign(isFamily) {
    const res = await fetch('{{ route("room-assignments.assign") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            booking_id: pendingBookingId,
            room_id:    pendingRoomId,
            is_family:  isFamily
        })
    });

    const data = await res.json();

    if (data.warning) {
        document.getElementById('familyModal').style.display = 'flex';
    } else if (data.type_mismatch) {
        document.getElementById('typeWarningMsg').innerHTML = data.message || 'نوع الغرفة لا يتطابق مع نوع الحجز.';
        document.getElementById('typeWarningModal').style.display = 'flex';
    } else if (data.success) {
        window.location.reload();
    } else {
        alert(data.error || 'حدث خطأ');
    }
}

function confirmAssign(isFamily) {
    closeModal();
    doAssign(isFamily);
}

function closeModal() {
    document.getElementById('familyModal').style.display = 'none';
}

function closeTypeWarning() {
    document.getElementById('typeWarningModal').style.display = 'none';
}

// إغلاق modals بالضغط خارجها
document.addEventListener('click', function(e) {
    const addModal  = document.getElementById('addRoomModal');
    const typeModal = document.getElementById('typeWarningModal');
    if (e.target === addModal)  closeAddRoomModal();
    if (e.target === typeModal) closeTypeWarning();
});
</script>
</x-app-layout>