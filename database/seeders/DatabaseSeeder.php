<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            AttendanceDemoSeeder::class,
        ]);

        // NotificationDemoSeeder là dữ liệu demo bổ sung cho trang thông báo,
        // chạy thủ công khi cần: php artisan db:seed --class=NotificationDemoSeeder
        // (không đưa vào seed mặc định để giữ ổn định bộ dữ liệu cho test).
    }
}
