<?php
namespace App\Imports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BookingsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Booking([
            'client_name' => $row['client_name'], // اسم العميل
            'hotel_id' => $row['hotel_id'], // رقم الفندق
            'room_number' => $row['room_number'], // نوع الغرفة
            'check_in' => \Carbon\Carbon::parse($row['check_in']), // تاريخ الدخول
            'check_out' => \Carbon\Carbon::parse($row['check_out']), // تاريخ الخروج
            'days' => $row['days'], // عدد الأيام
            'rooms' => $row['rooms'], // عدد الغرف
            'agent' => $row['agent'], // اسم الشركة
            'cost_price' => $row['cost_price'], // سعر التكلفة
            'sale_price' => $row['sale_price'], // سعر البيع
            'payment_status' => $row['payment_status'], // حالة الدفع
            'notes' => $row['notes'] ?? null, // الملاحظات
        ]);
    }
}

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BookingsImport;

class ImportBookings extends Command
{
    protected $signature = 'import:bookings {file}';
    protected $description = 'Import bookings from a CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error('File not found.');
            return 1;
        }

        try {
            Excel::import(new BookingsImport, $filePath);
            $this->info('Bookings imported successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }

        return 0;
    }
}