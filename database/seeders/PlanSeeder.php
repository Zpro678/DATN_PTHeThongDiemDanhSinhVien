<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['plan_tier' => 'FREE'],
            [
                'name' => 'Miễn phí',
                'description' => 'Gói cơ bản trải nghiệm tính năng điểm danh cốt lõi.',
                'price' => 0,
                'duration_days' => 0,
                'max_classes' => 2,
                'max_students_per_class' => 50,
                'max_gps_radius' => 50,
                'can_export_excel' => false,
                'api_access' => false,
                'support_level' => 'Cộng đồng',
                'is_active' => true,
                'features' => [
                    'Quản lý tối đa 2 lớp học',
                    '50 sinh viên mỗi lớp',
                    'Điểm danh qua QR code',
                    'Bán kính định vị tối đa 50m'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        Plan::query()->updateOrCreate(
            ['plan_tier' => 'PRO'],
            [
                'name' => 'Chuyên nghiệp',
                'description' => 'Phù hợp cho giảng viên độc lập cần quản lý nhiều lớp.',
                'price' => 99000,
                'duration_days' => 30,
                'max_classes' => 10,
                'max_students_per_class' => 100,
                'max_gps_radius' => 100,
                'can_export_excel' => true,
                'api_access' => false,
                'support_level' => 'Cơ bản',
                'is_active' => true,
                'features' => [
                    'Quản lý tối đa 10 lớp học',
                    '100 sinh viên mỗi lớp',
                    'Xuất báo cáo Excel/CSV',
                    'Bán kính định vị lên tới 100m'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        Plan::query()->updateOrCreate(
            ['plan_tier' => 'ENTERPRISE'],
            [
                'name' => 'Doanh nghiệp',
                'description' => 'Giải pháp toàn diện cho tổ chức giáo dục quy mô lớn.',
                'price' => 299000,
                'duration_days' => 30,
                'max_classes' => 9999, // Unlimitted basically
                'max_students_per_class' => 9999,
                'max_gps_radius' => 500,
                'can_export_excel' => true,
                'api_access' => true,
                'support_level' => 'Ưu tiên 24/7',
                'is_active' => true,
                'features' => [
                    'Không giới hạn lớp & sinh viên',
                    'Hỗ trợ tích hợp API hệ thống',
                    'Bán kính định vị lên tới 500m',
                    'Hỗ trợ kỹ thuật ưu tiên 24/7'
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
