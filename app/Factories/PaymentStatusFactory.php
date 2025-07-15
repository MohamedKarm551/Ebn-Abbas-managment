<?php

namespace App\Factories;

use App\Strategies\PaymentStatusStrategy;
use App\Strategies\NotPaidStrategy;
use App\Strategies\PartiallyPaidStrategy;
use App\Strategies\FullyPaidStrategy;
use InvalidArgumentException;

/**
 * مصنع إنتاج استراتيجيات حالة السداد
 * 
 * يستخدم Factory Pattern لإنشاء الاستراتيجية المناسبة
 * حسب حالة السداد المطلوبة
 */
class PaymentStatusFactory
{
    /**
     * إنشاء استراتيجية حالة السداد المناسبة
     * 
     * @param string $status حالة السداد (not_paid, partially_paid, fully_paid)
     * @return PaymentStatusStrategy
     * @throws InvalidArgumentException
     */
    public static function create(string $status): PaymentStatusStrategy
    {
        return match($status) {
            'not_paid' => new NotPaidStrategy(),
            'partially_paid' => new PartiallyPaidStrategy(),
            'fully_paid' => new FullyPaidStrategy(),
            default => throw new InvalidArgumentException("حالة السداد غير صحيحة: {$status}")
        };
    }

    /**
     * الحصول على جميع الحالات المتاحة
     * 
     * @return array
     */
    public static function getAllStatuses(): array
    {
        return [
            'not_paid' => 'لم يتم السداد',
            'partially_paid' => 'سداد جزئي',
            'fully_paid' => 'تم السداد بالكامل'
        ];
    }

    /**
     * الحصول على الحالات التي يمكن التحويل إليها
     * 
     * @param string $currentStatus
     * @return array
     */
    public static function getAvailableTransitions(string $currentStatus): array
    {
        $strategy = self::create($currentStatus);
        $allStatuses = self::getAllStatuses();
        $availableTransitions = [];

        foreach ($allStatuses as $status => $label) {
            if ($strategy->canTransitionTo($status)) {
                $availableTransitions[$status] = $label;
            }
        }

        return $availableTransitions;
    }
}