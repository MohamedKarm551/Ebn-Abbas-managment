<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_id','amount','description',
        'status','created_by','approved_by','approved_at'
    ];

    public function booking() {
        return $this->belongsTo(Booking::class);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy() {
        return $this->belongsTo(User::class, 'approved_by');
    }
}