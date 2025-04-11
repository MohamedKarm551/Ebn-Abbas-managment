<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agent;

class AgentsTableSeeder extends Seeder
{
    public function run()
    {
        $agents = [
            ['name' => 'قافلة المشاعر'],
            ['name' => 'الرابح'],
            ['name' => 'إدارة أرتال'],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }
    }
}
