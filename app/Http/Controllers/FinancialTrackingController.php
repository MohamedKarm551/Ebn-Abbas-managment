<?php

namespace App\Http\Controllers;

use App\Models\BookingFinancialTracking;
use App\Models\Booking;
use App\Factories\PaymentStatusFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Controller للمتابعة المالية للحجوزات
 * 
 * يوفر واجهة API لإدارة المتابعة المالية
 * يستخدم Design Patterns للتعامل مع حالات السداد المختلفة
 * 
 * مسار الملف: app/Http/Controllers/FinancialTrackingController.php
 */
class FinancialTrackingController extends Controller
{
    /**
     * عرض بيانات المتابعة المالية لحجز معين
     * 
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Booking $booking)
    {
        try {
            // التحقق من وجود المتابعة المالية أو إنشاء واحدة جديدة
            $tracking = $booking->financialTracking;

            if (!$tracking) {
                // إنشاء متابعة مالية جديدة بالقيم الافتراضية
                $tracking = new BookingFinancialTracking([
                    'booking_id' => $booking->id,
                    'agent_payment_status' => 'not_paid',
                    'company_payment_status' => 'not_paid',
                    'agent_payment_amount' => 0,
                    'company_payment_amount' => 0,
                    'priority_level' => 'medium',
                    'follow_up_date' => Carbon::now()->addDays(7), // متابعة بعد أسبوع
                    'last_updated_by' => Auth::id()
                ]);
            }

            // تحضير البيانات للإرسال
            $response = [
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'voucher_number' => $booking->voucher_number,
                    'client_name' => $booking->client_name,
                    'check_in' => $booking->check_in,
                    'check_out' => $booking->check_out,
                    'currency' => $booking->currency,
                    'agent' => [
                        'id' => $booking->agent->id ?? null,
                        'name' => $booking->agent->name ?? 'غير محدد',
                        'amount_due' => $booking->amount_due_to_hotel ?? 0,
                    ],
                    'company' => [
                        'id' => $booking->company->id ?? null,
                        'name' => $booking->company->name ?? 'غير محدد',
                        'amount_due' => $booking->amount_due_from_company ?? 0,
                    ]
                ],
                'tracking' => [
                    'id' => $tracking->id,
                    'agent_payment_status' => $tracking->agent_payment_status,
                    'agent_payment_amount' => $tracking->agent_payment_amount,
                    'agent_payment_notes' => $tracking->agent_payment_notes,
                    'company_payment_status' => $tracking->company_payment_status,
                    'company_payment_amount' => $tracking->company_payment_amount,
                    'company_payment_notes' => $tracking->company_payment_notes,
                    'payment_deadline' => $tracking->payment_deadline,
                    'follow_up_date' => $tracking->follow_up_date,
                    'priority_level' => $tracking->priority_level,
                    'created_at' => $tracking->created_at,
                    'updated_at' => $tracking->updated_at,
                    'last_updated_by' => $tracking->lastUpdatedBy->name ?? 'غير معروف'
                ],
                'strategies' => [
                    'agent' => [
                        'color' => $tracking->getAgentStatusColor(),
                        'icon' => $tracking->getAgentStatusIcon(),
                        'label' => $tracking->getAgentStatusLabel(),
                        'description' => $tracking->getAgentPaymentStrategy()->getStatusDescription(),
                        'bootstrap_class' => $tracking->getAgentPaymentStrategy()->getBootstrapClass(),
                    ],
                    'company' => [
                        'color' => $tracking->getCompanyStatusColor(),
                        'icon' => $tracking->getCompanyStatusIcon(),
                        'label' => $tracking->getCompanyStatusLabel(),
                        'description' => $tracking->getCompanyPaymentStrategy()->getStatusDescription(),
                        'bootstrap_class' => $tracking->getCompanyPaymentStrategy()->getBootstrapClass(),
                    ]
                ],
                'calculations' => [
                    'agent_payment_percentage' => $tracking->getAgentPaymentPercentage(),
                    'company_payment_percentage' => $tracking->getCompanyPaymentPercentage(),
                    'agent_remaining_amount' => $tracking->getAgentRemainingAmount(),
                    'company_remaining_amount' => $tracking->getCompanyRemainingAmount(),
                ],
                'available_statuses' => PaymentStatusFactory::getAllStatuses(),
                'available_priorities' => [
                    'low' => 'منخفضة',
                    'medium' => 'متوسطة',
                    'high' => 'عالية'
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('خطأ في عرض المتابعة المالية', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في تحميل بيانات المتابعة المالية'
            ], 500);
        }
    }

    /**
     * حفظ أو تحديث بيانات المتابعة المالية
     * 
     * @param Request $request
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Booking $booking)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            // بيانات جهة الحجز
            'agent_payment_status' => 'required|in:not_paid,partially_paid,fully_paid',
            'agent_payment_amount' => 'nullable|numeric|min:0',
            'agent_payment_notes' => 'nullable|string|max:1000',

            // بيانات الشركة
            'company_payment_status' => 'required|in:not_paid,partially_paid,fully_paid',
            'company_payment_amount' => 'nullable|numeric|min:0',
            'company_payment_notes' => 'nullable|string|max:1000',

            // بيانات إضافية
            'payment_deadline' => 'nullable|date|after:today',
            'follow_up_date' => 'nullable|date|after:today',
            'priority_level' => 'required|in:low,medium,high'
        ], [
            // رسائل خطأ مخصصة
            'agent_payment_status.required' => 'حالة السداد لجهة الحجز مطلوبة',
            'agent_payment_status.in' => 'حالة السداد لجهة الحجز غير صحيحة',
            'company_payment_status.required' => 'حالة السداد للشركة مطلوبة',
            'company_payment_status.in' => 'حالة السداد للشركة غير صحيحة',
            'agent_payment_amount.numeric' => 'مبلغ السداد لجهة الحجز يجب أن يكون رقماً',
            'agent_payment_amount.min' => 'مبلغ السداد لجهة الحجز لا يمكن أن يكون سالباً',
            'company_payment_amount.numeric' => 'مبلغ السداد للشركة يجب أن يكون رقماً',
            'company_payment_amount.min' => 'مبلغ السداد للشركة لا يمكن أن يكون سالباً',
            'payment_deadline.after' => 'تاريخ الاستحقاق يجب أن يكون في المستقبل',
            'follow_up_date.after' => 'تاريخ المتابعة يجب أن يكون في المستقبل',
            'priority_level.required' => 'مستوى الأولوية مطلوب',
            'priority_level.in' => 'مستوى الأولوية غير صحيح'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            // بدء معاملة قاعدة البيانات
            DB::beginTransaction();

            // البحث عن المتابعة المالية الموجودة
            $tracking = $booking->financialTracking;

            if ($tracking) {
                // تحديث المتابعة الموجودة
                $result = $this->updateExistingTracking($tracking, $validated, $booking);
            } else {
                // إنشاء متابعة جديدة
                $result = $this->createNewTracking($validated, $booking);
            }

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'tracking' => $result['tracking']
                ]);
            } else {
                DB::rollback();

                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('خطأ في حفظ المتابعة المالية', [
                'booking_id' => $booking->id,
                'data' => $validated,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في حفظ المتابعة المالية'
            ], 500);
        }
    }

    /**
     * تحديث المتابعة المالية الموجودة
     * 
     * @param BookingFinancialTracking $tracking
     * @param array $validated
     * @param Booking $booking
     * @return array
     */
    private function updateExistingTracking(BookingFinancialTracking $tracking, array $validated, Booking $booking): array
    {
        // التحقق من إمكانية تغيير حالة السداد فقط إذا تغيرت
        if (
            $tracking->agent_payment_status !== $validated['agent_payment_status'] &&
            !$tracking->canChangeAgentStatusTo($validated['agent_payment_status'])
        ) {
            return [
                'success' => false,
                'error' => 'لا يمكن تغيير حالة السداد لجهة الحجز إلى هذه الحالة'
            ];
        }

        if (
            $tracking->company_payment_status !== $validated['company_payment_status'] &&
            !$tracking->canChangeCompanyStatusTo($validated['company_payment_status'])
        ) {
            return [
                'success' => false,
                'error' => 'لا يمكن تغيير حالة السداد للشركة إلى هذه الحالة'
            ];
        }



        // التحقق من صحة المبالغ
        $agentValidation = $this->validatePaymentAmount(
            $validated['agent_payment_amount'] ?? 0,
            $booking->amount_due_to_hotel ?? 0,
            $validated['agent_payment_status'],
            'جهة الحجز'
        );

        if (!$agentValidation['valid']) {
            return [
                'success' => false,
                'error' => $agentValidation['error']
            ];
        }

        $companyValidation = $this->validatePaymentAmount(
            $validated['company_payment_amount'] ?? 0,
            $booking->amount_due_from_company ?? 0,
            $validated['company_payment_status'],
            'الشركة'
        );

        if (!$companyValidation['valid']) {
            return [
                'success' => false,
                'error' => $companyValidation['error']
            ];
        }

        // تحديث البيانات
        $tracking->update([
            'agent_payment_status' => $validated['agent_payment_status'],
            'agent_payment_amount' => $validated['agent_payment_amount'] ?? 0,
            'agent_payment_notes' => $validated['agent_payment_notes'],
            'company_payment_status' => $validated['company_payment_status'],
            'company_payment_amount' => $validated['company_payment_amount'] ?? 0,
            'company_payment_notes' => $validated['company_payment_notes'],
            'payment_deadline' => $validated['payment_deadline'],
            'follow_up_date' => $validated['follow_up_date'],
            'priority_level' => $validated['priority_level'],
            'last_updated_by' => Auth::id()
        ]);

        return [
            'success' => true,
            'message' => 'تم تحديث المتابعة المالية بنجاح',
            'tracking' => $tracking->fresh()
        ];
    }

    /**
     * إنشاء متابعة مالية جديدة
     * 
     * @param array $validated
     * @param Booking $booking
     * @return array
     */
    private function createNewTracking(array $validated, Booking $booking): array
    {
        // التحقق من صحة المبالغ
        $agentValidation = $this->validatePaymentAmount(
            $validated['agent_payment_amount'] ?? 0,
            $booking->amount_due_to_hotel ?? 0,
            $validated['agent_payment_status'],
            'جهة الحجز'
        );

        if (!$agentValidation['valid']) {
            return [
                'success' => false,
                'error' => $agentValidation['error']
            ];
        }

        $companyValidation = $this->validatePaymentAmount(
            $validated['company_payment_amount'] ?? 0,
            $booking->amount_due_from_company ?? 0,
            $validated['company_payment_status'],
            'الشركة'
        );

        if (!$companyValidation['valid']) {
            return [
                'success' => false,
                'error' => $companyValidation['error']
            ];
        }

        // إنشاء المتابعة الجديدة
        $tracking = BookingFinancialTracking::create([
            'booking_id' => $booking->id,
            'agent_payment_status' => $validated['agent_payment_status'],
            'agent_payment_amount' => $validated['agent_payment_amount'] ?? 0,
            'agent_payment_notes' => $validated['agent_payment_notes'],
            'company_payment_status' => $validated['company_payment_status'],
            'company_payment_amount' => $validated['company_payment_amount'] ?? 0,
            'company_payment_notes' => $validated['company_payment_notes'],
            'payment_deadline' => $validated['payment_deadline'],
            'follow_up_date' => $validated['follow_up_date'],
            'priority_level' => $validated['priority_level'],
            'last_updated_by' => Auth::id()
        ]);

        return [
            'success' => true,
            'message' => 'تم إنشاء المتابعة المالية بنجاح',
            'tracking' => $tracking
        ];
    }

    /**
     * التحقق من صحة مبلغ السداد
     * 
     * @param float $paymentAmount
     * @param float $totalDue
     * @param string $paymentStatus
     * @param string $entity
     * @return array
     */
    private function validatePaymentAmount(float $paymentAmount, float $totalDue, string $paymentStatus, string $entity): array
    {
        // التحقق من أن المبلغ لا يتجاوز المستحق
        if ($paymentAmount > $totalDue) {
            return [
                'valid' => false,
                'error' => "مبلغ السداد لـ{$entity} ({$paymentAmount}) لا يمكن أن يتجاوز المبلغ المستحق ({$totalDue})"
            ];
        }

        // التحقق من تطابق المبلغ مع حالة السداد
        switch ($paymentStatus) {
            case 'not_paid':
                if ($paymentAmount > 0) {
                    return [
                        'valid' => false,
                        'error' => "حالة السداد لـ{$entity} 'لم يتم السداد' لكن المبلغ أكبر من صفر"
                    ];
                }
                break;

            case 'fully_paid':
                // استخدام هامش دقة للمقارنة (0.01) كما في الواجهة
                if (abs($paymentAmount - $totalDue) > 0.01) {
                    return [
                        'valid' => false,
                        'error' => "حالة السداد لـ{$entity} 'تم السداد بالكامل' لكن المبلغ ({$paymentAmount}) لا يساوي المستحق ({$totalDue})"
                    ];
                }
                break;

            case 'partially_paid':
                if ($paymentAmount <= 0) {
                    return [
                        'valid' => false,
                        'error' => "حالة السداد لـ{$entity} 'سداد جزئي' لكن المبلغ ({$paymentAmount}) يجب أن يكون أكبر من صفر"
                    ];
                }
                if (abs($paymentAmount - $totalDue) <= 0.01) {
                    return [
                        'valid' => false,
                        'error' => "حالة السداد لـ{$entity} 'سداد جزئي' لكن المبلغ ({$paymentAmount}) يساوي المبلغ المستحق. يجب استخدام 'تم السداد بالكامل'"
                    ];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * حذف المتابعة المالية
     * 
     * @param Booking $booking
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Booking $booking)
    {
        try {
            $tracking = $booking->financialTracking;

            if (!$tracking) {
                return response()->json([
                    'success' => false,
                    'error' => 'المتابعة المالية غير موجودة'
                ], 404);
            }

            DB::beginTransaction();

            $tracking->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف المتابعة المالية بنجاح'
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('خطأ في حذف المتابعة المالية', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في حذف المتابعة المالية'
            ], 500);
        }
    }

    /**
     * الحصول على إحصائيات المتابعة المالية
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_trackings' => BookingFinancialTracking::count(),
                'fully_paid' => BookingFinancialTracking::fullyPaid()->count(),
                'incomplete' => BookingFinancialTracking::incomplete()->count(),
                'overdue' => BookingFinancialTracking::overdue()->count(),
                'high_priority' => BookingFinancialTracking::highPriority()->count(),
                'by_status' => [
                    'agent' => [
                        'not_paid' => BookingFinancialTracking::where('agent_payment_status', 'not_paid')->count(),
                        'partially_paid' => BookingFinancialTracking::where('agent_payment_status', 'partially_paid')->count(),
                        'fully_paid' => BookingFinancialTracking::where('agent_payment_status', 'fully_paid')->count(),
                    ],
                    'company' => [
                        'not_paid' => BookingFinancialTracking::where('company_payment_status', 'not_paid')->count(),
                        'partially_paid' => BookingFinancialTracking::where('company_payment_status', 'partially_paid')->count(),
                        'fully_paid' => BookingFinancialTracking::where('company_payment_status', 'fully_paid')->count(),
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إحصائيات المتابعة المالية', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في تحميل الإحصائيات'
            ], 500);
        }
    }
}
