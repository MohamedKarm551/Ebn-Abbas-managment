<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportToExcel()
    {
        return Excel::download(new \App\Exports\BookingsExport, 'bookings.xlsx');
    }
}