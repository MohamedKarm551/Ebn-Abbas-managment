<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Models\Booking;

class EmployeeController extends Controller
{
    public function index(Request $request) {
        $query = User::whereHas('roles')->latest();
    
        if ($request->filled('name'))
            $query->where('name', 'LIKE', '%' . $request->name . '%');
    
        if ($request->filled('email'))
            $query->where('email', 'LIKE', '%' . $request->email . '%');
    
        if ($request->filled('role'))
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
    
        $employees = $query->paginate(10)->withQueryString();
        $roles = Role::all();
        return view('employees.index', compact('employees', 'roles'));
    }

    public function create() {
        $roles = Role::all();
        return view('employees.create', compact('roles'));
    }

    public function store(Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('employees.index')
            ->with('success', '✅ تم إضافة الموظف');
    }

    public function edit(User $employee) {
        $roles = Role::all();
        return view('employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, User $employee) {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'role' => 'required|exists:roles,name',
        ]);

        $employee->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        $employee->syncRoles($request->role);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $employee->update(['password' => bcrypt($request->password)]);
        }

        return redirect()->route('employees.index')
            ->with('success', '✅ تم تعديل بيانات الموظف');
    }

    public function destroy(User $employee) {
        $employee->delete();
        return redirect()->route('employees.index')
            ->with('success', '🗑️ تم حذف الموظف');
    }

    public function report(User $employee, Request $request) {
        $month = $request->month; // format: 2024-01

       $query = Booking::where('representative_id', $employee->id)
        ->with('trip');

        if ($month) {
            $query->whereYear('created_at', substr($month, 0, 4))
                  ->whereMonth('created_at', substr($month, 5, 2));
        }

        $bookings = $query->latest()->get();

        $byTrip = $bookings->groupBy('trip_id')->map(function($group) {
            return [
                'trip'  => $group->first()->trip,
                'count' => $group->count(),
            ];
        });

        $byMonth = Booking::where('representative_id', $employee->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month,
                         COUNT(*) as count')
            ->groupBy('month')
            ->orderByDesc('month')
            ->get();

        return view('employees.report',
            compact('employee','bookings','byTrip','byMonth','month'));
    }


}