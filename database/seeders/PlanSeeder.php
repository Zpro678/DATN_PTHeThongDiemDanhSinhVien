<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_tier' => Plan::TIER_FREE,
                'name' => 'Miễn phí',
                'description' => 'Gói cơ bản trải nghiệm tính năng điểm danh cốt lõi.',
                'price' => 0,
                'duration_days' => 0,
                'config' => [
                    'max_classes' => 2,
                    'max_students_per_class' => 50,
                    'can_export_excel' => false,
                ],
            ],
            [
                'plan_tier' => Plan::TIER_PRO,
                'name' => 'Chuyên nghiệp',
                'description' => 'Phù hợp cho giảng viên độc lập cần quản lý nhiều lớp.',
                'price' => 99000,
                'duration_days' => 30,
                'config' => [
                    'max_classes' => 10,
                    'max_students_per_class' => 100,
                    'can_export_excel' => true,
                ],
            ],
            [
                'plan_tier' => Plan::TIER_PREMIUM,
                'name' => 'Cao cấp',
                'description' => 'Giải pháp toàn diện cho tổ chức giáo dục quy mô lớn.',
                'price' => 299000,
                'duration_days' => 30,
                'config' => [
                    'max_classes' => 9999,
                    'max_students_per_class' => 9999,
                    'can_export_excel' => true,
                ],
            ],
        ];

        foreach ($plans as $data) {
            $config = $data['config'];
            unset($data['config']);

            $plan = Plan::query()->updateOrCreate(
                ['plan_tier' => $data['plan_tier']],
                array_merge($data, ['is_active' => true]),
            );

            $plan->config()->updateOrCreate(['plan_id' => $plan->id], $config);
        }
    }
}
