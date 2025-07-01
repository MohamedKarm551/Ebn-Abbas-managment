<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\BookingOperationReport;
use App\Models\BookingReportVisa;
use App\Models\BookingReportFlight;
use App\Models\BookingReportTransport;
use App\Models\BookingReportHotel;
use App\Models\BookingReportLandTrip;
use App\Models\Booking;
use App\Models\LandTripBooking;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingOperationReportController extends Controller
{
    // عرض صفحة تقارير العمليات
    public function index()
    {
        $reports = BookingOperationReport::with(['employee', 'client', 'company', 'visas', 'flights', 'transports', 'hotels', 'landTrips'])
            ->latest()
            ->paginate(20);

        // حساب الأرباح حسب العملة
        $profitsByCurrency = $this->calculateProfitsByCurrency();

        // حساب عدد التقارير هذا الشهر
        $reportsThisMonth = BookingOperationReport::whereMonth('report_date', now()->month)
            ->whereYear('report_date', now()->year)
            ->count();

        return view('admin.operation-reports.index', compact(
            'reports',
            'profitsByCurrency',
            'reportsThisMonth'
        ));
    }

    /**
     * حساب الأرباح مجمعة حسب العملة
     */
    private function calculateProfitsByCurrency()
    {
        $profits = [
            'KWD' => 0,
            'SAR' => 0,
            'USD' => 0,
            'EUR' => 0
        ];

        // جمع أرباح التأشيرات
        $visaProfits = DB::table('booking_report_visas')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($visaProfits as $profit) {
            if (isset($profits[$profit->currency])) {
                $profits[$profit->currency] += $profit->total_profit;
            }
        }

        // جمع أرباح الطيران
        $flightProfits = DB::table('booking_report_flights')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($flightProfits as $profit) {
            if (isset($profits[$profit->currency])) {
                $profits[$profit->currency] += $profit->total_profit;
            }
        }

        // جمع أرباح النقل
        $transportProfits = DB::table('booking_report_transports')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($transportProfits as $profit) {
            if (isset($profits[$profit->currency])) {
                $profits[$profit->currency] += $profit->total_profit;
            }
        }

        // جمع أرباح الفنادق
        $hotelProfits = DB::table('booking_report_hotels')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($hotelProfits as $profit) {
            if (isset($profits[$profit->currency])) {
                $profits[$profit->currency] += $profit->total_profit;
            }
        }

        // جمع أرباح الرحلات البرية
        $landTripProfits = DB::table('booking_report_land_trips')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($landTripProfits as $profit) {
            if (isset($profits[$profit->currency])) {
                $profits[$profit->currency] += $profit->total_profit;
            }
        }

        // إزالة العملات التي لا تحتوي على أرباح
        return array_filter($profits, function ($value) {
            return $value > 0;
        });
    }
    // عرض صفحة إنشاء تقرير العمليات
    public function create()
    {
        // جلب آخر الحجوزات (فنادق + رحلات برية)
        $recentBookings = $this->getRecentBookings();
        $clients = Client::latest()->take(50)->get();
        $companies = Company::all();

        return view('admin.operation-reports.create', compact('recentBookings', 'clients', 'companies'));
    }

    //  جلب آخر الحجوزات (فنادق + رحلات برية)
    public function getBookingDetails(Request $request)
    {
        Log::info('=== استدعاء getBookingDetails ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route_name' => $request->route()?->getName(),
            'type' => $request->type,
            'id' => $request->id,
            'all_params' => $request->all()
        ]);

        try {
            if ($request->type === 'hotel') {
                $booking = Booking::with(['hotel', 'company'])->find($request->id);

                if (!$booking) {
                    return response()->json(['success' => false, 'message' => 'لم يتم العثور على الحجز']);
                }

                // حساب سعر الليلة الواحدة إذا لم يكن محفوظاً
                $nightCost = 0;
                $nightSellingPrice = 0;

                if ($booking->days && $booking->rooms) {
                    $nightCost = $booking->cost_price ? ($booking->cost_price / ($booking->days * $booking->rooms)) : 0;
                    $nightSellingPrice = $booking->sale_price ? ($booking->sale_price / ($booking->days * $booking->rooms)) : 0;
                }

                $hotelData = [
                    'hotel_name' => $booking->hotel->name ?? '',
                    'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : '',
                    'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : '',
                    'nights' => $booking->days ?? 1, // استخدم days بدلاً من nights
                    'rooms' => $booking->rooms ?? 1,
                    'room_type' => $booking->room_type ?? '',
                    'night_cost' => round($nightCost, 2),
                    'night_selling_price' => round($nightSellingPrice, 2),
                    'currency' => $booking->currency ?? 'KWD',
                    'guests' => $booking->guests ?? 1,
                ];

                return response()->json([
                    'success' => true,
                    'type' => 'hotel',
                    'hotelData' => $hotelData,
                ]);
            } elseif ($request->type === 'land_trip') {
                $trip = LandTripBooking::with(['landTrip.tripType', 'company'])->find($request->id);

                if (!$trip) {
                    return response()->json(['success' => false, 'message' => 'لم يتم العثور على الرحلة البرية']);
                }

                Log::info('بيانات الرحلة البرية:', [
                    'trip' => $trip->toArray()
                ]);

                $landTripData = [
                    'trip_type' => $trip->landTrip->tripType->name ?? 'رحلة برية',
                    'departure_date' => $trip->landTrip->departure_date ? $trip->landTrip->departure_date->format('Y-m-d') : '',
                    'return_date' => $trip->landTrip->return_date ? $trip->landTrip->return_date->format('Y-m-d') : '',
                    'days' => $trip->landTrip->days_count ?? 1,
                    'selling_price' => $trip->sale_price ?? 0,
                    'transport_cost' => $trip->cost_price ?? 0,
                    'mecca_hotel_cost' => 0, // أضف هذه إذا كانت موجودة في جدولك
                    'medina_hotel_cost' => 0, // أضف هذه إذا كانت موجودة في جدولك
                    'extra_costs' => 0, // أضف هذه إذا كانت موجودة في جدولك
                    'currency' => $trip->currency ?? 'KWD',
                ];

                Log::info('بيانات الرحلة البرية المرسلة', ['landTripData' => $landTripData]);

                return response()->json([
                    'success' => true,
                    'type' => 'land_trip',
                    'landTripData' => $landTripData,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'نوع الحجز غير صالح']);
        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على بيانات الحجز: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استرجاع البيانات',
                'error' => $e->getMessage()
            ]);
        }
    }
    // ===============
    /**
     * حفظ تقرير العمليات الجديد في قاعدة البيانات
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'report_date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_notes' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'booking_type' => 'nullable|string|max:20',
            'booking_id' => 'nullable|integer',
            'booking_reference' => 'nullable|string|max:100',
            'hotels.*.voucher_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
            'transports.*.ticket_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // البحث عن العميل أو إنشاء عميل جديد
            $client = Client::firstOrCreate(
                ['name' => $validated['client_name']],
                [
                    'phone' => $validated['client_phone'] ?? null,
                    'email' => $request->client_email ?? null,
                    'notes' => $request->client_notes ?? null
                ]
            );

            // البحث عن الشركة أو إنشاء شركة جديدة إذا وجدت
            $company = null;
            if ($request->filled('company_name')) {
                $company = Company::firstOrCreate(
                    ['name' => $request->company_name],
                    ['phone' => $request->company_phone ?? null]
                );
            }

            // إنشاء تقرير العملية
            $report = BookingOperationReport::create([
                'employee_id' => Auth::id(),
                'report_date' => $validated['report_date'],
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_phone' => $client->phone,
                'company_id' => $company ? $company->id : null,
                'company_name' => $company ? $company->name : null,
                'company_phone' => $company ? $company->phone : null,
                'booking_type' => $validated['booking_type'] ?? null,
                'booking_id' => $validated['booking_id'] ?? null,
                'booking_reference' => $validated['booking_reference'] ?? null,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // معالجة بيانات التأشيرات
            $totalVisaProfit = 0;
            if ($request->has('visas')) {
                foreach ($request->visas as $visaData) {
                    $profit = floatval($visaData['profit'] ?? 0);
                    $totalVisaProfit += $profit;

                    BookingReportVisa::create([
                        'booking_operation_report_id' => $report->id,
                        'visa_type' => $visaData['visa_type'] ?? 'سياحية',
                        'quantity' => $visaData['quantity'] ?? 1,
                        'cost' => $visaData['cost'] ?? 0,
                        'selling_price' => $visaData['selling_price'] ?? 0,
                        'currency' => $visaData['currency'] ?? 'KWD',
                        'profit' => $profit,
                        'notes' => $visaData['notes'] ?? null,
                    ]);
                }
            }
            $report->total_visa_profit = $totalVisaProfit;

            // معالجة بيانات الطيران
            $totalFlightProfit = 0;
            if ($request->has('flights')) {
                foreach ($request->flights as $flightData) {
                    $profit = floatval($flightData['profit'] ?? 0);
                    $totalFlightProfit += $profit;

                    BookingReportFlight::create([
                        'booking_operation_report_id' => $report->id,
                        'flight_date' => $flightData['flight_date'] ?? null,
                        'flight_number' => $flightData['flight_number'] ?? null,
                        'airline' => $flightData['airline'] ?? null,
                        'route' => $flightData['route'] ?? null,
                        'passengers' => $flightData['passengers'] ?? 1,
                        'trip_type' => $flightData['trip_type'] ?? 'ذهاب وعودة',
                        'cost' => $flightData['cost'] ?? 0,
                        'selling_price' => $flightData['selling_price'] ?? 0,
                        'currency' => $flightData['currency'] ?? 'KWD',
                        'profit' => $profit,
                        'notes' => $flightData['notes'] ?? null,
                    ]);
                }
            }
            $report->total_flight_profit = $totalFlightProfit;

            // معالجة بيانات النقل
            $totalTransportProfit = 0;
            if ($request->has('transports')) {
                foreach ($request->transports as $index => $transportData) {
                    $profit = floatval($transportData['profit'] ?? 0);
                    $totalTransportProfit += $profit;

                    $transportEntry = [
                        'booking_operation_report_id' => $report->id,
                        'transport_type' => $transportData['transport_type'] ?? null,
                        'driver_name' => $transportData['driver_name'] ?? null,
                        'driver_phone' => $transportData['driver_phone'] ?? null,
                        'vehicle_info' => $transportData['vehicle_info'] ?? null,
                        'departure_time' => $transportData['departure_time'] ?? null,
                        'arrival_time' => $transportData['arrival_time'] ?? null,
                        'schedule_notes' => $transportData['schedule_notes'] ?? null,
                        'cost' => $transportData['cost'] ?? 0,
                        'selling_price' => $transportData['selling_price'] ?? 0,
                        'currency' => $transportData['currency'] ?? 'KWD',
                        'profit' => $profit,
                        'notes' => $transportData['notes'] ?? null,
                    ];

                    // معالجة ملف التذكرة
                    if ($request->hasFile("transports.{$index}.ticket_file")) {
                        $file = $request->file("transports.{$index}.ticket_file");
                        $fileName = time() . '_transport_' . $index . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('transport-tickets', $fileName, 'public');
                        $transportEntry['ticket_file_path'] = $path;

                        // --- نسخ الملف يدويًا إلى public/storage/transport-tickets ---
                        $publicPath = public_path('storage/transport-tickets/' . $fileName);
                        if (!file_exists(dirname($publicPath))) {
                            mkdir(dirname($publicPath), 0775, true);
                        }
                        copy($file->getRealPath(), $publicPath);

                        Log::info("تم رفع تذكرة النقل {$index}", [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'file_size' => $file->getSize()
                        ]);
                    }


                    BookingReportTransport::create($transportEntry);
                }
            }
            $report->total_transport_profit = $totalTransportProfit;

            // معالجة بيانات الفنادق
            $totalHotelProfit = 0;
            if ($request->has('hotels')) {
                foreach ($request->hotels as $index => $hotelData) {
                    $profit = floatval($hotelData['profit'] ?? 0);
                    $totalHotelProfit += $profit;

                    $hotelEntry = [
                        'booking_operation_report_id' => $report->id,
                        'hotel_name' => $hotelData['hotel_name'] ?? null,
                        'city' => $hotelData['city'] ?? null,
                        'room_type' => $hotelData['room_type'] ?? null,
                        'nights' => $hotelData['nights'] ?? 1,
                        'rooms' => $hotelData['rooms'] ?? 1,
                        'check_in' => $hotelData['check_in'] ?? null,
                        'check_out' => $hotelData['check_out'] ?? null,
                        'guests' => $hotelData['guests'] ?? 1,
                        'night_cost' => $hotelData['night_cost'] ?? 0,
                        'night_selling_price' => $hotelData['night_selling_price'] ?? 0,
                        'total_cost' => $hotelData['total_cost'] ?? 0,
                        'total_selling_price' => $hotelData['total_selling_price'] ?? 0,
                        'profit' => $profit,
                        'currency' => $hotelData['currency'] ?? 'KWD',
                        'notes' => $hotelData['notes'] ?? null,
                    ];

                    // معالجة ملف الفاوتشر
                    if ($request->hasFile("hotels.{$index}.voucher_file")) {
                        $file = $request->file("hotels.{$index}.voucher_file");
                        $fileName = time() . '_hotel_' . $index . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('hotel-vouchers', $fileName, 'public');
                        $hotelEntry['voucher_file_path'] = $path;

                        Log::info("تم رفع فاوتشر الفندق {$index}", [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'file_size' => $file->getSize()
                        ]);
                    }

                    BookingReportHotel::create($hotelEntry);
                }
            }
            $report->total_hotel_profit = $totalHotelProfit;

            // معالجة بيانات الرحلات البرية
            $totalLandTripProfit = 0;
            if ($request->has('land_trips')) {
                foreach ($request->land_trips as $index => $tripData) {
                    $profit = floatval($tripData['profit'] ?? 0);
                    $totalLandTripProfit += $profit;

                    $tripEntry = [
                        'booking_operation_report_id' => $report->id,
                        'trip_type' => $tripData['trip_type'] ?? null,
                        'departure_date' => $tripData['departure_date'] ?? null,
                        'return_date' => $tripData['return_date'] ?? null,
                        'days' => $tripData['days'] ?? 1,
                        'transport_cost' => $tripData['transport_cost'] ?? 0,
                        'mecca_hotel_cost' => $tripData['mecca_hotel_cost'] ?? 0,
                        'medina_hotel_cost' => $tripData['medina_hotel_cost'] ?? 0,
                        'extra_costs' => $tripData['extra_costs'] ?? 0,
                        'total_cost' => $tripData['total_cost'] ?? 0,
                        'selling_price' => $tripData['selling_price'] ?? 0,
                        'currency' => $tripData['currency'] ?? 'KWD',
                        'profit' => $profit,
                        'notes' => $tripData['notes'] ?? null,
                    ];

                    BookingReportLandTrip::create($tripEntry);
                }
            }
            $report->total_land_trip_profit = $totalLandTripProfit;

            // حساب المجموع الكلي للأرباح
            $report->grand_total_profit =
                $totalVisaProfit +
                $totalFlightProfit +
                $totalTransportProfit +
                $totalHotelProfit +
                $totalLandTripProfit;

            $report->save();

            DB::commit();

            return redirect()->route('admin.operation-reports.show', $report)
                ->with('success', 'تم إنشاء تقرير العمليات بنجاح');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('خطأ في إنشاء تقرير العمليات: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage());
        }
    }
    public function update(Request $request, BookingOperationReport $operationReport)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'report_date' => 'required|date',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'client_notes' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:20',
            'booking_type' => 'nullable|string|max:20',
            'booking_id' => 'nullable|integer',
            'booking_reference' => 'nullable|string|max:100',
            'hotels.*.voucher_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
            'transports.*.ticket_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // البحث عن العميل أو إنشاء عميل جديد
            $client = Client::firstOrCreate(
                ['name' => $validated['client_name']],
                [
                    'phone' => $validated['client_phone'] ?? null,
                    'email' => $request->client_email ?? null,
                    'notes' => $request->client_notes ?? null
                ]
            );

            // البحث عن الشركة أو إنشاء شركة جديدة إذا وجدت
            $company = null;
            if ($request->filled('company_name')) {
                $company = Company::firstOrCreate(
                    ['name' => $request->company_name],
                    ['phone' => $request->company_phone ?? null]
                );
            }

            // تحديث تقرير العملية
            $operationReport->update([
                'employee_id' => Auth::id(),
                'report_date' => $validated['report_date'],
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_phone' => $client->phone,
                'company_id' => $company ? $company->id : null,
                'company_name' => $company ? $company->name : null,
                'company_phone' => $company ? $company->phone : null,
                'booking_type' => $validated['booking_type'] ?? null,
                'booking_id' => $validated['booking_id'] ?? null,
                'booking_reference' => $validated['booking_reference'] ?? null,
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // حذف البيانات القديمة أولاً
            $operationReport->visas()->delete();
            $operationReport->flights()->delete();
            $operationReport->transports()->delete();
            $operationReport->hotels()->delete();
            $operationReport->landTrips()->delete();

            // معالجة بيانات التأشيرات الجديدة
            $totalVisaProfit = 0;
            if ($request->has('visas')) {
                foreach ($request->visas as $visaData) {
                    $profit = floatval($visaData['profit'] ?? 0);
                    $totalVisaProfit += $profit;

                    BookingReportVisa::create([
                        'booking_operation_report_id' => $operationReport->id,
                        'visa_type' => $visaData['visa_type'] ?? 'سياحية',
                        'quantity' => $visaData['quantity'] ?? 1,
                        'cost' => $visaData['cost'] ?? 0,
                        'selling_price' => $visaData['selling_price'] ?? 0,
                        'currency' => $visaData['currency'] ?? 'KWD', // ✅ إضافة العملة
                        'profit' => $profit,
                        'notes' => $visaData['notes'] ?? null,
                    ]);
                }
            }
            $operationReport->total_visa_profit = $totalVisaProfit;

            // معالجة بيانات الطيران الجديدة
            $totalFlightProfit = 0;
            if ($request->has('flights')) {
                foreach ($request->flights as $flightData) {
                    $profit = floatval($flightData['profit'] ?? 0);
                    $totalFlightProfit += $profit;

                    BookingReportFlight::create([
                        'booking_operation_report_id' => $operationReport->id,
                        'flight_date' => $flightData['flight_date'] ?? null,
                        'flight_number' => $flightData['flight_number'] ?? null,
                        'airline' => $flightData['airline'] ?? null,
                        'route' => $flightData['route'] ?? null,
                        'passengers' => $flightData['passengers'] ?? 1,
                        'trip_type' => $flightData['trip_type'] ?? 'ذهاب وعودة',
                        'cost' => $flightData['cost'] ?? 0,
                        'selling_price' => $flightData['selling_price'] ?? 0,
                        'currency' => $flightData['currency'] ?? 'KWD', // ✅ إضافة العملة
                        'profit' => $profit,
                        'notes' => $flightData['notes'] ?? null,
                    ]);
                }
            }
            $operationReport->total_flight_profit = $totalFlightProfit;

            // معالجة بيانات النقل الجديدة
            $totalTransportProfit = 0;
            if ($request->has('transports')) {
                foreach ($request->transports as $index => $transportData) {
                    $profit = floatval($transportData['profit'] ?? 0);
                    $totalTransportProfit += $profit;

                    $transportEntry = [
                        'booking_operation_report_id' => $operationReport->id,
                        'transport_type' => $transportData['transport_type'] ?? null,
                        'driver_name' => $transportData['driver_name'] ?? null,
                        'driver_phone' => $transportData['driver_phone'] ?? null,
                        'vehicle_info' => $transportData['vehicle_info'] ?? null,
                        'departure_time' => $transportData['departure_time'] ?? null,
                        'arrival_time' => $transportData['arrival_time'] ?? null,
                        'schedule_notes' => $transportData['schedule_notes'] ?? null,
                        'cost' => $transportData['cost'] ?? 0,
                        'selling_price' => $transportData['selling_price'] ?? 0,
                        'currency' => $transportData['currency'] ?? 'KWD', // ✅ إضافة العملة
                        'profit' => $profit,
                        'notes' => $transportData['notes'] ?? null,
                    ];

                    // معالجة ملف التذكرة الجديد
                    if ($request->hasFile("hotels.{$index}.voucher_file")) {
                        $file = $request->file("hotels.{$index}.voucher_file");
                        $fileName = time() . '_hotel_' . $index . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('hotel-vouchers', $fileName, 'public');
                        $hotelEntry['voucher_file_path'] = $path;

                        // --- نسخ الملف يدويًا إلى public/storage/hotel-vouchers ---
                        $publicPath = public_path('storage/hotel-vouchers/' . $fileName);
                        if (!file_exists(dirname($publicPath))) {
                            mkdir(dirname($publicPath), 0775, true);
                        }
                        copy($file->getRealPath(), $publicPath);

                        Log::info("تم رفع فاوتشر الفندق {$index}", [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'file_size' => $file->getSize()
                        ]);
                    }
                    // إذا لم يتم رفع ملف جديد، الاحتفاظ بالملف القديم إذا وجد
                    elseif (isset($transportData['existing_ticket_file'])) {
                        $transportEntry['ticket_file_path'] = $transportData['existing_ticket_file'];
                    }

                    BookingReportTransport::create($transportEntry);
                }
            }
            $operationReport->total_transport_profit = $totalTransportProfit;

            // معالجة بيانات الفنادق الجديدة
            $totalHotelProfit = 0;
            if ($request->has('hotels')) {
                foreach ($request->hotels as $index => $hotelData) {
                    $profit = floatval($hotelData['profit'] ?? 0);
                    $totalHotelProfit += $profit;

                    $hotelEntry = [
                        'booking_operation_report_id' => $operationReport->id,
                        'hotel_name' => $hotelData['hotel_name'] ?? null,
                        'city' => $hotelData['city'] ?? null,
                        'room_type' => $hotelData['room_type'] ?? null,
                        'nights' => $hotelData['nights'] ?? 1,
                        'rooms' => $hotelData['rooms'] ?? 1,
                        'check_in' => $hotelData['check_in'] ?? null,
                        'check_out' => $hotelData['check_out'] ?? null,
                        'guests' => $hotelData['guests'] ?? 1,
                        'night_cost' => $hotelData['night_cost'] ?? 0,
                        'night_selling_price' => $hotelData['night_selling_price'] ?? 0,
                        'total_cost' => $hotelData['total_cost'] ?? 0,
                        'total_selling_price' => $hotelData['total_selling_price'] ?? 0,
                        'profit' => $profit,
                        'currency' => $hotelData['currency'] ?? 'KWD', // ✅ إضافة العملة
                        'notes' => $hotelData['notes'] ?? null,
                    ];

                    // معالجة ملف الفاوتشر الجديد
                    if ($request->hasFile("hotels.{$index}.voucher_file")) {
                        $file = $request->file("hotels.{$index}.voucher_file");
                        $fileName = time() . '_hotel_' . $index . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('hotel-vouchers', $fileName, 'public');
                        $hotelEntry['voucher_file_path'] = $path;

                        Log::info("تم رفع فاوتشر الفندق {$index} في التحديث", [
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'file_size' => $file->getSize()
                        ]);
                    }
                    // إذا لم يتم رفع ملف جديد، الاحتفاظ بالملف القديم إذا وجد
                    elseif (isset($hotelData['existing_voucher_file'])) {
                        $hotelEntry['voucher_file_path'] = $hotelData['existing_voucher_file'];
                    }

                    BookingReportHotel::create($hotelEntry);
                }
            }
            $operationReport->total_hotel_profit = $totalHotelProfit;

            // معالجة بيانات الرحلات البرية الجديدة
            $totalLandTripProfit = 0;
            if ($request->has('land_trips')) {
                foreach ($request->land_trips as $index => $tripData) {
                    $profit = floatval($tripData['profit'] ?? 0);
                    $totalLandTripProfit += $profit;

                    $tripEntry = [
                        'booking_operation_report_id' => $operationReport->id,
                        'trip_type' => $tripData['trip_type'] ?? null,
                        'departure_date' => $tripData['departure_date'] ?? null,
                        'return_date' => $tripData['return_date'] ?? null,
                        'days' => $tripData['days'] ?? 1,
                        'transport_cost' => $tripData['transport_cost'] ?? 0,
                        'mecca_hotel_cost' => $tripData['mecca_hotel_cost'] ?? 0,
                        'medina_hotel_cost' => $tripData['medina_hotel_cost'] ?? 0,
                        'extra_costs' => $tripData['extra_costs'] ?? 0,
                        'total_cost' => $tripData['total_cost'] ?? 0,
                        'selling_price' => $tripData['selling_price'] ?? 0,
                        'currency' => $tripData['currency'] ?? 'KWD', // ✅ إضافة العملة
                        'profit' => $profit,
                        'notes' => $tripData['notes'] ?? null,
                    ];

                    BookingReportLandTrip::create($tripEntry);
                }
            }
            $operationReport->total_land_trip_profit = $totalLandTripProfit;

            // تحديث إجمالي الأرباح
            $operationReport->grand_total_profit =
                $totalVisaProfit +
                $totalFlightProfit +
                $totalTransportProfit +
                $totalHotelProfit +
                $totalLandTripProfit;

            $operationReport->save();

            DB::commit();

            return redirect()->route('admin.operation-reports.show', $operationReport)
                ->with('success', 'تم تحديث تقرير العمليات بنجاح');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('خطأ في تحديث تقرير العمليات: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'report_id' => $operationReport->id
            ]);

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث التقرير: ' . $e->getMessage());
        }
    }
    // API لجلب بيانات العميل
    /**
     * الحصول على بيانات العميل
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientData(Request $request)
    {
        // التحقق من إرسال معرّف العميل أو اسم العميل
        if ($request->has('id')) {
            // البحث بالمعرّف
            $client = Client::find($request->get('id'));
        } elseif ($request->has('name')) {
            // البحث بالاسم
            $clientName = $request->get('name');
            $client = Client::where('name', 'LIKE', '%' . $clientName . '%')->first();
        } else {
            return response()->json(['client' => null]);
        }

        if ($client) {
            $latestBooking = $client->latest_booking;

            return response()->json([
                'client' => $client,
                'latest_booking' => $latestBooking,
                'company' => $latestBooking->company ?? null
            ]);
        }

        return response()->json(['client' => null]);
    }

    // API الحصول على بيانات الحجز بواسطة النوع والرقم التعريفي
    /**
     * الحصول على بيانات الحجز بواسطة النوع والرقم التعريفي
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBookingData(Request $request)
    {
        $type = $request->type;
        $id = $request->id;

        if (!$type || !$id) {
            return response()->json(['error' => 'يجب توفير نوع الحجز ورقم التعريف']);
        }

        if ($type === 'hotel') {
            $booking = Booking::with(['client', 'company', 'hotel'])->find($id);
        } elseif ($type === 'land_trip') {
            $booking = LandTripBooking::with(['client', 'company', 'landTrip'])->find($id);
        } else {
            return response()->json(['error' => 'نوع الحجز غير صالح']);
        }

        if (!$booking) {
            return response()->json(['error' => 'الحجز غير موجود']);
        }

        return response()->json([
            'booking' => $booking,
            'client' => $booking->client,
            'company' => $booking->company ?? null,
            'service' => $type === 'hotel' ? $booking->hotel : $booking->landTrip
        ]);
    }

    // جلب آخر الحجوزات
    private function getRecentBookings()
    {
        $hotelBookings = Booking::with(['company', 'hotel'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'type' => 'hotel',
                    'client_name' => $booking->client_name,
                    'service_name' => $booking->hotel->name ?? 'فندق',
                    'company' => $booking->company,
                    'date' => $booking->created_at,
                    'display_text' => $booking->client_name . ' - حجز فندق (' . ($booking->hotel->name ?? 'فندق') . ')'
                ];
            });

        $landTripBookings = LandTripBooking::with(['company', 'landTrip'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'type' => 'land_trip',
                    'client_name' => $booking->client_name,
                    'service_name' => $booking->landTrip->title ?? 'رحلة برية',
                    'company' => $booking->company,
                    'date' => $booking->created_at,
                    'display_text' => $booking->client_name . ' - رحلة برية (' . ($booking->landTrip->title ?? 'رحلة برية') . ')'
                ];
            });

        return $hotelBookings->concat($landTripBookings)
            ->sortByDesc('date')
            ->take(20)
            ->values();
    }
    // عرض تقرير العمليات
    public function show(BookingOperationReport $operationReport)
    {
        $operationReport->load(['visas', 'flights', 'transports', 'hotels', 'landTrips', 'employee', 'client', 'company']);

        return view('admin.operation-reports.show', compact('operationReport'));
    }

    public function edit(BookingOperationReport $operationReport)
    {
        $operationReport->load(['visas', 'flights', 'transports', 'hotels', 'landTrips']);
        $recentBookings = $this->getRecentBookings();
        $clients = Client::latest()->take(50)->get();
        $companies = Company::all();

        return view('admin.operation-reports.edit', compact('operationReport', 'recentBookings', 'clients', 'companies'));
    }

    public function destroy(BookingOperationReport $operationReport)
    {
        $operationReport->delete();

        return redirect()->route('admin.operation-reports.index')
            ->with('success', 'تم حذف تقرير العمليات بنجاح');
    }
    /**
     * البحث عن العملاء عن طريق الاسم
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('q');

        $clients = \App\Models\Client::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->take(10)
            ->get();

        return response()->json([
            'clients' => $clients
        ]);
    }

    /**
     * الحصول على آخر حجز للعميل
     */
    public function getClientLatestBooking($name)
    {
        $client = \App\Models\Client::where('name', 'LIKE', "%{$name}%")->first();

        if (!$client) {
            return response()->json(['latest_booking' => null]);
        }

        // جلب آخر حجز من النموذج
        $latestBooking = $client->latest_booking;

        return response()->json([
            'latest_booking' => $latestBooking
        ]);
    }
    /**
     * عرض صفحة التحليلات الرسومية
     */

    // جديد : 
    public function charts()
    {
        try {
            // ✅ استخدم نفس طريقة صفحة index - تجميع حسب العملة
            $profitsByCurrency = $this->calculateProfitsByCurrency();

            // ✅ بدلاً من تجميع الأرباح حسب النوع، اجمعها حسب النوع والعملة
            $profitsByTypeAndCurrency = $this->calculateProfitsByTypeAndCurrency();

            // ✅ للعرض في الرسم البياني، اجمع كل العملات لكل نوع
            $profitsByType = [
                'visa' => array_sum($profitsByTypeAndCurrency['visa'] ?? []),
                'flight' => array_sum($profitsByTypeAndCurrency['flight'] ?? []),
                'transport' => array_sum($profitsByTypeAndCurrency['transport'] ?? []),
                'hotel' => array_sum($profitsByTypeAndCurrency['hotel'] ?? []),
                'land_trip' => array_sum($profitsByTypeAndCurrency['land_trip'] ?? []),
            ];

            // ✅ فحص التقارير الموجودة
            $reports = BookingOperationReport::with(['visas', 'flights', 'transports', 'hotels', 'landTrips'])->get();
            foreach ($reports as $report) {
                Log::info("التقرير #{$report->id}:", [
                    'visas_count' => $report->visas->count(),
                    'flights_count' => $report->flights->count(),
                    'transports_count' => $report->transports->count(),
                    'hotels_count' => $report->hotels->count(),
                    'land_trips_count' => $report->landTrips->count(),
                    'grand_total_profit' => $report->grand_total_profit,
                ]);
            }

            // 1. تحسين استعلام أرباح كل نوع عملية
            $profitsByType = [
                'visa' => (float)DB::table('booking_report_visas')->sum('profit'),
                'flight' => (float)DB::table('booking_report_flights')->sum('profit'),
                'transport' => (float)DB::table('booking_report_transports')->sum('profit'),
                'hotel' => (float)DB::table('booking_report_hotels')->sum('profit'),
                'land_trip' => (float)DB::table('booking_report_land_trips')->sum('profit'),
            ];

            Log::info('💰 أرباح كل نوع عملية:', $profitsByType);

            // 2. بديل: استخدام الأرباح المحفوظة في التقرير الرئيسي
            $profitsByTypeFromReports = [
                'visa' => (float)BookingOperationReport::sum('total_visa_profit'),
                'flight' => (float)BookingOperationReport::sum('total_flight_profit'),
                'transport' => (float)BookingOperationReport::sum('total_transport_profit'),
                'hotel' => (float)BookingOperationReport::sum('total_hotel_profit'),
                'land_trip' => (float)BookingOperationReport::sum('total_land_trip_profit'),
            ];

            Log::info('💰 أرباح من التقارير الرئيسية:', $profitsByTypeFromReports);

            // استخدم البيانات اللي فيها قيم
            $totalFromTables = array_sum($profitsByType);
            $totalFromReports = array_sum($profitsByTypeFromReports);

            if ($totalFromReports > 0) {
                $profitsByType = $profitsByTypeFromReports;
                Log::info('✅ استخدام بيانات من التقارير الرئيسية');
            } elseif ($totalFromTables > 0) {
                Log::info('✅ استخدام بيانات من الجداول الفرعية');
            } else {
                Log::warning('⚠️ لا توجد أرباح في أي من المصادر');
            }

            // باقي الكود...
            // 2. التقارير عبر الزمن (آخر 30 يوم)
            $reportsOverTime = BookingOperationReport::selectRaw('
            DATE(report_date) as date,
            COUNT(*) as reports_count
        ')
                ->where('report_date', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // 3. أعلى العملاء (بسيط ومباشر)
            $topClients = BookingOperationReport::selectRaw('
            client_name,
            COUNT(*) as reports_count,
            SUM(grand_total_profit) as total_profit
        ')
                ->whereNotNull('client_name')
                ->where('client_name', '!=', '')
                ->groupBy('client_name')
                ->orderBy('total_profit', 'desc')
                ->limit(10)
                ->get();

            // 4. أعلى الشركات (بسيط ومباشر)
            $topCompanies = BookingOperationReport::selectRaw('
            company_name,
            COUNT(*) as reports_count,
            SUM(grand_total_profit) as total_profit
        ')
                ->whereNotNull('company_name')
                ->where('company_name', '!=', '')
                ->groupBy('company_name')
                ->orderBy('total_profit', 'desc')
                ->limit(10)
                ->get();

            // 5. فئات الربح (بسيطة)
            $profitRanges = [
                'صغير (0-100)' => BookingOperationReport::whereBetween('grand_total_profit', [0, 100])->count(),
                'متوسط (100-500)' => BookingOperationReport::whereBetween('grand_total_profit', [100, 500])->count(),
                'كبير (500-1000)' => BookingOperationReport::whereBetween('grand_total_profit', [500, 1000])->count(),
                'ضخم (+1000)' => BookingOperationReport::where('grand_total_profit', '>', 1000)->count(),
            ];

            // 6. العملاء الأكثر نشاطاً
            $mostActiveClients = BookingOperationReport::selectRaw('
            client_name,
            COUNT(*) as reports_count
        ')
                ->whereNotNull('client_name')
                ->where('client_name', '!=', '')
                ->groupBy('client_name')
                ->orderBy('reports_count', 'desc')
                ->limit(8)
                ->get();

            // 7. متوسط الربح لكل نوع عملية
            $avgProfitByType = [
                'visa' => DB::table('booking_report_visas')->avg('profit') ?? 0,
                'flight' => DB::table('booking_report_flights')->avg('profit') ?? 0,
                'transport' => DB::table('booking_report_transports')->avg('profit') ?? 0,
                'hotel' => DB::table('booking_report_hotels')->avg('profit') ?? 0,
                'land_trip' => DB::table('booking_report_land_trips')->avg('profit') ?? 0,
            ];

            // 8. الأرباح حسب العملة
            $profitsByCurrency = $this->calculateProfitsByCurrency();

            // 9. توزيع الحالات
            $statusDistribution = BookingOperationReport::selectRaw('
            status,
            COUNT(*) as count
        ')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // 10. إحصائيات أساسية
            $totalReports = BookingOperationReport::count();
            $totalProfitByCurrency = $profitsByCurrency;
            $totalClients = BookingOperationReport::distinct('client_name')->count('client_name');
            $totalCompanies = BookingOperationReport::distinct('company_name')->whereNotNull('company_name')->count('company_name');

            // متغيرات إضافية للتوافق مع الفيو
            $totalProfit = array_sum($totalProfitByCurrency);
            $avgProfitPerReport = $totalReports > 0 ? BookingOperationReport::avg('grand_total_profit') : 0;

            // ✅ إذا لم توجد بيانات حقيقية أو كانت فارغة، استخدم بيانات تجريبية
            if ($totalReports == 0 || array_sum($profitsByType) == 0) {
                Log::info('📊 استخدام بيانات تجريبية');

                $profitsByType = [
                    'visa' => 1500,
                    'flight' => 2500,
                    'transport' => 800,
                    'hotel' => 3200,
                    'land_trip' => 1200,
                ];

                $topClients = collect([
                    (object)['client_name' => 'عميل تجريبي 1', 'reports_count' => 5, 'total_profit' => 2500],
                    (object)['client_name' => 'عميل تجريبي 2', 'reports_count' => 3, 'total_profit' => 1800],
                    (object)['client_name' => 'عميل تجريبي 3', 'reports_count' => 7, 'total_profit' => 3200],
                ]);

                $topCompanies = collect([
                    (object)['company_name' => 'شركة تجريبية 1', 'reports_count' => 4, 'total_profit' => 2000],
                    (object)['company_name' => 'شركة تجريبية 2', 'reports_count' => 6, 'total_profit' => 2800],
                ]);

                $profitRanges = [
                    'صغير (0-100)' => 15,
                    'متوسط (100-500)' => 25,
                    'كبير (500-1000)' => 20,
                    'ضخم (+1000)' => 10,
                ];

                $mostActiveClients = collect([
                    (object)['client_name' => 'عميل نشط 1', 'reports_count' => 12],
                    (object)['client_name' => 'عميل نشط 2', 'reports_count' => 9],
                    (object)['client_name' => 'عميل نشط 3', 'reports_count' => 7],
                ]);

                $reportsOverTime = collect([
                    (object)['date' => now()->subDays(4)->format('Y-m-d'), 'reports_count' => 5],
                    (object)['date' => now()->subDays(3)->format('Y-m-d'), 'reports_count' => 8],
                    (object)['date' => now()->subDays(2)->format('Y-m-d'), 'reports_count' => 3],
                    (object)['date' => now()->subDays(1)->format('Y-m-d'), 'reports_count' => 12],
                    (object)['date' => now()->format('Y-m-d'), 'reports_count' => 7],
                ]);

                $profitsByCurrency = [
                    'KWD' => 5000,
                    'SAR' => 15000,
                    'USD' => 2000,
                ];

                $avgProfitByType = [
                    'visa' => 150,
                    'flight' => 250,
                    'transport' => 80,
                    'hotel' => 320,
                    'land_trip' => 120,
                ];

                $statusDistribution = [
                    'completed' => 45,
                    'draft' => 15
                ];

                $totalProfitByCurrency = $profitsByCurrency;
                $totalProfit = array_sum($profitsByCurrency);
            }

            return view('admin.operation-reports.charts', compact(
                'profitsByType',
                'profitsByTypeAndCurrency', // ✅ أضف هذا للفيو
                'profitsByCurrency',
                'reportsOverTime',
                'topClients',
                'topCompanies',
                'profitRanges',
                'avgProfitByType',
                'mostActiveClients',
                'statusDistribution',
                'totalReports',
                'totalProfitByCurrency',
                'totalClients',
                'totalCompanies',
                'totalProfit',
                'avgProfitPerReport'
            ));
        } catch (\Exception $e) {
            Log::error('❌ خطأ في charts: ' . $e->getMessage());

            return view('admin.operation-reports.charts')->with([
                'profitsByType' => ['visa' => 0, 'flight' => 0, 'transport' => 0, 'hotel' => 0, 'land_trip' => 0],
                'reportsOverTime' => collect([]),
                'topClients' => collect([]),
                'topCompanies' => collect([]),
                'profitRanges' => ['صغير (0-100)' => 0, 'متوسط (100-500)' => 0, 'كبير (500-1000)' => 0, 'ضخم (+1000)' => 0],
                'avgProfitByType' => ['visa' => 0, 'flight' => 0, 'transport' => 0, 'hotel' => 0, 'land_trip' => 0],
                'mostActiveClients' => collect([]),
                'profitsByCurrency' => [],
                'statusDistribution' => [],
                'totalReports' => 0,
                'totalProfitByCurrency' => [],
                'totalClients' => 0,
                'totalCompanies' => 0,
                'totalProfit' => 0,
                'avgProfitPerReport' => 0
            ]);
        }
    }










    /**
     * ✅ دالة جديدة: حساب الأرباح حسب النوع والعملة (نفس طريقة index)
     */
    private function calculateProfitsByTypeAndCurrency()
    {
        $profitsByType = [
            'visa' => ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0],
            'flight' => ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0],
            'transport' => ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0],
            'hotel' => ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0],
            'land_trip' => ['KWD' => 0, 'SAR' => 0, 'USD' => 0, 'EUR' => 0],
        ];

        // ✅ جمع أرباح التأشيرات حسب العملة
        $visaProfits = DB::table('booking_report_visas')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($visaProfits as $profit) {
            if (isset($profitsByType['visa'][$profit->currency])) {
                $profitsByType['visa'][$profit->currency] = $profit->total_profit;
            }
        }

        // ✅ جمع أرباح الطيران حسب العملة
        $flightProfits = DB::table('booking_report_flights')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($flightProfits as $profit) {
            if (isset($profitsByType['flight'][$profit->currency])) {
                $profitsByType['flight'][$profit->currency] = $profit->total_profit;
            }
        }

        // ✅ جمع أرباح النقل حسب العملة
        $transportProfits = DB::table('booking_report_transports')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($transportProfits as $profit) {
            if (isset($profitsByType['transport'][$profit->currency])) {
                $profitsByType['transport'][$profit->currency] = $profit->total_profit;
            }
        }

        // ✅ جمع أرباح الفنادق حسب العملة
        $hotelProfits = DB::table('booking_report_hotels')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($hotelProfits as $profit) {
            if (isset($profitsByType['hotel'][$profit->currency])) {
                $profitsByType['hotel'][$profit->currency] = $profit->total_profit;
            }
        }

        // ✅ جمع أرباح الرحلات البرية حسب العملة
        $landTripProfits = DB::table('booking_report_land_trips')
            ->select('currency', DB::raw('SUM(profit) as total_profit'))
            ->groupBy('currency')
            ->get();

        foreach ($landTripProfits as $profit) {
            if (isset($profitsByType['land_trip'][$profit->currency])) {
                $profitsByType['land_trip'][$profit->currency] = $profit->total_profit;
            }
        }

        // ✅ إزالة العملات التي لا تحتوي على أرباح
        foreach ($profitsByType as $type => $currencies) {
            $profitsByType[$type] = array_filter($currencies, function ($value) {
                return $value > 0;
            });
        }

        return $profitsByType;
    }
}
