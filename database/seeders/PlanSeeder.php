<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::query()->updateOrCreate(
            ['code' => 'FREE'],
            [
                'name' => 'Miễn phí',
                'price' => 0,
                'max_classes' => 2,
                'can_export_excel' => false,
                'created_at' => now(),
            ],
        );

        Plan::query()->updateOrCreate(
            ['code' => 'PRO'],
            [
                'name' => 'Chuyên nghiệp',
                'price' => 199000,
                'max_classes' => 30,
                'can_export_excel' => true,
                'created_at' => now(),
            ],
        );
    }
}
