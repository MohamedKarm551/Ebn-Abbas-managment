{{-- filepath: resources/views/components/booking-selector.blade.php --}}
@props([
    'bookings',
    'amountField' => 'amount_due_from_company',
    'costPriceField' => 'cost_price',
    'tableId' => 'bookingsTable' // Default table ID
])

<div class="booking-selector-container mb-3"> {{-- Add a container --}}
    <table class="table table-bordered" id="{{ $tableId }}"> {{-- Use dynamic ID --}}
        <thead>
            {{-- Let the parent view define the header if it varies --}}
            {{ $header ?? '' }} {{-- Optional slot for header --}}
        </thead>
        <tbody>
            @foreach ($bookings as $key => $booking)
                <tr style="cursor: pointer;">
                    <td>{{ $key + 1 }}</td> {{-- Numbering --}}
                    <td>
                        <label>
                            <input type="checkbox" class="booking-checkbox" data-booking-id="{{ $booking->id }}"
                                {{-- Use dynamic fields --}}
                                data-amount-due="{{ $booking->{$amountField} ?? 0 }}"
                                data-amount-paid="{{ $booking->amount_paid_by_company ?? ($booking->amount_paid_to_hotel ?? 0) }}" {{-- Try to guess paid amount --}}
                                data-client-name="{{ $booking->client_name }}"
                                data-hotel-name="{{ $booking->hotel->name ?? 'N/A' }}"
                                data-check-in="{{ $booking->check_in->format('Y-m-d') }}"
                                data-check-out="{{ $booking->check_out->format('Y-m-d') }}"
                                data-rooms="{{ $booking->rooms }}"
                                data-days="{{ $booking->days ?? \Carbon\Carbon::parse($booking->check_in)->diffInDays(\Carbon\Carbon::parse($booking->check_out)) }}"
                                data-cost-price="{{ $booking->{$costPriceField} ?? 0 }}"
                                onclick="event.stopPropagation();">
                        </label>
                    </td>
                    {{-- Render the rest of the row columns passed from parent --}}
                    {{ $slot }} {{-- Use a slot for the rest of the row data --}}
                </tr>
            @endforeach
        </tbody>
    </table>
    <button class="btn btn-primary selectRangeBtn">تحديد النطاق</button> {{-- Use class instead of ID --}}
    <button class="btn btn-secondary resetRangeBtn">إعادة تعيين النطاق</button> {{-- Use class instead of ID --}}
</div>

@push('scripts')
{{-- Use @once to ensure the script runs only once even if component is used multiple times --}}
@once
<script>
    // Wrap the entire script logic in a function to handle multiple instances
    function initializeBookingSelector(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return; // Exit if container not found

        const checkboxes = container.querySelectorAll('.booking-checkbox');
        // Find table, select/reset buttons relative to the container
        const table = container.querySelector('table'); // Find table within container
        const selectRangeBtn = container.querySelector('.selectRangeBtn');
        const resetRangeBtn = container.querySelector('.resetRangeBtn');

        let alertDiv = null;
        let startCheckbox = null;
        let endCheckbox = null;

        // --- Paste the ENTIRE JavaScript logic here ---
        // --- IMPORTANT: Modify JS to use relative selectors ---
        // Example: Replace getElementById('companyBookingsTable') with just 'table' variable
        // Replace getElementById('selectRangeBtn') with 'selectRangeBtn' variable
        // Replace getElementById('resetRangeBtn') with 'resetRangeBtn' variable

        // Add click event listener to each row within this container
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', function() {
                const checkbox = row.querySelector('.booking-checkbox');
                handleCheckboxSelection(checkbox);
            });
        });

         checkboxes.forEach(checkbox => {
            checkbox.addEventListener('click', function(event) {
                event.stopPropagation();
                handleCheckboxSelection(this);
            });
            checkbox.addEventListener('change', function() {
                updateSelectedRows();
            });
        });

        // Common function to handle setting start and end points
        function handleCheckboxSelection(checkbox) {
             // Check if the checkbox belongs to the current container instance
            if (!container.contains(checkbox)) return;

            if (startCheckbox === null) {
                startCheckbox = checkbox;
                checkbox.closest('tr').classList.add('range-start');
                showNotification('تم تحديد نقطة البداية. الرجاء تحديد نقطة النهاية.', 'info');
            } else if (endCheckbox === null && checkbox !== startCheckbox) {
                endCheckbox = checkbox;
                checkbox.closest('tr').classList.add('range-end');
                showNotification('تم تحديد نقطة النهاية. اضغط على زر "تحديد النطاق".', 'success');
            } else {
                clearRangeSelectionVisuals(); // Clear visuals within this container
                startCheckbox = checkbox; // Start new selection
                endCheckbox = null;
                checkbox.closest('tr').classList.add('range-start');
                showNotification('تم تحديد نقطة بداية جديدة. الرجاء تحديد نقطة النهاية.', 'warning');
            }
            updateSelectedRows(); // Update totals/alert immediately for this container
        }

        // Function to clear visual indicators for range start/end within this container
        function clearRangeSelectionVisuals() {
            table.querySelectorAll('tr.range-start, tr.range-end').forEach(row => {
                row.classList.remove('range-start', 'range-end');
                 if (!row.classList.contains('selected-row')) {
                    row.style.backgroundColor = '';
                    row.style.color = '';
                    row.style.fontWeight = '';
                } else {
                    row.style.backgroundColor = 'rgba(220, 53, 69, 0.3)';
                    row.style.color = '#fff';
                    row.style.fontWeight = 'bold';
                }
            });
        }

         // Event listener for the reset button within this container
        resetRangeBtn.addEventListener('click', function() {
            clearRangeSelectionVisuals();
            startCheckbox = null;
            endCheckbox = null;
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedRows();
        });

        // Event listener for the select range button within this container
        selectRangeBtn.addEventListener('click', function() {
            if (!startCheckbox || !endCheckbox) {
                showNotification('الرجاء تحديد نقطة البداية ونقطة النهاية أولاً.', 'danger');
                return;
            }
            const checkboxesArray = Array.from(checkboxes);
            const startIndex = checkboxesArray.indexOf(startCheckbox);
            const endIndex = checkboxesArray.indexOf(endCheckbox);
            if (startIndex === -1 || endIndex === -1) {
                showNotification('حدث خطأ في تحديد النطاق.', 'danger'); // Use notification
                return;
            }
            const minIndex = Math.min(startIndex, endIndex);
            const maxIndex = Math.max(startIndex, endIndex);
            for (let i = minIndex; i <= maxIndex; i++) {
                checkboxesArray[i].checked = true;
            }
            clearRangeSelectionVisuals();
            startCheckbox = null;
            endCheckbox = null;
            updateSelectedRows();
        });


        // Helper function to format date with Gregorian (Arabic Name) and Hijri
        function formatCombinedDate(dateString) {
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return "تاريخ غير صالح";
                const gregorianOptions = { day: 'numeric', month: 'long' };
                const gregorianFormatted = date.toLocaleDateString('ar-EG', gregorianOptions);
                const hijriOptions = { day: 'numeric', month: 'long', year: 'numeric', calendar: 'islamic' };
                const hijriFormatted = date.toLocaleDateString('ar-SA-u-ca-islamic', hijriOptions);
                return `${gregorianFormatted} (${hijriFormatted})`;
            } catch (e) { return "خطأ في التاريخ"; }
        }

        // Update selected rows logic specific to this container
        function updateSelectedRows() {
            let totalAmount = 0;
            let bookingDetailsHTML = [];
            let bookingDetailsText = [];
            let selectedCount = 0;

            checkboxes.forEach((checkbox, index) => {
                const row = checkbox.closest('tr');
                if (checkbox.checked) {
                    selectedCount++;
                    row.classList.add('selected-row');
                    const amountDue = parseFloat(checkbox.dataset.amountDue) || 0;
                    totalAmount += amountDue;
                    const clientName = checkbox.dataset.clientName;
                    const hotelName = checkbox.dataset.hotelName || 'فندق غير محدد';
                    const checkInString = checkbox.dataset.checkIn;
                    const checkOutString = checkbox.dataset.checkOut;
                    const rooms = checkbox.dataset.rooms;
                    const days = checkbox.dataset.days;
                    const costPrice = checkbox.dataset.costPrice;
                    const checkInFormatted = formatCombinedDate(checkInString);
                    const checkOutFormatted = formatCombinedDate(checkOutString);

                    bookingDetailsHTML.push(
                         `<li class="list-group-item d-flex justify-content-between align-items-start bg-transparent text-white border-secondary">
                            <span class="badge bg-light text-dark rounded-pill me-3">${index + 1}</span>
                            <div class="ms-0 me-auto text-start">
                                <div class="fw-bold">${clientName} - ${hotelName}</div>
                                <small>${rooms} غرف | ${checkInFormatted} إلى ${checkOutFormatted} (${days} ليالي) | ${costPrice} ريال</small>
                            </div>
                            <span class="badge bg-light text-dark rounded-pill ms-3">${amountDue.toFixed(2)}</span>
                        </li>`
                    );
                    bookingDetailsText.push(
                        `${clientName} - ${hotelName} | ${rooms} غرف | ${checkInFormatted} إلى ${checkOutFormatted} (${days} ليالي) | ${costPrice} ريال | المستحق: ${amountDue.toFixed(2)}`
                    );
                } else {
                    row.classList.remove('selected-row');
                }
            });

            // Apply styling within this container's table
            table.querySelectorAll('tbody tr').forEach(row => {
                 if (row.classList.contains('selected-row')) {
                    row.style.backgroundColor = 'rgba(220, 53, 69, 0.3)';
                    row.style.color = '#fff';
                    row.style.fontWeight = 'bold';
                } else {
                    if (!row.classList.contains('range-start') && !row.classList.contains('range-end')) {
                        row.style.backgroundColor = '';
                        row.style.color = '';
                        row.style.fontWeight = '';
                    }
                }
            });
            const startRow = table.querySelector('tr.range-start');
            if (startRow) {
                startRow.style.backgroundColor = 'rgba(255, 193, 7, 0.4)';
                startRow.style.color = '#000';
                startRow.style.fontWeight = 'bold';
            }
            const endRow = table.querySelector('tr.range-end');
            if (endRow) {
                endRow.style.backgroundColor = 'rgba(23, 162, 184, 0.4)';
                endRow.style.color = '#000';
                endRow.style.fontWeight = 'bold';
            }

            // Show/hide alert logic
            if (selectedCount > 0) {
                let calculationDetails = bookingDetailsHTML.join('');
                let alertMessage = `
                    <div class="d-flex flex-column align-items-center" style="direction: rtl;">
                        <h5 class="mb-3">تم تحديد ${selectedCount} حجوزات</h5>
                        <ul class="list-group list-group-flush w-100 mb-3" style="text-align: right;">
                            ${calculationDetails}
                        </ul>
                        <h4 class="mb-3">الإجمالي: ${totalAmount.toFixed(2)} ريال</h4>
                        <div class="d-flex justify-content-center mt-2">
                            <button type="button" class="btn btn-light btn-sm mx-2 copyAlertBtn">نسخ</button> {/* Use class */}
                            <button type="button" class="btn btn-outline-light btn-sm closeAlertBtn">إغلاق</button> {/* Use class */}
                        </div>
                    </div>
                `;
                showAlert(alertMessage, selectedCount, bookingDetailsText, totalAmount);
            } else {
                table.querySelectorAll('tr').forEach(row => {
                    row.classList.remove('range-start', 'range-end');
                    row.style.backgroundColor = '';
                    row.style.color = '';
                    row.style.fontWeight = '';
                });
                if (alertDiv) {
                    alertDiv.remove();
                    alertDiv = null;
                }
            }
        }

        // Main Alert Function (Global or specific instance?) - Keep global for now
        // Ensure copy/close buttons inside use classes if alertDiv is global
        function showAlert(message, count, detailsTextArray, total) {
             if (alertDiv) alertDiv.remove();
             alertDiv = document.createElement('div');
             alertDiv.className = 'alert alert-danger fixed-top shadow-lg';
             alertDiv.style.cssText = `...`; // Same styles as before
             alertDiv.innerHTML = message;
             document.body.appendChild(alertDiv);

             // Use classes for buttons inside the alert
             const closeAlertBtn = alertDiv.querySelector('.closeAlertBtn');
             closeAlertBtn.addEventListener('click', function() {
                 alertDiv.remove();
                 alertDiv = null;
             });
             const copyAlertBtn = alertDiv.querySelector('.copyAlertBtn');
             copyAlertBtn.addEventListener('click', function() {
                let alertText = `تقرير الحجوزات (${count} حجز محدد)\n------------------------------------\n`;
                detailsTextArray.forEach((detail, index) => { alertText += `${index + 1}. ${detail}\n`; });
                alertText += `------------------------------------\nالإجمالي: ${total.toFixed(2)} ريال`;
                navigator.clipboard.writeText(alertText).then(() => { /* Success feedback */ }).catch(err => { /* Error feedback */ });
             });
        }

        // Notification function (Keep global)
        function showNotification(message, type = 'info') { /* ... Same as before ... */ }

    } // End of initializeBookingSelector function

    // Initialize for the specific table ID used in this component instance
    // Use a unique selector for the container based on table ID or a dedicated class
    document.addEventListener('DOMContentLoaded', function() {
         // Find all containers and initialize
         document.querySelectorAll('.booking-selector-container').forEach((container, index) => {
             // Give each container a unique ID if it doesn't have one
             const uniqueId = `booking-selector-${index}`;
             container.id = uniqueId;
             initializeBookingSelector(`#${uniqueId}`);
         });
    });

</script>
@endonce
@endpush