<x-app-layout>
<div style="max-width:1200px;margin:40px auto;padding:20px;font-family:Arial;" dir="rtl">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2>🗑️ الرحلات المحذوفة</h2>
        <a href="{{ route('trips.index') }}"
           style="background:#6b7280;color:white;padding:8px 16px;border-radius:6px;text-decoration:none;">
            ↩️ رجوع
        </a>
    </div>

    <table style="width:100%;border-collapse:collapse;background:white;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,.1);">
        <thead style="background:#7c3aed;color:white;">
            <tr>
                <th style="padding:12px;">#</th>
                <th style="padding:12px;">اسم الرحلة</th>
                <th style="padding:12px;">من</th>
                <th style="padding:12px;">إلى</th>
                <th style="padding:12px;">تاريخ الحذف</th>
                 @if(auth()->user()->hasRole('admin'))
                <th style="padding:12px;">استعادة</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($trips as $trip)
            <tr style="border-bottom:1px solid #f0f0f0;text-align:center;">
                <td style="padding:12px;">{{ $loop->iteration }}</td>
                <td style="padding:12px;font-weight:bold;">{{ $trip->name }}</td>
                <td style="padding:12px;">{{ $trip->from }}</td>
                <td style="padding:12px;">{{ $trip->to }}</td>
                <td style="padding:12px;">{{ $trip->deleted_at->format('Y-m-d') }}</td>
                 @if(auth()->user()->hasRole('admin'))
                <td style="padding:12px;">
                    <form method="POST" action="{{ route('trips.restore', $trip->id) }}">
                        @csrf
                        <button type="submit"
                            style="background:#16a34a;color:white;padding:6px 12px;border:none;border-radius:4px;cursor:pointer;">
                            ♻️ استعادة
                        </button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr><td colspan="6" style="padding:20px;text-align:center;color:#999;">لا توجد رحلات محذوفة</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;">{{ $trips->links() }}</div>
</div>
</x-app-layout>