<?php

namespace App\View\Components;

use Illuminate\Database\Eloquent\Collection; // Or Illuminate\Support\Collection
use Illuminate\View\Component;

class BookingSelector extends Component
{
    public Collection $bookings;
    public string $amountField;
    public string $costPriceField;
    public string $tableId; // Add a unique ID for the table

    /**
     * Create a new component instance.
     *
     * @param Collection $bookings
     * @param string $amountField The field name for the amount due (e.g., 'amount_due_from_company')
     * @param string $costPriceField The field name for the cost price (e.g., 'cost_price')
     * @param string $tableId A unique ID for the table element
     * @return void
     */
    public function __construct(Collection $bookings, string $amountField = 'amount_due_from_company', string $costPriceField = 'cost_price', string $tableId = 'bookingsTable')
    {
        $this->bookings = $bookings;
        $this->amountField = $amountField;
        $this->costPriceField = $costPriceField;
        $this->tableId = $tableId; // Assign the unique ID
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.booking-selector');
    }
}
