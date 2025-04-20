@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>إضافة حجز جديد</h1>
        <form action="{{ route('bookings.store') }}" method="POST">
            @csrf
            {{-- csrf اهميته هنا :  
        
         --}}
            <div class="mb-3">
                <label for="client_name" class="form-label">اسم العميل</label>
                <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name"
                    name="client_name" value="{{ old('client_name') }}" required>
                @error('client_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="company_id" class="form-label">اسم الشركة</label>
                <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id"
                    required>
                    <option value="" disabled selected>اختر الشركة</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="agent_id" class="form-label">جهة الحجز</label>
                <select class="form-control" id="agent_id" name="agent_id" required>
                    <option value="" disabled selected>اختر جهة الحجز</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ isset($booking) && $agent->id == $booking->agent_id ? 'selected' : '' }}{{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="hotel_id" class="form-label">اسم الفندق</label>
                <select class="form-control" id="hotel_id" name="hotel_id" required>
                    <option value="" disabled selected>اختر الفندق</option>
                    @foreach ($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                            {{ isset($booking) && $hotel->id == $booking->hotel_id ? 'selected' : '' }}{{ $hotel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="room_type" class="form-label">نوع الغرفة</label>
                <select class="form-control @error('room_type') is-invalid @enderror" id="room_type" name="room_type"
                    required>
                    <option value="" disabled selected>اختر نوع الغرفة</option>
                    <option value="رباعي" {{ old('room_type') == 'رباعي' ? 'selected' : '' }}>رباعي</option>
                    <option value="خماسي" {{ old('room_type') == 'خماسي' ? 'selected' : '' }}>خماسي</option>
                </select>
                @error('room_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                @php
                    $today = \Carbon\Carbon::today()->format('Y-m-d');
                @endphp
                <label for="check_in" class="form-label">تاريخ الدخول</label>
                <input type="date" class="form-control @error('check_in') is-invalid @enderror" id="check_in"
                    name="check_in" value="{{ old('check_in') }}"
                    @if (!auth()->user() || strtolower(auth()->user()->role) !== 'admin') min="{{ $today }}" onkeydown="return false" @endif required>
                @error('check_in')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="check_out" class="form-label">تاريخ الخروج</label>
                <input type="date" class="form-control @error('check_out') is-invalid @enderror" id="check_out"
                    name="check_out" value="{{ old('check_out') }}" required>
                @error('check_out')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="rooms" class="form-label">عدد الغرف</label>
                <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="rooms"
                    name="rooms" value="{{ old('rooms') }}" required>
                @error('rooms')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="cost_price" class="form-label">السعر من الفندق</label>
                <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror"
                    id="cost_price" name="cost_price" value="{{ old('cost_price') }}" required>
                @error('cost_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="sale_price" class="form-label">سعر البيع للشركة</label>
                <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                    id="sale_price" name="sale_price" value="{{ old('sale_price') }}" required>
                @error('sale_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="employee_id" class="form-label">الموظف المسؤول</label>
                <select class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id"
                    required>
                    <option value="" disabled selected>اختر الموظف</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">الملاحظات</label>
                <textarea class="form-control" id="notes" name="notes">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">إضافة الحجز</button>
        </form>
    </div>
@endsection
