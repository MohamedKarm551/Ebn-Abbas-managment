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
        // بناء الاستعلام الأساسي للشركات
        $query = Company::query();

        // فلترة حسب الوكيل إذا تم تمرير agent_id
        if ($request->filled('agent_id')) {
            $query->whereHas('landTripBookings.landTrip', function ($q) use ($request) {
                $q->where('agent_id', $request->agent_id);
            });
        }

        // إضافة عدد الحجوزات مع مراعاة فلتر الوكيل
        $query->withCount(['landTripBookings as bookings_count' => function ($q) use ($request) {
            if ($request->filled('agent_id')) {
                $q->whereHas('landTrip', function ($subQ) use ($request) {
                    $subQ->where('agent_id', $request->agent_id);
                });
            }
        }]);

        // تحميل الحجوزات الأخيرة (مع فلتر الوكيل إن وجد)
        $query->with(['landTripBookings' => function ($q) use ($request) {
            $q->latest();
            if ($request->filled('agent_id')) {
                $q->whereHas('landTrip', function ($subQ) use ($request) {
                    $subQ->where('agent_id', $request->agent_id);
                });
            }
            $q->take(5);
        }]);

        // فلتر البحث بالاسم
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // فلتر تاريخ البداية
        if ($request->filled('start_date')) {
            $query->whereHas('landTripBookings', function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
                if ($request->filled('agent_id')) {
                    $q->whereHas('landTrip', function ($subQ) use ($request) {
                        $subQ->where('agent_id', $request->agent_id);
                    });
                }
            });
        }

        // فلتر تاريخ النهاية
        if ($request->filled('end_date')) {
            $query->whereHas('landTripBookings', function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
                if ($request->filled('agent_id')) {
                    $q->whereHas('landTrip', function ($subQ) use ($request) {
                        $subQ->where('agent_id', $request->agent_id);
                    });
                }
            });
        }

        // تنفيذ الاستعلام وجلب الشركات بعد تطبيق الفلاتر
        $companies = $query->get();

        // دالة تُستخدم لتطبيق فلاتر التاريخ على الاستعلامات
        $applyDateFilters = function ($q) use ($request) {
            if ($request->filled('start_date')) {
                $q->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $q->whereDate('created_at', '<=', $request->end_date);
            }
        };

        // حساب بيانات كل شركة على حدة (أحدث الحجوزات، المبالغ، المدفوعات)
        $companies->each(function ($company) use ($request, $applyDateFilters) {
            // أحدث 5 حجوزات
            $bookingsQuery = $company->landTripBookings()->latest();
            if ($request->filled('agent_id')) {
                $bookingsQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
            }
            $applyDateFilters($bookingsQuery);
            $company->recent_bookings = $bookingsQuery->take(5)->get();

            // إجمالي المبالغ المستحقة حسب العملة
            $sarQuery = $company->landTripBookings()->where('currency', 'SAR');
            $kwdQuery = $company->landTripBookings()->where('currency', 'KWD');

            if ($request->filled('agent_id')) {
                $sarQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
                $kwdQuery->whereHas('landTrip', function ($q) use ($request) {
                    $q->where('agent_id', $request->agent_id);
                });
            }
            // إجمالي المدفوعات من جدول landtrips_company_payments
            $paidBase = $company->landTripsCompanyPayments();
            if ($request->filled('agent_id')) {
                $paidBase->where('agent_id', $request->agent_id);
            }


            $applyDateFilters($sarQuery);
            $applyDateFilters($kwdQuery);

            $company->total_sar = (float) $sarQuery->sum('amount_due_from_company');
            $company->total_kwd = (float) $kwdQuery->sum('amount_due_from_company');

            // إجمالي المدفوعات
            $company->paid_sar = (float) (clone $paidBase)->where('currency', 'SAR')->sum('amount');
            $company->paid_kwd = (float) (clone $paidBase)->where('currency', 'KWD')->sum('amount');
        });

        // بناء الاستعلام الأساسي للحجوزات لإجماليات الصفحة
        $ltbBase = DB::table('land_trip_bookings as ltb')
            ->join('land_trips as lt', 'ltb.land_trip_id', '=', 'lt.id');

        if ($request->filled('agent_id')) {
            $ltbBase->where('lt.agent_id', $request->agent_id);
        }

        // لو فيه بحث بالاسم نحتاج نربط مع جدول الشركات
        if ($request->filled('search')) {
            $ltbBase->join('companies as c', 'ltb.company_id', '=', 'c.id')
                ->where('c.name', 'like', '%' . $request->search . '%');
        }

        // تطبيق فلاتر التاريخ على الاستعلام العام
        if ($request->filled('start_date')) {
            $ltbBase->whereDate('ltb.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $ltbBase->whereDate('ltb.created_at', '<=', $request->end_date);
        }

        // إذا كان الحقل deleted_at موجود وتستخدم SoftDeletes فعّل السطر التالي
        // $ltbBase->whereNull('ltb.deleted_at');

        // إجمالي عدد الحجوزات
        $totalBookings = (clone $ltbBase)->count();

        // // إجمالي المبالغ حسب العملة
        // $totalsByCurrency = (clone $ltbBase)
        //     ->select('ltb.currency', DB::raw('SUM(COALESCE(ltb.amount_due_from_company, 0)) as total'))
        //     ->groupBy('ltb.currency')
        //     ->get()
        //     ->keyBy('currency');

        // $totalAmountSAR = (float) ($totalsByCurrency['SAR']->total ?? 0);
        // $totalAmountKWD = (float) ($totalsByCurrency['KWD']->total ?? 0);
        // ✅ اجمع إجماليات الصفحة مباشرة من نفس الشركات المعروضة
        $totalBookings   = (int) $companies->sum('bookings_count');
        $totalAmountSAR  = (float) $companies->sum('total_sar');
        $totalAmountKWD  = (float) $companies->sum('total_kwd');

        // جلب الوكيل إذا تم تمرير agent_id
        $agent = $request->filled('agent_id') ? Agent::find($request->agent_id) : null;

        // عرض النتائج في الصفحة
        return view('admin.companies.bookings', compact(
            'companies',
            'totalBookings',
            'totalAmountSAR',
            'totalAmountKWD',
            'agent'
        ));
    }
}
