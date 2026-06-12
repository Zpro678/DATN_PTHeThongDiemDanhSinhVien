<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSetting>
 */
class TenantSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'default_gps_radius' => fake()->numberBetween(30, 70),
            'default_absence_warning' => fake()->randomFloat(2, 10, 25),
        ];
    }
}
