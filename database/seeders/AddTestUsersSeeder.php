<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Quản trị viên (Admin)', 'password' => Hash::make('password123')]
        );
        $admin->assignRole('admin');

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            ['name' => 'Giảng viên (Teacher)', 'password' => Hash::make('password123')]
        );
        $teacher->assignRole('teacher');

        $student = User::firstOrCreate(
            ['email' => 'student@example.com'],
            ['name' => 'Sinh viên (Student)', 'password' => Hash::make('password123')]
        );
        $student->assignRole('student');
    }
}
