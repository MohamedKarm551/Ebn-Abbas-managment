<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;

class HotelsTableSeeder extends Seeder
{
    public function run()
    {
        $hotels = [
            ['name' => 'الكسوة', 'location' => 'مكة'],
            ['name' => 'أبراج المسك', 'location' => 'المدينة'],
            ['name' => 'النزهة بلس', 'location' => 'جدة'],
        ];

        foreach ($hotels as $hotel) {
            Hotel::create($hotel);
        }
    }
}
