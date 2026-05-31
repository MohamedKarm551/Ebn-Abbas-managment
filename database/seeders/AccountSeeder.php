<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\Company;  
use App\Models\Agent;    

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // مسح القديم لو موجود
        Account::query()->forceDelete();

        $accounts = [
            // ============================================================
            // 1 - الأصول
            // ============================================================
           
            ['code' => '1',     'name' => 'الأصول',               'type' => 'asset',     'parent' => null, 'is_leaf' => false],
            ['code' => '1.1',   'name' => 'الأصول المتداولة',     'type' => 'asset',     'parent' => '1', 'is_leaf' => false],
            
            ['code' => '1.1.1', 'name' => 'الصندوق',              'type' => 'asset',     'parent' => '1.1'],
            
            ['code' => '1.1.2', 'name' => 'البنوك',              'type' => 'asset',     'parent' => '1.1', 'is_leaf' => false],
            ['code' => '1.1.2.1', 'name' => 'البنك - الأهلي التجاري','type' => 'asset',    'parent' => '1.1.2'],
            
            ['code' => '1.1.3', 'name' => 'مدينون',  'type' => 'asset',     'parent' => '1.1', 'is_leaf' => false],
            ['code' => '1.1.3.1', 'name' => 'العملاء',  'type' => 'asset',     'parent' => '1.1.3', 'company_id' => null, 'agent_id' => null , 'is_leaf' => false],
            
            ['code' => '1.1.3.2', 'name' => 'المخزون',  'type' => 'asset',     'parent' => '1.1.3'],
            
            ['code' => '1.2',   'name' => 'الأصول الثابتة',       'type' => 'asset',     'parent' => '1', 'is_leaf' => false],
            ['code' => '1.2.1', 'name' => 'المباني والأثاث',      'type' => 'asset',     'parent' => '1.2'],
            ['code' => '1.2.2', 'name' => 'الأجهزة والمعدات',     'type' => 'asset',     'parent' => '1.2'],
            ['code' => '1.2.3', 'name' => 'مجمع الإهلاك',     'type' => 'asset',     'parent' => '1.2'],

            // ============================================================
            // 2 - الخصوم
            // ============================================================
            ['code' => '2',     'name' => 'الخصوم',               'type' => 'liability', 'parent' => null, 'is_leaf' => false],
            ['code' => '2.1',   'name' => 'الخصوم المتداولة',     'type' => 'liability', 'parent' => '2', 'is_leaf' => false],
            
            ['code' => '2.1.1', 'name' => 'دائنون',  'type' => 'liability', 'parent' => '2.1', 'is_leaf' => false],    
            
            ['code' => '2.1.1.1', 'name' => 'موردين',  'type' => 'liability', 'parent' => '2.1.1', 'company_id' => null, 'agent_id' => null, 'is_leaf' => false],              
            ['code' => '2.1.1.2', 'name' => 'مخصص ضرائب عامة',  'type' => 'liability', 'parent' => '2.1.1', 'company_id' => null, 'agent_id' => null, 'is_leaf' => false],              
           
            ['code' => '2.1.2', 'name' => 'دفعات مقدمة من نزلاء',      'type' => 'liability', 'parent' => '2.1'],
            ['code' => '2.1.3', 'name' => 'مصروفات مستحقة',      'type' => 'liability', 'parent' => '2.1'],
            ['code' => '2.2',   'name' => 'الخصوم طويلة الأمد',  'type' => 'liability', 'parent' => '2'],

            // ============================================================
            // 3 - حقوق الملكية
            // ============================================================
            ['code' => '3',     'name' => 'حقوق الملكية',         'type' => 'equity',    'parent' => null, 'is_leaf' => false],
            ['code' => '3.1',   'name' => 'رأس المال',             'type' => 'equity',    'parent' => '3'],
            ['code' => '3.2',   'name' => 'الأرباح المحتجزة',     'type' => 'equity',    'parent' => '3'],

            // ============================================================
            // 4 - الإيرادات
            // ============================================================
            ['code' => '4',     'name' => 'الإيرادات',             'type' => 'revenue',   'parent' => null, 'is_leaf' => false],
            ['code' => '4.1',   'name' => 'إيرادات حجز',         'type' => 'revenue',   'parent' => '4'],
            ['code' => '4.2',   'name' => 'إيرادات أخرى',   'type' => 'revenue',   'parent' => '4'],

            // ============================================================
            // 5 - المصروفات
            // ============================================================
            ['code' => '5',     'name' => 'المصروفات',             'type' => 'expense',   'parent' => null, 'is_leaf' => false],
            ['code' => '5.1',   'name' => 'مصروفات عمومية و إدارية','type' => 'expense',   'parent' => '5'],
            ['code' => '5.2',   'name' => 'مصروفات التشغيل',       'type' => 'expense',   'parent' => '5'],
            ['code' => '5.3',   'name' => 'مصاريف تكلفة النشاط',       'type' => 'expense',   'parent' => '5'],
        ];

        $created = [];

        foreach ($accounts as $data) {
            $parentId = null;
            if ($data['parent'] !== null) {
                $parentId = $created[$data['parent']]->id ?? null;
            }

            $normalBalance = in_array($data['type'], ['asset', 'expense']) ? 'debit' : 'credit';

            $account = Account::create([
                'code'           => $data['code'],
                'name'           => $data['name'],
                'type'           => $data['type'],
                'normal_balance' => $normalBalance,
                'parent_id'      => $parentId,
                'company_id'     => $data['company_id'] ?? null,
                'agent_id'       => $data['agent_id'] ?? null,
                'is_leaf'        => $data['is_leaf'] ?? true,
                'is_active'      => true,
                'level'          => $parentId ? ($created[$data['parent']]->level + 1) : 1,
            ]);

            $created[$data['code']] = $account;
        }

        $this->command->info('✅ تم إنشاء ' . count($accounts) . ' حساب بنجاح');
    }
}