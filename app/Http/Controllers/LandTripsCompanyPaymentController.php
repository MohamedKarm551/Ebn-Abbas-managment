<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandTripsCompanyPayment;
use Illuminate\Support\Facades\Auth;

class LandTripsCompanyPaymentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'agent_id'     => 'nullable|exists:agents,id',
            'currency'     => 'required|in:SAR,KWD',
            'amount'       => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $data['employee_id'] = Auth::id();

        LandTripsCompanyPayment::create($data);

        return back()->with('success', 'تم إضافة الدفعة بنجاح');
    }
}