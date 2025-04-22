<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Company;
use App\Models\Agent;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\ArchivedBooking; // <--- 2. نضيف ArchivedBooking
use App\Models\EditLog; // فوق في أول الملف
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArchivedBookingsExport;


class AdminController extends Controller
{

    public function notifications()
    {
        $notifications = Notification::latest()->paginate(20); // عرض الإشعارات الأحدث أولاً آخر 20 
        return view('admin.notifications', compact('notifications'));
    }
    public function markNotificationRead($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        $notification->is_read = true;
        $notification->save();

        return redirect()->back()->with('success', 'تم تعليم الإشعار كمقروء');
    }
    public function markAllNotificationsRead()
    {
        $notifications = \App\Models\Notification::where('is_read', false)->get();
        foreach ($notifications as $notification) {
            $notification->is_read = true;
            $notification->save();
        }

        return redirect()->back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة');
    }
    public function employees()
    {
        $employees = Employee::all();
        return view('admin.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Employee::create(['name' => $request->name]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت 
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة موظف جديد : {$request->name} ,",
            'type' => 'جديد',
        ]);
        return redirect()->back()->with('success', 'تم إضافة الموظف بنجاح!');
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف موظف   : {$employee->name} ,",
            'type' => 'عملية حذف',
        ]);

        return redirect()->back()->with('success', 'تم حذف الموظف بنجاح!');
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:employees,name,' . $employee->id,
        ]);
        $oldName = $employee->name;
        $employee->update(['name' => $request->name]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل اسم موظف   :{$oldName} إلى: {  $employee->name} ,",
            'type' => 'تحديث اسم',
        ]);
        return response()->json(['success' => true]);
    }

    public function companies()
    {
        $companies = Company::all();
        return view('admin.companies', compact('companies'));
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
        ]);

        Company::create(['name' => $request->name]);
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة شركة: {  $request->name} ,",
            'type' => 'شركة جديدة  ',
        ]);
        return redirect()->route('admin.companies')->with('success', 'تم إضافة الشركة بنجاح!');
    }

    public function editCompany($id)
    {
        $company = Company::findOrFail($id);
        return view('admin.edit-company', compact('company'));
    }

    public function updateCompany(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
        ]);
        $oldName = $company->name;
        $company->update(['name' => $request->name]);
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تحديث اسم شركة  {$oldName}  إلى {  $request->name} ,",
            'type' => 'تحديث اسم اشركة ',
        ]);
        return redirect()->route('admin.companies')->with('success', 'تم تعديل اسم الشركة بنجاح!');
    }

    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف شركة {$company->name},",
            'type' => 'عملية حذف شركة!!!',
        ]);
        return redirect()->route('admin.companies')->with('success', 'تم حذف الشركة بنجاح!');
    }

    public function agents()
    {
        $agents = Agent::all();
        return view('admin.agents', compact('agents'));
    }

    public function storeAgent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:agents,name',
        ]);

        Agent::create(['name' => $request->name]);
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة جهة حجز: {  $request->name} ,",
            'type' => 'جهة حجز جديدة  ',
        ]);
        return redirect()->route('admin.agents')->with('success', 'تم إضافة جهة الحجز بنجاح!');
    }

    public function editAgent($id)
    {
        $agent = Agent::findOrFail($id);
        return view('admin.edit-agent', compact('agent'));
    }

    public function updateAgent(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $oldName = $agent->name;
        $request->validate([
            'name' => 'required|string|max:255|unique:agents,name,' . $agent->id,
        ]);

        $agent->update(['name' => $request->name]);

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تحديث   جهة حجز  {$oldName}  إلى {  $request->name} ,",
            'type' => 'تحديث  اسم جهة حجز',
        ]);
        return redirect()->route('admin.agents')->with('success', 'تم تعديل جهة الحجز بنجاح!');
    }

    public function deleteAgent($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->delete();

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف جهة ججز  {$agent->name},",
            'type' => 'عملية حذف شركة!!!',
        ]);
        return redirect()->route('admin.agents')->with('success', 'تم حذف جهة الحجز بنجاح!');
    }

    public function archivedBookings(Request $request)
    {
        $query = Booking::with(['company', 'employee', 'agent', 'hotel'])
            ->where('cost_price', 0)
            ->where('sale_price', 0);

        // فلتر البحث النصي
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('client_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('employee', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('company', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('agent', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('hotel', function ($subQ) use ($searchTerm) {
                        $subQ->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // فلترة التواريخ
        $startDate = null;
        $endDate = null;
        $startDateFilled = $request->filled('start_date');
        $endDateFilled = $request->filled('end_date');

        if ($startDateFilled) {
            try {
                $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('start_date'))->startOfDay();
            } catch (\Exception $e) {
                $startDateFilled = false;
            }
        }
        if ($endDateFilled) {
            try {
                $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('end_date'))->endOfDay();
            } catch (\Exception $e) {
                $endDateFilled = false;
            }
        }

        if ($startDateFilled && $endDateFilled) {
            $query->whereDate('check_in', '<=', $endDate)
                ->whereDate('check_out', '>=', $startDate);
        } elseif ($startDateFilled) {
            $query->whereDate('check_in', '=', $startDate);
        } elseif ($endDateFilled) {
            $query->whereDate('check_out', '=', $endDate->startOfDay());
        }

        // فلترة الشركة
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }
        // فلترة جهة الحجز
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->input('agent_id'));
        }
        // فلترة الفندق
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->input('hotel_id'));
        }
        // فلترة الموظف
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        $query->orderBy('created_at', 'desc');

        // إجماليات (لو محتاجهم زي index)
        $queryForTotals = clone $query;
        $totalCount = $queryForTotals->count();
        $totalDueFromCompany = $queryForTotals->sum('amount_due_from_company') ?? 0;
        $totalPaidByCompany = $queryForTotals->sum('amount_paid_by_company') ?? 0;
        $remainingFromCompany = $totalDueFromCompany - $totalPaidByCompany;

        $totalDueToHotels = 0;
        $totalPaidToHotels = 0;
        $remainingToHotels = 0;
        if (!$request->filled('company_id')) {
            $totalDueToHotels = $queryForTotals->sum('amount_due_to_hotel') ?? 0;
            $totalPaidToHotels = $queryForTotals->sum('amount_paid_to_hotel') ?? 0;
            $remainingToHotels = $totalDueToHotels - $totalPaidToHotels;
        }
        // عدد الحجوزات المؤرشفة (بعد الفلترة)
        $totalArchivedBookingsCount = (clone $query)->count();



        $archivedBookings = $query->paginate(10)->withQueryString();

        // لو الطلب AJAX (للأجاكس)
        if ($request->wantsJson() || $request->ajax()) {
            $paginationLinks = $archivedBookings->appends($request->all())
                ->onEachSide(1)
                ->links('vendor.pagination.bootstrap-4')
                ->toHtml();

            return response()->json([
                'table' => view('admin._archived_table', ['archivedBookings' => $archivedBookings])->render(),
                'pagination' => $paginationLinks,
                'totals' => [
                    'count' => $totalCount,
                    'due_from_company' => $totalDueFromCompany,
                    'paid_by_company' => $totalPaidByCompany,
                    'remaining_from_company' => $remainingFromCompany,
                    'due_to_hotels' => $request->filled('company_id') ? null : $totalDueToHotels,
                    'paid_to_hotels' => $request->filled('company_id') ? null : $totalPaidToHotels,
                    'remaining_to_hotels' => $request->filled('company_id') ? null : $remainingToHotels,
                ]
            ]);
        }

        // نفس منطق الجدول العادي
        return view('admin.archived_bookings', [
            'archivedBookings' => $archivedBookings,
            'totalBookingsCount' => $totalCount,
            'totalDueFromCompany' => $totalDueFromCompany,
            'totalPaidByCompany' => $totalPaidByCompany,
            'remainingFromCompany' => $remainingFromCompany,
            'totalDueToHotels' => $totalDueToHotels,
            'totalPaidToHotels' => $totalPaidToHotels,
            'remainingToHotels' => $remainingToHotels,
            'totalArchivedBookingsCount' => $totalArchivedBookingsCount,

        ]);
    }
    // ... (use Maatwebsite\Excel\Facades\Excel; use App\Exports\ArchivedBookingsExport; use Illuminate\Http\Request;)

    public function exportArchivedBookings(Request $request)
    {
        // اسم الملف الذي سيتم تحميله
        $fileName = 'archived_bookings_' . now()->format('Ymd_His') . '.xlsx';

        // استخدم الـ Export class مع تمرير الـ request الحالي للحفاظ على الفلاتر
        return Excel::download(new ArchivedBookingsExport($request), $fileName);
    }
}
