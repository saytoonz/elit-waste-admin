<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fuel', 'code' => 'FUEL', 'color' => '#EF4444'],
            ['name' => 'Vehicle Maintenance', 'code' => 'VEH-MAINT', 'color' => '#F97316'],
            ['name' => 'Salaries & Wages', 'code' => 'SALARY', 'color' => '#8B5CF6'],
            ['name' => 'Equipment', 'code' => 'EQUIP', 'color' => '#3B82F6'],
            ['name' => 'Utilities', 'code' => 'UTIL', 'color' => '#06B6D4'],
            ['name' => 'Rent', 'code' => 'RENT', 'color' => '#10B981'],
            ['name' => 'Office Supplies', 'code' => 'OFFICE', 'color' => '#84CC16'],
            ['name' => 'Permits & Licenses', 'code' => 'PERMIT', 'color' => '#EAB308'],
            ['name' => 'Marketing', 'code' => 'MKT', 'color' => '#EC4899'],
            ['name' => 'Insurance', 'code' => 'INSURE', 'color' => '#6366F1'],
            ['name' => 'Bank Charges', 'code' => 'BANK', 'color' => '#64748B'],
            ['name' => 'Travel', 'code' => 'TRAVEL', 'color' => '#14B8A6'],
            ['name' => 'Repairs', 'code' => 'REPAIRS', 'color' => '#F59E0B'],
            ['name' => 'Miscellaneous', 'code' => 'MISC', 'color' => '#9CA3AF'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
