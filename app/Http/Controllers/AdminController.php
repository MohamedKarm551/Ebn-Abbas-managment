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

    public function notifications(Request $request)
    {
        $user = Auth::user(); // جلب المستخدم الحالي
        // *** بداية التعديل: فلترة الإشعارات حسب الدور ***
        $query = Notification::latest(); // نبدأ بالاستعلام الأساسي
             // شوف لو فيه باراميتر 'filter' جاي في الـ URL
             $currentFilter = $request->input('filter');
                // لو فيه فلتر، طبق الشرط بتاعه
                if ($currentFilter) {
                    switch ($currentFilter) {
                        case 'bookings':
                            // فلتر حسب الكلمات المفتاحية للحجوزات في الرسالة
                            $query->where(function ($q) {
                                $q->where('message', 'LIKE', '%حجز%') // كلمة "حجز"
                                  ->orWhere('message', 'LIKE', '%booking%'); // كلمة "booking"
                                  // ممكن تضيف كلمات تانية زي "فاتورة", "voucher" لو بتظهر في إشعارات الحجوزات
                            });
                            break;
                        case 'payments':
                            // فلتر حسب الكلمات المفتاحية للدفعات
                            $query->where(function ($q) {
                                $q->where('message', 'LIKE', '%دفعة%') // كلمة "دفعة"
                                  ->orWhere('message', 'LIKE', '%payment%'); // كلمة "payment"
                                  // ممكن تضيف "سداد", "تحصيل" ...إلخ
                            });
                            break;
                        case 'availabilities':
                            // فلتر حسب الكلمات المفتاحية للإتاحات
                            $query->where(function ($q) {
                                $q->where('message', 'LIKE', '%إتاحة%') // كلمة "إتاحة"
                                  ->orWhere('message', 'LIKE', '%availability%') // كلمة "availability"
                                  ->orWhere('message', 'LIKE', '%allotment%'); // كلمة "allotment"
                            });
                            break;
                        // ممكن تضيف case تانية لأنواع فلاتر تانية لو حبيت
                        // مثال:
                        // case 'users':
                        //     $query->where('message', 'LIKE', '%مستخدم%');
                        //     break;
                    }
                }
        
        
        if ($user->role === 'employee') {
            // لو المستخدم موظف، جيب إشعاراته هو بس
            $query->where('user_id', $user->id);
        }
        // لو المستخدم أدمن، مش هنضيف أي شرط إضافي (هيجيب كله)

        $notifications = $query->paginate(20); // تطبيق الـ pagination على الاستعلام النهائي
        // *** نهاية التعديل ***

        return view('admin.notifications', compact('notifications', 'currentFilter'));
    }

    public function markNotificationRead($id)
    {
        $user = Auth::user();
        $notification = Notification::findOrFail($id);

        // *** بداية التعديل: التحقق من الصلاحية ***
        // نسمح للأدمن أو لصاحب الإشعار فقط بتعليمه كمقروء
        if ($user->role === 'Admin' || $notification->user_id == $user->id) {
            $notification->is_read = true;
            $notification->save();
            return redirect()->back()->with('success', 'تم تعليم الإشعار كمقروء');
        } 
            // لو مش مسموحله، نرجع برسالة خطأ
            return redirect()->back()->with('error', 'ليس لديك الصلاحية لتعليم هذا الإشعار كمقروء.');
    } 
        
    public function markAllNotificationsRead()
    {
        $user = Auth::user();

        // *** بداية التعديل: تحديد الكل حسب الدور ***
        $query = Notification::where('is_read', false);

        if ($user->role === 'employee') {
            // لو موظف، حدد إشعاراته هو بس
            $query->where('user_id', $user->id);
        }
        // لو أدمن، هيحدد كل الإشعارات غير المقروءة في النظام

        $notifications = $query->get();
        // *** نهاية التعديل ***

        foreach ($notifications as $notification) {
            $notification->is_read = true;
            $notification->save();
        }

        return redirect()->back()->with('success', 'تم تعليم جميع الإشعارات المحددة كمقروءة');
    }
    public function employees()
    {
        $employees = Employee::all();
        return view('admin.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:employees,name',
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل الحفظ ***
        $sanitizedName = strip_tags($request->input('name'));

        $employee = Employee::create(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة موظف جديد : {$employee->name} ,", // استخدام الاسم من الموديل بعد الحفظ
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
        $oldName = $employee->name; // حفظ الاسم القديم

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:employees,name,' . $employee->id, // تجاهل الموظف الحالي عند التحقق من التفرد
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل التحديث ***
        $sanitizedName = strip_tags($request->input('name'));

        $employee->update(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل اسم موظف   :{$oldName} إلى: {$employee->name} ,", // استخدام الاسم من الموديل بعد التحديث
            'type' => 'تحديث اسم',
        ]);
        // ملاحظة: إذا كان هذا الطلب يأتي من AJAX كما يوحي return response()->json
        // فتأكد من أن الواجهة تتعامل مع الاستجابة بشكل صحيح.
        // إذا كان نموذجًا عاديًا، قد تحتاج إلى redirect بدلاً من json.
        // سنفترض أنه AJAX بناءً على الكود الأصلي.
        return response()->json(['success' => true, 'newName' => $employee->name]); // إرجاع الاسم الجديد قد يكون مفيدًا
    }

    public function companies()
    {
        $companies = Company::all();
        return view('admin.companies', compact('companies'));
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:companies,name',
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل الحفظ ***
        $sanitizedName = strip_tags($request->input('name'));

        $company = Company::create(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة شركة: {$company->name} ,", // استخدام الاسم من الموديل بعد الحفظ
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
        $oldName = $company->name; // حفظ الاسم القديم

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:companies,name,' . $company->id, // تجاهل الشركة الحالية
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل التحديث ***
        $sanitizedName = strip_tags($request->input('name'));

        $company->update(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تحديث اسم شركة  {$oldName}  إلى {$company->name} ,", // استخدام الاسم من الموديل بعد التحديث
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
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:agents,name',
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل الحفظ ***
        $sanitizedName = strip_tags($request->input('name'));

        $agent = Agent::create(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة جهة حجز: {$agent->name} ,", // استخدام الاسم من الموديل بعد الحفظ
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
        $oldName = $agent->name; // حفظ الاسم القديم

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:agents,name,' . $agent->id, // تجاهل الجهة الحالية
                // regex: يسمح بالحروف (Unicode)، الأرقام، المسافات، الشرطة، القوسين
                'regex:/^[\pL\pN\s\-()]+$/u'
            ],
        ]);

        // *** تطبيق التنقية هنا قبل التحديث ***
        $sanitizedName = strip_tags($request->input('name'));

        $agent->update(['name' => $sanitizedName]); // استخدام الاسم المنقّى

        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تحديث   جهة حجز  {$oldName}  إلى {$agent->name} ,", // استخدام الاسم من الموديل بعد التحديث
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
            'type' => 'عملية حذف شركة!!!', // ربما تغيير النوع هنا؟
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
    public function archivedAutocomplete(Request $request)
    {
        $term = $request->input('term'); // بناخد الكلمة اللي المستخدم كتبها
        $suggestions = collect(); // بنجهز مصفوفة فاضية للاقتراحات

        if ($term) { // لو المستخدم كتب حاجة
            $term = '%' . $term . '%'; // بنحط علامات % عشان البحث الجزئي

            // بنعرف الشروط بتاعة الحجوزات المؤرشفة (زي ما هي في دالة archivedBookings)
           
            $archiveConditions = function ($query) {
                $query->where('cost_price', 0)->where('sale_price', 0);
            };

            // 1. البحث في اسم العميل في الحجوزات المؤرشفة (ده سليم)
            $clientSuggestions = Booking::where($archiveConditions) // تطبيق شروط الأرشفة هنا
                ->where('client_name', 'LIKE', $term)
                ->distinct()->limit(5)->pluck('client_name');
            $suggestions = $suggestions->merge($clientSuggestions);

            // *** بداية التعديل: تطبيق الشروط جوه whereHas ***

            // 2. البحث في أسماء الشركات المرتبطة بالحجوزات المؤرشفة
            $companySuggestions = Company::whereHas('bookings', function ($query) use ($archiveConditions) {
                // بنطبق شروط الأرشفة على الحجوزات اللي بنبحث فيها
                $archiveConditions($query);
            })
            ->where('name', 'LIKE', $term) // بنبحث عن اسم الشركة نفسها
            ->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($companySuggestions);

            // 3. البحث في أسماء جهات الحجز المرتبطة بالحجوزات المؤرشفة
            // السطر 511 (تقريباً)
            $agentSuggestions = Agent::whereHas('bookings', function ($query) use ($archiveConditions) {
                $archiveConditions($query); // تطبيق الشروط هنا
            })
            ->where('name', 'LIKE', $term)
            ->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($agentSuggestions);

            // 4. البحث في أسماء الفنادق المرتبطة بالحجوزات المؤرشفة
            // السطر 516 (تقريباً)
            $hotelSuggestions = Hotel::whereHas('bookings', function ($query) use ($archiveConditions) {
                $archiveConditions($query); // تطبيق الشروط هنا
            })
            ->where('name', 'LIKE', $term)
            ->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($hotelSuggestions);

            // 5. البحث في أسماء الموظفين المرتبطين بالحجوزات المؤرشفة
            // السطر 521 (تقريباً)
            $employeeSuggestions = Employee::whereHas('bookings', function ($query) use ($archiveConditions) {
                $archiveConditions($query); // تطبيق الشروط هنا
            })
            ->where('name', 'LIKE', $term)
            ->limit(5)->pluck('name');
            $suggestions = $suggestions->merge($employeeSuggestions);

            // *** نهاية التعديل ***

            // بنشيل التكرار وناخد أول 10 اقتراحات بس
            $suggestions = $suggestions->unique()->take(10)->values();
        }

        // بنرجع الاقتراحات كـ JSON عشان الجافاسكريبت يفهمها
        return response()->json($suggestions);
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
