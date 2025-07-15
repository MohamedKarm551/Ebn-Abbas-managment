<?php

namespace App\Strategies;

/**
 * استراتيجية حالة "السداد الجزئي"
 * 
 * تمثل الحالة عندما يتم دفع جزء من المبلغ المستحق
 * تتطلب متابعة للجزء المتبقي
 */
class PartiallyPaidStrategy implements PaymentStatusStrategy
{
    /**
     * لون أصفر للدلالة على السداد الجزئي
     */
    public function getStatusColor(): string
    {
        return '#ffc107'; // Bootstrap warning color
    }

    /**
     * أيقونة ساعة للدلالة على الانتظار
     */
    public function getStatusIcon(): string
    {
        return 'fas fa-clock';
    }

    /**
     * التسمية العربية
     */
    public function getStatusLabel(): string
    {
        return 'سداد جزئي';
    }

    /**
     * يمكن التحويل لأي حالة أخرى
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, ['not_paid', 'fully_paid']);
    }

    /**
     * وصف تفصيلي للحالة
     */
    public function getStatusDescription(): string
    {
        return 'تم تسديد جزء من المبلغ المستحق. يجب متابعة الجزء المتبقي.';
    }

    /**
     * كلاس Bootstrap للتنسيق
     */
    public function getBootstrapClass(): string
    {
        return 'warning';
    }
}