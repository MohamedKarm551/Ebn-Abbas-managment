<?php

namespace App\Strategies;

/**
 * واجهة استراتيجية حالة السداد
 * 
 * تحدد العمليات المطلوبة لكل حالة سداد:
 * - اللون المخصص
 * - الأيقونة المخصصة
 * - التسمية بالعربية
 * - إمكانية التحويل للحالات الأخرى
 */
interface PaymentStatusStrategy
{
    /**
     * الحصول على لون الحالة (Hex Code)
     */
    public function getStatusColor(): string;

    /**
     * الحصول على أيقونة الحالة (Font Awesome)
     */
    public function getStatusIcon(): string;

    /**
     * الحصول على التسمية بالعربية
     */
    public function getStatusLabel(): string;

    /**
     * التحقق من إمكانية التحويل لحالة جديدة
     */
    public function canTransitionTo(string $newStatus): bool;

    /**
     * الحصول على وصف تفصيلي للحالة
     */
    public function getStatusDescription(): string;

    /**
     * الحصول على كلاس Bootstrap للحالة
     */
    public function getBootstrapClass(): string;
}