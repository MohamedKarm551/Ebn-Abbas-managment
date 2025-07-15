<?php

namespace App\Observers;

use App\Models\BookingFinancialTracking;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Observer للمتابعة المالية للحجوزات
 * 
 * يستخدم Observer Pattern لتسجيل جميع التغييرات
 * وإرسال الإشعارات التلقائية للمدراء والموظفين
 * 
 * مسار الملف: app/Observers/FinancialTrackingObserver.php
 */
class FinancialTrackingObserver
{
    /**
     * التعامل مع إنشاء متابعة مالية جديدة
     * 
     * @param BookingFinancialTracking $tracking
     */
    public function created(BookingFinancialTracking $tracking)
    {
        // تسجيل العملية في الـ Log
        Log::info('إنشاء متابعة مالية جديدة', [
            'tracking_id' => $tracking->id,
            'booking_id' => $tracking->booking_id,
            'voucher_number' => $tracking->booking->id ?? 'غير معروف',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'غير معروف'
        ]);

        // إرسال الإشعارات للمدراء
        $this->notifyAdmins(
            'إنشاء متابعة مالية جديدة',
            "تم إنشاء متابعة مالية جديدة للحجز رقم {$tracking->booking->id}",
            $tracking,
            'financial_tracking_created'
        );
    }

    /**
     * التعامل مع تحديث المتابعة المالية
     * 
     * @param BookingFinancialTracking $tracking
     */
    public function updated(BookingFinancialTracking $tracking)
    {
        // الحصول على التغييرات التي حدثت
        $changes = $tracking->getDirty();

        // تسجيل التغييرات في الـ Log
        Log::info('تحديث متابعة مالية', [
            'tracking_id' => $tracking->id,
            'booking_id' => $tracking->booking_id,
            'voucher_number' => $tracking->booking->id ?? 'غير معروف',
            'changes' => $changes,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'غير معروف'
        ]);

        // التحقق من تغيير حالة السداد لجهة الحجز
        if (array_key_exists('agent_payment_status', $changes)) {
            $this->handleAgentPaymentStatusChange($tracking, $changes['agent_payment_status']);
        }

        // التحقق من تغيير حالة السداد للشركة
        if (array_key_exists('company_payment_status', $changes)) {
            $this->handleCompanyPaymentStatusChange($tracking, $changes['company_payment_status']);
        }

        // التحقق من تغيير المبالغ
        if (
            array_key_exists('agent_payment_amount', $changes) ||
            array_key_exists('company_payment_amount', $changes)
        ) {
            $this->handlePaymentAmountChange($tracking, $changes);
        }

        // التحقق من تغيير مستوى الأولوية
        if (array_key_exists('priority_level', $changes)) {
            $this->handlePriorityLevelChange($tracking, $changes['priority_level']);
        }

        // التحقق من تغيير تاريخ المتابعة
        if (array_key_exists('follow_up_date', $changes)) {
            $this->handleFollowUpDateChange($tracking, $changes['follow_up_date']);
        }
    }

    /**
     * التعامل مع حذف المتابعة المالية
     * 
     * @param BookingFinancialTracking $tracking
     */
    public function deleted(BookingFinancialTracking $tracking)
    {
        // تسجيل العملية في الـ Log
        Log::warning('حذف متابعة مالية', [
            'tracking_id' => $tracking->id,
            'booking_id' => $tracking->booking_id,
            'voucher_number' => $tracking->booking->id ?? 'غير معروف',
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name ?? 'غير معروف'
        ]);

        // إرسال تنبيه للمدراء
        $this->notifyAdmins(
            'حذف متابعة مالية',
            "تم حذف المتابعة المالية للحجز رقم {$tracking->booking->id}",
            $tracking,
            'financial_tracking_deleted'
        );
    }

    /**
     * التعامل مع تغيير حالة السداد لجهة الحجز
     * 
     * @param BookingFinancialTracking $tracking
     * @param string $newStatus
     */
    private function handleAgentPaymentStatusChange(BookingFinancialTracking $tracking, string $newStatus)
    {
        $agentName = $tracking->booking->agent->name ?? 'غير معروف';
        $statusLabel = $tracking->getAgentPaymentStrategy()->getStatusLabel();
        $voucherNumber = $tracking->booking->id ?? 'غير معروف';

        $message = "تم تغيير حالة السداد لجهة الحجز ({$agentName}) إلى: {$statusLabel} - الحجز رقم: {$voucherNumber}";

        // إرسال إشعار حسب نوع التغيير
        $notificationType = match ($newStatus) {
            'fully_paid' => 'agent_payment_completed',
            'partially_paid' => 'agent_payment_partial',
            'not_paid' => 'agent_payment_pending',
            default => 'agent_payment_status_change'
        };

        $this->notifyAdmins(
            'تغيير حالة سداد جهة الحجز',
            $message,
            $tracking,
            $notificationType
        );
    }

    /**
     * التعامل مع تغيير حالة السداد للشركة
     * 
     * @param BookingFinancialTracking $tracking
     * @param string $newStatus
     */
    private function handleCompanyPaymentStatusChange(BookingFinancialTracking $tracking, string $newStatus)
    {
        $companyName = $tracking->booking->company->name ?? 'غير معروف';
        $statusLabel = $tracking->getCompanyPaymentStrategy()->getStatusLabel();
        $voucherNumber = $tracking->booking->client_name ?? 'غير معروف';

        $message = "تم تغيير حالة السداد للشركة ({$companyName}) إلى: {$statusLabel} - حجز العميل : {$voucherNumber}";

        // إرسال إشعار حسب نوع التغيير
        $notificationType = match ($newStatus) {
            'fully_paid' => 'company_payment_completed',
            'partially_paid' => 'company_payment_partial',
            'not_paid' => 'company_payment_pending',
            default => 'company_payment_status_change'
        };

        $this->notifyAdmins(
            'تغيير حالة سداد الشركة',
            $message,
            $tracking,
            $notificationType
        );
    }

    /**
     * التعامل مع تغيير المبالغ
     * 
     * @param BookingFinancialTracking $tracking
     * @param array $changes
     */
    private function handlePaymentAmountChange(BookingFinancialTracking $tracking, array $changes)
    {
        $voucherNumber = $tracking->booking->id ?? 'غير معروف';
        $currency = $tracking->booking->currency ?? 'غير معروف';
        $messageDetails = [];

        if (array_key_exists('agent_payment_amount', $changes)) {
            $oldAmount = $tracking->getOriginal('agent_payment_amount') ?? 0;
            $newAmount = $tracking->agent_payment_amount;
            $agentName = $tracking->booking->agent->name ?? 'غير معروف';

            $messageDetails[] = "جهة الحجز ({$agentName}): من {$oldAmount} إلى {$newAmount} {$currency}";
        }

        if (array_key_exists('company_payment_amount', $changes)) {
            $oldAmount = $tracking->getOriginal('company_payment_amount') ?? 0;
            $newAmount = $tracking->company_payment_amount;
            $companyName = $tracking->booking->company->name ?? 'غير معروف';

            $messageDetails[] = "الشركة ({$companyName}): من {$oldAmount} إلى {$newAmount} {$currency}";
        }

        $message = "تم تغيير مبالغ السداد للحجز رقم {$voucherNumber}:\n" . implode("\n", $messageDetails);

        $this->notifyAdmins(
            'تغيير مبالغ السداد',
            $message,
            $tracking,
            'payment_amount_change'
        );
    }

    /**
     * التعامل مع تغيير مستوى الأولوية
     * 
     * @param BookingFinancialTracking $tracking
     * @param string $newPriority
     */
    private function handlePriorityLevelChange(BookingFinancialTracking $tracking, string $newPriority)
    {
        $voucherNumber = $tracking->booking->id ?? 'غير معروف';
        $priorityLabels = [
            'low' => 'منخفضة',
            'medium' => 'متوسطة',
            'high' => 'عالية'
        ];

        $priorityLabel = $priorityLabels[$newPriority] ?? 'غير معروف';
        $message = "تم تغيير مستوى أولوية المتابعة للحجز رقم {$voucherNumber} إلى: {$priorityLabel}";

        $notificationType = $newPriority === 'high' ? 'متابعة مالية عالية الأهمية' : 'تغيير مستوى الأولوية';

        $this->notifyAdmins(
            'تغيير مستوى الأولوية',
            $message,
            $tracking,
            $notificationType
        );
    }

    /**
     * التعامل مع تغيير تاريخ المتابعة
     * 
     * @param BookingFinancialTracking $tracking
     * @param string $newFollowUpDate
     */
    /**
     * التعامل مع تغيير تاريخ المتابعة
     * 
     * @param BookingFinancialTracking $tracking
     * @param string|null $newFollowUpDate
     */
    private function handleFollowUpDateChange(BookingFinancialTracking $tracking, ?string $newFollowUpDate)
    {
        $voucherNumber = $tracking->booking?->id ?? 'غير معروف';
        $formattedDate = $newFollowUpDate ? date('Y-m-d', strtotime($newFollowUpDate)) : 'غير محدد';

        $message = "تم تحديث تاريخ المتابعة للحجز رقم {$voucherNumber} إلى: {$formattedDate}";

        $this->notifyAdmins(
            'تحديث تاريخ المتابعة',
            $message,
            $tracking,
            'follow_up_date_change'
        );
    }

    /**
     * إرسال إشعارات للمدراء
     * 
     * @param string $title
     * @param string $message
     * @param BookingFinancialTracking $tracking
     * @param string $type
     */
    private function notifyAdmins(string $title, string $message, BookingFinancialTracking $tracking, string $type)
    {
        try {
            // الحصول على جميع المدراء
            $adminUsers = User::where('role', 'Admin')->get();

            // إرسال إشعار لكل مدير
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'data' => json_encode([
                        'tracking_id' => $tracking->id,
                        'booking_id' => $tracking->booking_id,
                        'voucher_number' => $tracking->booking->id ?? null,
                        'agent_name' => $tracking->booking->agent->name ?? null,
                        'company_name' => $tracking->booking->company->name ?? null,
                        'updated_by' => Auth::id(),
                        'updated_by_name' => Auth::user()->name ?? null,
                        'timestamp' => now()->toDateTimeString()
                    ])
                ]);
            }

            Log::info('تم إرسال إشعارات المتابعة المالية', [
                'title' => $title,
                'type' => $type,
                'tracking_id' => $tracking->id,
                'admin_count' => $adminUsers->count()
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعارات المتابعة المالية', [
                'title' => $title,
                'type' => $type,
                'tracking_id' => $tracking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * إرسال إشعارات للموظفين المحددين
     * 
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param BookingFinancialTracking $tracking
     * @param string $type
     */
    private function notifySpecificUsers(array $userIds, string $title, string $message, BookingFinancialTracking $tracking, string $type)
    {
        try {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'data' => json_encode([
                        'tracking_id' => $tracking->id,
                        'booking_id' => $tracking->booking_id,
                        'voucher_number' => $tracking->booking->id ?? null,
                        'updated_by' => Auth::id(),
                        'updated_by_name' => Auth::user()->name ?? null,
                        'timestamp' => now()->toDateTimeString()
                    ])
                ]);
            }

            Log::info('تم إرسال إشعارات متخصصة للمتابعة المالية', [
                'title' => $title,
                'type' => $type,
                'tracking_id' => $tracking->id,
                'user_count' => $users->count()
            ]);
        } catch (\Exception $e) {
            Log::error('خطأ في إرسال إشعارات متخصصة للمتابعة المالية', [
                'title' => $title,
                'type' => $type,
                'tracking_id' => $tracking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
