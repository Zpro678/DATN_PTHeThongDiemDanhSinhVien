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

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên (Admin)',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Giảng viên (Teacher)',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $teacher->syncRoles(['teacher']);

        $student = User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Sinh viên (Student)',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['student']);
    }
}
