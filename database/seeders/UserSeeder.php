<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dept = fn(string $name) => Department::where('name', $name)->value('id');

        $users = [
            [
                'name'                 => 'IT Admin',
                'email'                => 'admin@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('IT'),
                'is_active'            => true,
                'must_change_password' => true,   // ← forced on first login
                'role'                 => 'it_head',
            ],
            [
                'name'                 => 'Juan dela Cruz',
                'email'                => 'juan@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('IT'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'it_staff',
            ],
            [
                'name'                 => 'Maria Santos',
                'email'                => 'maria@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('IT'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'it_staff',
            ],
            [
                'name'                 => 'Ana Reyes',
                'email'                => 'ana@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Finance/Billing'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Pedro Gomez',
                'email'                => 'pedro@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Engineering'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Rosa Mendoza',
                'email'                => 'rosa@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Commercial/Customer Service'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Carlo Bautista',
                'email'                => 'carlo@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Administration'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Liza Torres',
                'email'                => 'liza@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Production/Plant Operations'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Ben Navarro',
                'email'                => 'ben@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Meter Reading'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
            [
                'name'                 => 'Joy Flores',
                'email'                => 'joy@tmcwd.gov.ph',
                'password'             => Hash::make('password'),
                'department_id'        => $dept('Human Resources'),
                'is_active'            => true,
                'must_change_password' => true,
                'role'                 => 'requester',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::where('email', $data['email'])->first();
            if ($user) {
                $user->update($data);
            } else {
                $user = User::create($data);
            }
            $user->syncRoles([$role]);
        }
    }
}
