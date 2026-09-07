<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Administration',
            'Finance/Billing',
            'Commercial/Customer Service',
            'Engineering',
            'Production/Plant Operations',
            'Meter Reading',
            'Human Resources',
            'IT',
            'Other',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
