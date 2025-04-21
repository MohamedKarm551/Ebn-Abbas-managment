<?php 

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\ArchivedBooking; // <--- 1. نضيف ArchivedBooking
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // <--- 2. نضيف DB
use Illuminate\Support\Facades\Log; // <--- 3. نضيف Log

class HotelController extends Controller
{
    public function index()
    {
        $hotels = Hotel::all();
        return view('admin.hotels', compact('hotels'));
    }

    public function create()
    {
        return view('hotels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:hotels,name',
        ]);

        Hotel::create(['name' => $request->name]);
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "إضافة فندق جديد : {$request->name} ,",
            'type' => 'جديد',
        ]);
        return redirect()->route('admin.hotels')->with('success', 'تم إضافة الفندق بنجاح!');
    }

    public function edit($id)
    {
        $hotel = Hotel::findOrFail($id);
        return view('admin.edit-hotel', compact('hotel'));
    }

    public function update(Request $request, $id)
    {
        $hotel = Hotel::findOrFail($id);
        $oldName = $hotel->name; // حفظ الاسم القديم
        $request->validate([
            'name' => 'required|string|max:255|unique:hotels,name,' . $hotel->id,
        ]);

        $hotel->update(['name' => $request->name]);
        // هنعمل هنا إشعار للأدمن يشوف إن العملية تمت
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "تعديل اسم فندق   :{$oldName} إلى: {  $hotel->name} ,",
            'type' => 'تحديث اسم',
        ]);
        return redirect()->route('admin.hotels')->with('success', 'تم تعديل الفندق بنجاح!');
    }

    public function destroy($id)
    {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        Notification::create([
            'user_id' => Auth::user()->id,
            'message' => "حذف فندق   : {$hotel->name} ,",
            'type' => 'عملية حذف',
        ]);
        return redirect()->route('admin.hotels')->with('success', 'تم حذف الفندق بنجاح!');
    }
}