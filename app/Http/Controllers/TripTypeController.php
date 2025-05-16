<?php

namespace App\Http\Controllers;

use App\Models\TripType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class TripTypeController extends Controller
{
    public function index()
    {
        $tripTypes = TripType::all();
    return view('admin.trip-types.index', compact('tripTypes'));
    }

    public function create()
    {
        return view('admin.trip-types.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:trip_types',
            'description' => 'nullable|string',
        ], [
            'name.required' => 'يجب إدخال اسم نوع الرحلة',
            'name.unique' => 'اسم نوع الرحلة موجود مسبقاً',
        ]);

        $tripType = TripType::create($validatedData);

        // إنشاء إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "إضافة نوع رحلة جديد: {$tripType->name}",
            'type' => 'إضافة',
        ]);

        return redirect()->route('admin.trip-types.index')->with('success', 'تم إضافة نوع الرحلة بنجاح');
    }

    public function edit(TripType $tripType)
    {
    return view('admin.trip-types.edit', compact('tripType'));
    }

    public function update(Request $request, TripType $tripType)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:trip_types,name,'.$tripType->id,
            'description' => 'nullable|string',
        ], [
            'name.required' => 'يجب إدخال اسم نوع الرحلة',
            'name.unique' => 'اسم نوع الرحلة موجود مسبقاً',
        ]);

        $oldName = $tripType->name;
        $tripType->update($validatedData);

        // إنشاء إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "تعديل نوع رحلة: {$oldName} إلى {$tripType->name}",
            'type' => 'تعديل',
        ]);

        return redirect()->route('admin.trip-types.index')->with('success', 'تم تحديث نوع الرحلة بنجاح');
    }

    public function destroy(TripType $tripType)
    {
        // التحقق إذا كان هناك رحلات مرتبطة بهذا النوع
        if ($tripType->landTrips()->exists()) {
            return redirect()->route('admin.trip-types.index')
                ->with('error', 'لا يمكن حذف نوع الرحلة لوجود رحلات مرتبطة به');
        }

        $name = $tripType->name;
        $tripType->delete();

        // إنشاء إشعار
        Notification::create([
            'user_id' => Auth::id(),
            'message' => "حذف نوع رحلة: {$name}",
            'type' => 'حذف',
        ]);

        return redirect()->route('admin.trip-types.index')->with('success', 'تم حذف نوع الرحلة بنجاح');
    }
}
