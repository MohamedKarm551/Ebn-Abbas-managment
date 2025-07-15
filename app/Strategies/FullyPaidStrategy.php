<?php

namespace App\Strategies;

/**
 * استراتيجية حالة "السداد الكامل"
 * 
 * تمثل الحالة المثالية عند اكتمال السداد
 * لا تحتاج متابعة إضافية
 */
class FullyPaidStrategy implements PaymentStatusStrategy
{
    /**
     * لون أخضر للدلالة على اكتمال السداد
     */
    public function getStatusColor(): string
    {
        return '#28a745'; // Bootstrap success color
    }

    /**
     * أيقونة صح للدلالة على الإنجاز
     */
    public function getStatusIcon(): string
    {
        return 'fas fa-check-circle';
    }

    /**
     * التسمية العربية
     */
    public function getStatusLabel(): string
    {
        return 'تم السداد بالكامل';
    }

    /**
     * يمكن التحويل لأي حالة أخرى (في حالة التراجع)
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, ['not_paid', 'partially_paid']);
    }

    /**
     * وصف تفصيلي للحالة
     */
    public function getStatusDescription(): string
    {
        return 'تم تسديد كامل المبلغ المستحق. لا حاجة لمتابعة إضافية.';
    }

    /**
     * كلاس Bootstrap للتنسيق
     */
    public function getBootstrapClass(): string
    {
        return 'success';
    }
}