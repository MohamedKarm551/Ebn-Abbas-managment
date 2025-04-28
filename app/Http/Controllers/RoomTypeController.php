<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomType; // تأكد من وجود هذا النموذج

class RoomTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roomTypes = RoomType::latest()->paginate(10); // استرجاع 10 أنواع في كل صفحة، الأحدث أولاً
        return view('admin.room_types.index', compact('roomTypes')); // تأكد من وجود هذا العرض
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('admin.room_types.index');    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_name' => 'required|string|max:255|unique:room_types,room_type_name',
        ], [
            'room_type_name.required' => 'اسم نوع الغرفة مطلوب.',
            'room_type_name.unique' => 'هذا النوع من الغرف موجود بالفعل.',
            'room_type_name.max' => 'اسم نوع الغرفة طويل جداً.',
        ]);

        RoomType::create($validated);

        return redirect()->route('admin.room_types.index')->with('success', 'تم إضافة نوع الغرفة بنجاح!');
    }

    /**
     * Display the specified resource.
     */
    public function show(RoomType $roomType)
    {
        return redirect()->route('admin.room_types.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RoomType $roomType)
    {
        return view('admin.room_types.edit', compact('roomType')); // تأكد من وجود هذا العرض
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'room_type_name' => 'required|string|max:255|unique:room_types,room_type_name,' . $roomType->id,
        ], [
            'room_type_name.required' => 'اسم نوع الغرفة مطلوب.',
            'room_type_name.unique' => 'هذا النوع من الغرف موجود بالفعل.',
            'room_type_name.max' => 'اسم نوع الغرفة طويل جداً.',
        ]);

        $roomType->update($validated);

        return redirect()->route('admin.room_types.index')->with('success', 'تم تعديل نوع الغرفة بنجاح!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RoomType $roomType)
    {
        try {
            $roomType->delete();
            return redirect()->route('admin.room_types.index')->with('success', 'تم حذف نوع الغرفة بنجاح!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.room_types.index')->with('error', 'لا يمكن حذف نوع الغرفة لوجود بيانات مرتبطة به.');
        } catch (\Exception $e) {
            return redirect()->route('admin.room_types.index')->with('error', 'حدث خطأ أثناء محاولة حذف نوع الغرفة.');
        }
    }
}
