<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'trip_id','client_name','gender',
        'passport_image','personal_photo',
        'accommodation_type','base_price',
        'representative_name','notes','representative_id',
        'room_assignment_id','is_family','old_room_assignment_id',
    ];

    public function trip() {
        return $this->belongsTo(Trip::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function totalPaid() {
        return $this->payments->sum('amount');
    }

    public function representative()
    {
        return $this->belongsTo(User::class, 'representative_id');
    }

    public function roomAssignment() {
        return $this->belongsTo(RoomAssignment::class);
    }

    public function discounts() {
        return $this->hasMany(Discount::class);
    }

    // إجمالي الخصومات المعتمدة
    public function totalDiscount() {
        return $this->discounts()
            ->where('status','approved')
            ->sum('amount');
    }

    // السعر بعد الخصم
    public function finalPrice() {
        return $this->base_price - $this->totalDiscount();
    }

    // المتبقي بعد الخصم والمدفوعات
    public function remaining() {
        return $this->finalPrice() - $this->totalPaid();
    }

    public function scopeHasRemaining($query)
    {
        return $query->whereRaw("
            (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payments.booking_id = bookings.id)
            <
            (bookings.base_price - COALESCE(
                (SELECT SUM(amount) FROM discounts WHERE discounts.booking_id = bookings.id AND discounts.status = 'approved'), 0
            ))
        ");
    }

   public static function deleteFileIfExists($path)
    {
        if ($path && \Storage::disk('public')->exists($path)) {
            \Storage::disk('public')->delete($path);
        }
    }
    
    public function deleteAssociatedFiles()
    {
        static::deleteFileIfExists($this->passport_image);
        static::deleteFileIfExists($this->personal_photo);
    }

    public function journalEntry()
    {
        return $this->morphOne(\App\Models\JournalEntry::class, 'source');
    }

}