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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function employees()
    {
        $employees = Employee::all();
        return view('admin.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Employee::create(['name' => $request->name]);
        return redirect()->back()->with('success', 'تم إضافة الموظف بنجاح!');
    }

    public function deleteEmployee($id)
    {
        Employee::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'تم حذف الموظف بنجاح!');
    }

    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:employees,name,' . $employee->id,
        ]);

        $employee->update(['name' => $request->name]);

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

        $company->update(['name' => $request->name]);

        return redirect()->route('admin.companies')->with('success', 'تم تعديل اسم الشركة بنجاح!');
    }

    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

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

        $request->validate([
            'name' => 'required|string|max:255|unique:agents,name,' . $agent->id,
        ]);

        $agent->update(['name' => $request->name]);

        return redirect()->route('admin.agents')->with('success', 'تم تعديل جهة الحجز بنجاح!');
    }

    public function deleteAgent($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->delete();

        return redirect()->route('admin.agents')->with('success', 'تم حذف جهة الحجز بنجاح!');
    }

    public function archivedBookings()
    {
        $archivedBookings = Booking::where('cost_price', 0)
            ->where('sale_price', 0)
            ->paginate(20);

        foreach ($archivedBookings as $booking) {
            // آخر رقم كان في cost_price قبل ما يتغير لصفر
            $lastCost = EditLog::where('booking_id', $booking->id)
                ->where('field', 'cost_price')
                ->where('new_value', 0)
                ->orderByDesc('created_at')
                ->first();

            $booking->old_cost_price = $lastCost ? $lastCost->old_value : null;

            // آخر رقم كان في sale_price قبل ما يتغير لصفر
            $lastSale = EditLog::where('booking_id', $booking->id)
                ->where('field', 'sale_price')
                ->where('new_value', 0)
                ->orderByDesc('created_at')
                ->first();

            $booking->old_sale_price = $lastSale ? $lastSale->old_value : null;
        }

        return view('admin.archived_bookings', compact('archivedBookings'));
    }
}
