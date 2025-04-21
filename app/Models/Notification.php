<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // لو جدولك اسمه notifications مش محتاج تغير اسم الجدول

    // لو عندك أعمدة قابلة للتعبئة الجماعية (mass assignment)
    protected $fillable = [
        'type',
        'message',
        'user_id',
        'is_read',
    ];

    // لو عندك تواريخ وتريد تحويلها تلقائياً لـ Carbon
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // علاقة مع المستخدم (اختياري)
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}
}
