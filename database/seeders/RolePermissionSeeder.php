<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions ───────────────────────────────
        $permissions = [
            'create-ticket',
            'view-own-tickets',
            'view-all-tickets',
            'update-ticket',
            'delete-ticket',
            'assign-ticket',
            'add-public-reply',
            'add-internal-note',
            'manage-users',
            'manage-settings',   // departments, categories, priorities
            'view-reports',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Requester ────────────────────────────────────────────
        $requester = Role::firstOrCreate(['name' => 'requester', 'guard_name' => 'web']);
        $requester->syncPermissions([
            'create-ticket',
            'view-own-tickets',
            'add-public-reply',
        ]);

        // ── Agent ────────────────────────────────────────────────
        $agent = Role::firstOrCreate(['name' => 'it_staff', 'guard_name' => 'web']);
        $agent->syncPermissions([
            'create-ticket',
            'view-all-tickets',
            'update-ticket',
            'assign-ticket',
            'add-public-reply',
            'add-internal-note',
        ]);

        // ── Admin ─────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'it_head', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions); // all permissions
    }
}
