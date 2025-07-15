<?php

namespace App\Strategies;

/**
 * استراتيجية حالة "لم يتم السداد"
 * 
 * تمثل الحالة الافتراضية عند إنشاء متابعة جديدة
 * يمكن التحويل منها إلى أي حالة أخرى
 */
class NotPaidStrategy implements PaymentStatusStrategy
{
    /**
     * لون أحمر للدلالة على عدم السداد
     */
    public function getStatusColor(): string
    {
        return '#dc3545'; // Bootstrap danger color
    }

    /**
     * أيقونة X للدلالة على عدم السداد
     */
    public function getStatusIcon(): string
    {
        return 'fas fa-times-circle';
    }

    /**
     * التسمية العربية
     */
    public function getStatusLabel(): string
    {
        return 'لم يتم السداد';
    }

    /**
     * يمكن التحويل لأي حالة أخرى
     */
    public function canTransitionTo(string $newStatus): bool
    {
        // return in_array($newStatus, ['partially_paid', 'fully_paid']);//  الانتقال إلى أي حالة أخرى
        return true; // يمكن الانتقال إلى أي حالة أخرى

    }

    /**
     * وصف تفصيلي للحالة
     */
    public function getStatusDescription(): string
    {
        return 'لم يتم تسديد أي مبلغ بعد. يجب المتابعة مع الجهة المسؤولة.';
    }

    /**
     * كلاس Bootstrap للتنسيق
     */
    public function getBootstrapClass(): string
    {
        return 'danger';
    }
}