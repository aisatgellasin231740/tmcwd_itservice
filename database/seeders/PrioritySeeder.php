<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    public function run(): void
    {
        $priorities = [
            // sort_order 1 = most urgent
            ['name' => 'Urgent', 'sla_hours' => 2,   'color_code' => 'red',    'sort_order' => 1],
            ['name' => 'High',   'sla_hours' => 8,   'color_code' => 'orange', 'sort_order' => 2],
            ['name' => 'Medium', 'sla_hours' => 48,  'color_code' => 'yellow', 'sort_order' => 3],
            ['name' => 'Low',    'sla_hours' => 120, 'color_code' => 'blue',   'sort_order' => 4],
        ];

        foreach ($priorities as $data) {
            Priority::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
