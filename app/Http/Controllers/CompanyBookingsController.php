<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyBookingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        // فلترة الشركات حسب الوكيل إذا تم تمرير معرّف الوكيل
        if ($request->filled('agent_id')) {
            $agentId = $request->agent_id;
            $query->whereHas('landTripBookings.landTrip', function ($q) use ($agentId) {
                $q->where('agent_id', $agentId);
            });
        }

        // حساب عدد الحجوزات للشركات مع مراعاة فلتر الوكيل
        $query->withCount(['landTripBookings as bookings_count' => function ($q) use ($request) {
            if ($request->filled('agent_id')) {
                $q->whereHas('landTrip', function ($subQ) use ($request) {
                    $subQ->where('agent_id', $request->agent_id);
                });
            }
        }]);

        // تحميل حجوزات الشركات مع مراعاة فلتر الوكيل
        $query->with(['landTripBookings' => function ($q) use ($request) {
            $q->latest();
            if ($request->filled('agent_id')) {
                $q->whereHas('landTrip', function ($subQ) use ($request) {
                    $subQ->where('agent_id', $request->agent_id);
                });
            }
            $q->take(5);
        }]);


        // فلاتر البحث الأخرى
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // فلتر التاريخ مع مراعاة فلتر الوكيل
        if ($request->has('start_date') && $request->start_date) {
            $startDate = $request->start_date;
            $query->whereHas('landTripBookings', function ($q) use ($startDate, $request) {
                $q->whereDate('created_at', '>=', $startDate);
                if ($request->filled('agent_id')) {
                    // تعديل هنا: استخدام العلاقة بدلاً من الحقل المباشر
                    $q->whereHas('landTrip', function ($subQ) use ($request) {
                        $subQ->where('agent_id', $request->agent_id);
                    });
                }
            });
        }

        if ($request->has('end_date') && $request->end_date) {
            $endDate = $request->end_date;
            $query->whereHas('landTripBookings', function ($q) use ($endDate, $request) {
                $q->whereDate('created_at', '<=', $endDate);
                if ($request->filled('agent_id')) {
                    // تعديل هنا: استخدام العلاقة بدلاً من الحقل المباشر
                    $q->whereHas('landTrip', function ($subQ) use ($request) {
                        $subQ->where('agent_id', $request->agent_id);
                    });
                }
            });
        }

        // تنفيذ الاستعلام وجلب الشركات
        $companies = $query->get();

        // حساب الإجماليات لكل شركة مع مراعاة فلتر الوكيل
        $companies->each(function ($company) use ($request) {
            // جلب أحدث 5 حجوزات
            $bookingsQuery = $company->landTripBookings()->latest();
            if ($request->filled('agent_id')) {
                // تعديل هنا: استخدام العلاقة بدلاً من الحقل المباشر
                $bookingsQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
            }
            $company->recent_bookings = $bookingsQuery->take(5)->get();

            // حساب المستحقات حسب العملة
            $sarQuery = $company->landTripBookings()->where('currency', 'SAR');
            $kwdQuery = $company->landTripBookings()->where('currency', 'KWD');

            if ($request->filled('agent_id')) {
                // تعديل هنا: استخدام العلاقة بدلاً من الحقل المباشر
                $sarQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
                $kwdQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
            }

            $company->total_sar = $sarQuery->sum('amount_due_from_company');
            $company->total_kwd = $kwdQuery->sum('amount_due_from_company');

            // حساب المدفوعات
            $company->paid_sar = $company->payments()->where('currency', 'SAR')->sum('amount');
            $company->paid_kwd = $company->payments()->where('currency', 'KWD')->sum('amount');
        });

        // إجماليات الصفحة مع مراعاة فلتر الوكيل
        if ($request->filled('agent_id')) {
            // استخدام subquery للحصول على الحجوزات المرتبطة بالوكيل المحدد
            $landTripBookingIds = DB::table('land_trip_bookings')
                ->join('land_trips', 'land_trip_bookings.land_trip_id', '=', 'land_trips.id')
                ->where('land_trips.agent_id', $request->agent_id)
                ->pluck('land_trip_bookings.id');

            $totalBookingsQuery = DB::table('land_trip_bookings')->whereIn('id', $landTripBookingIds);
            $totalAmountSARQuery = DB::table('land_trip_bookings')
                ->whereIn('id', $landTripBookingIds)
                ->where('currency', 'SAR');
            $totalAmountKWDQuery = DB::table('land_trip_bookings')
                ->whereIn('id', $landTripBookingIds)
                ->where('currency', 'KWD');
        } else {
            $totalBookingsQuery = DB::table('land_trip_bookings');
            $totalAmountSARQuery = DB::table('land_trip_bookings')->where('currency', 'SAR');
            $totalAmountKWDQuery = DB::table('land_trip_bookings')->where('currency', 'KWD');
        }

        $totalBookings = $totalBookingsQuery->count();
        $totalAmountSAR = $totalAmountSARQuery->sum('amount_due_from_company');
        $totalAmountKWD = $totalAmountKWDQuery->sum('amount_due_from_company');

        // جلب معلومات الوكيل إذا تم تحديده
        $agent = null;
        if ($request->filled('agent_id')) {
            $agent = Agent::find($request->agent_id);
        }

        return view('admin.companies.bookings', compact(
            'companies',
            'totalBookings',
            'totalAmountSAR',
            'totalAmountKWD',
            'agent'
        ));
    }
}
