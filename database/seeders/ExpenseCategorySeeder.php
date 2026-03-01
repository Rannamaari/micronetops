<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Inventory Cost', 'type' => ExpenseCategory::TYPE_COGS],
            ['name' => 'Utilities', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Salary', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Electricity Bill', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Water Bill', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Internet', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Entertainment', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Transport', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Fuel', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Rent', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Maintenance', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Repairs', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Office Supplies', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Cleaning', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Security', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Marketing', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Insurance', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Bank Charges', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Professional Services', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'License & Registration', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Work Permit Fees', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Visa Fees', 'type' => ExpenseCategory::TYPE_OPERATING],
            ['name' => 'Miscellaneous', 'type' => ExpenseCategory::TYPE_OPERATING],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                ['type' => $category['type'], 'is_active' => true]
            );
        }
    }
}
