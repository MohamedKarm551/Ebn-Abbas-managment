<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use Illuminate\Support\Str;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء 3 شركات تجريبية
      $companies = [
            ['name' => 'شركة الحج الأولى'],
            ['name' => 'شركة العمرة المباركة'],
            ['name' => 'شركة السياحة الذهبية'],
        ];
        // إدخال الشركات في جدول companies
        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}