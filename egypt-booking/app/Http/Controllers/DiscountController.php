<?php
namespace App\Http\Controllers;

use App\Models\Discount;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    // المندوب يضيف خصم
    public function store(Request $request, Booking $booking) {
        $request->validate([
            'amount'      => 'required|numeric|min:1|max:'.$booking->finalPrice(),
            'description' => 'required|string',
        ]);

        Discount::create([
            'booking_id'  => $booking->id,
            'amount'      => $request->amount,
            'description' => $request->description,
            'status'      => 'pending',
            'created_by'  => auth()->id(),
        ]);

        return back()->with('success',
            '⏳ تم إرسال الخصم — في انتظار موافقة الأدمن');
    }

    // الأدمن يوافق
    public function approve(Discount $discount) {
        
        $this->checkAdmin();
        if ($discount->status !== 'pending') {
            return back()->withErrors(['error' => 'هذا الخصم تمت معالجته مسبقاً']);
        }
         DB::transaction(function () use ($discount) {
            $discount->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

        AccountingService::onDiscountApproved($discount);
    });

        return back()->with('success','✅ تم اعتماد الخصم');
    }

    // الأدمن يرفض
    public function reject(Discount $discount) {
        $this->checkAdmin();
        $discount->update(['status' => 'rejected']);
        return back()->with('success', '❌ تم رفض الخصم');
    }

    // صفحة الخصومات المعلقة للأدمن
    public function pendingIndex() {
        $this->checkAdmin();

        $discounts = Discount::with(['booking.trip','createdBy'])
            ->where('status','pending')
            ->latest()
            ->paginate(15);

        return view('discounts.pending', compact('discounts'));
    }


    public function edit(Discount $discount)
    {
        $this->checkAdmin();
        return view('discounts.edit', compact('discount'));
    }

    public function update(Request $request, Discount $discount)
    {
        $this->checkAdmin();
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);

        $oldAmount = $discount->amount;
        $oldStatus = $discount->status;

        DB::beginTransaction();
         try {
        $discount->update([
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        // إذا كان الخصم معتمداً بالفعل، نعدل القيد المحاسبي
        if ($oldStatus === 'approved') {
            AccountingService::onDiscountUpdated($discount, $oldAmount);
        }

        DB::commit();
        return redirect()->route('bookings.show', $discount->booking)
                         ->with('success', '✅ تم تعديل الخصم');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function destroy(Discount $discount)
    {
        $this->checkAdmin();
        $booking = $discount->booking;
        $status = $discount->status;
       DB::beginTransaction();
        try {
        if ($status === 'approved') {
            AccountingService::onDiscountDeleted($discount);
        }
        $discount->delete();
        DB::commit();
        return redirect()->route('bookings.show', $booking)
                         ->with('success', '🗑️ تم حذف الخصم');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطأ في الحذف: ' . $e->getMessage()]);
        }
    }

    private function checkAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'غير مصرح');
        }
    }

}