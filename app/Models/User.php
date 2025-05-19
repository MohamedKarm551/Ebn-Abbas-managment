<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// *** استيراد موديل الشركة ***
use App\Models\Company;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id', // *** إضافة company_id هنا ***
        'role',       // *** تأكد من وجود role هنا لو بتستخدمه في التسجيل أو التعديل ***
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company that the user belongs to.
     * علاقة المستخدم بالشركة (المستخدم يتبع شركة واحدة)
     */
    public function company()
    {
        // Eloquent هيفترض إن المفتاح الأجنبي هو company_id بناءً على اسم الدالة
        return $this->belongsTo(Company::class);
    }
    // *** علاقة المستخدم بالموظف ***
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
