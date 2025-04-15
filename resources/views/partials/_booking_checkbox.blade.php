{{-- filepath: resources/views/partials/_booking_checkbox.blade.php --}}
@props([
    'booking',
    'amountDueField' => 'amount_due_from_company', // الحقل الافتراضي للمبلغ المستحق
    'amountPaidField' => 'amount_paid_by_company', // الحقل الافتراضي للمبلغ المدفوع
    'costPriceField' => 'cost_price', // الحقل الافتراضي لسعر التكلفة
])

<label>
    <input type="checkbox" class="booking-checkbox" data-booking-id="{{ $booking->id }}"
        {{-- استخدام الحقول الديناميكية --}}
        data-amount-due="{{ $booking->{$amountDueField} ?? 0 }}"
        data-amount-paid="{{ $booking->{$amountPaidField} ?? 0 }}"
        data-client-name="{{ $booking->client_name }}"
        data-hotel-name="{{ $booking->hotel->name ?? 'N/A' }}"
        data-check-in="{{ $booking->check_in->format('Y-m-d') }}"
        data-check-out="{{ $booking->check_out->format('Y-m-d') }}"
        data-rooms="{{ $booking->rooms }}"
        data-days="{{ $booking->days ?? \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}"
        data-cost-price="{{ $booking->{$costPriceField} ?? 0 }}"
        onclick="event.stopPropagation();">
</label>