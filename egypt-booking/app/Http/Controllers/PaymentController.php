<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\AccountingService;
use App\Http\Controllers\VoucherController;
use Illuminate\Http\Request as HttpRequest;

class PaymentController extends Controller
{
    public function destroy(Payment $payment)
    {
        $this->checkAdmin();
        $booking = $payment->booking;

         DB::beginTransaction();
        try {

            if ($payment->receipt_image && Storage::disk('public')->exists($payment->receipt_image)) {
                Storage::disk('public')->delete($payment->receipt_image);
            }

            AccountingService::onPaymentDeleted($payment); 
            $payment->delete();
            DB::commit();
            return redirect()->route('bookings.show', $booking)
                             ->with('success', '🗑️ تم حذف الدفعة');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'خطأ في حذف الدفعة: ' . $e->getMessage()]);
        }
    }

     private function checkAdmin()
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'غير مصرح');
        }
    }
}