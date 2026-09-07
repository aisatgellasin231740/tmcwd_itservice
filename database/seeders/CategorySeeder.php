<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Hardware Issue',
            'Software Issue',
            'Network/Internet Issue',
            'Printer/Scanner Issue',
            'Email/Account Access',
            'Billing System Issue',
            'GIS/Mapping System Issue',
            'New Equipment Request',
            'Account Creation/Access Request',
            'Other',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
