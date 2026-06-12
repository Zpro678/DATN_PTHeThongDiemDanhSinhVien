<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'manage users',
            'manage plans',
            'manage tenants',
            'create classes',
            'manage own classes',
            'manage attendance',
            'export attendance',
            'view own attendance',
            'submit leave requests',
        ])->mapWithKeys(fn (string $name) => [
            $name => Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]),
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions->values());
        $teacher->syncPermissions([
            $permissions['create classes'],
            $permissions['manage own classes'],
            $permissions['manage attendance'],
            $permissions['export attendance'],
        ]);
        $user->syncPermissions([
            $permissions['create classes'],
            $permissions['manage own classes'],
            $permissions['view own attendance'],
            $permissions['submit leave requests'],
        ]);
        $student->syncPermissions([
            $permissions['view own attendance'],
            $permissions['submit leave requests'],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
