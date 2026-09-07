<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            PrioritySeeder::class,
            CategorySeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
