<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // التأكد من وجود شركات في قاعدة البيانات
        if (Company::count() === 0) {
            $this->call(CompaniesTableSeeder::class);
        }

        // جلب الشركات المتاحة
        $companies = Company::all()->pluck('id')->toArray();

        // إنشاء مستخدمين تجريبيين
        $users = [
            [
                'name' => 'محمد كرم',
                'email' => 'admin@example.com',
                'password' => Hash::make('123'),
                'role' => 'Admin',
                'company_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'محمد علي',
                'email' => 'employee@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Employee',
                'company_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'شركة الحج الأولى',
                'email' => 'company1@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Company',
                'company_id' => $companies[0] ?? null, // ربط مع أول شركة
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'شركة السياحة المميزة',
                'email' => 'company2@example.com',
                'password' => Hash::make('password123'),
                'role' => 'Company',
                'company_id' => $companies[1] ?? null, // ربط مع ثاني شركة
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // إدخال المستخدمين في جدول users
        foreach ($users as $user) {
            User::create($user);
        }
    }
}