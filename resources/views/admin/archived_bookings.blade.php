{{--   هخلي الصفحة دي تعرض بيانات الحجوزات المؤرشفة  واللي هتكون عبارة عن  --}} 
@extends('layouts.app')
@section('content')
<div class="container" dir="rtl">
    <h1><i class="fas fa-archive"></i> أرشيف الحجوزات</h1>
    @if($archivedBookings->isEmpty())
        <div class="alert alert-info">لا توجد حجوزات مؤرشفة.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                {{-- رؤوس الأعمدة --}}
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>العميل</th>
                        <th>سبب الأرشفة</th>
                        <th>تاريخ الأرشفة</th>
                        {{-- أعمدة أخرى زي الفندق، الشركة، التواريخ... --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach($archivedBookings as $key => $booking)
                        <tr>
                            <td>{{ $archivedBookings->firstItem() + $key }}</td>
                            <td>{{ $booking->client_name }}</td>
                            <td>{{ $booking->archived_reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->archived_at)->format('Y-m-d H:i') }}</td>
                           
                        </tr>
                    @endforeach --}}
                </tbody>
            </table>
        </div>
        {{-- روابط الـ Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $archivedBookings->links() }}
        </div>
    @endif
</div>
@endsection