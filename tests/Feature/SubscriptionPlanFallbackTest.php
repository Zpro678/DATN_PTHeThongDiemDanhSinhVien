<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Hạn mức của gói nằm ở quan hệ config (bảng plan_configs), KHÔNG phải cột của plans.
 * Nếu thiếu config thì accessor trả null và mọi giới hạn bị ép về 0 — giảng viên chưa
 * có gói sẽ bị chặn với thông báo vô lý kiểu "Lớp đã đạt giới hạn 0 sinh viên".
 * Bộ test này khoá lại hành vi mặc định cho cả hai đường thiếu config.
 */
class SubscriptionPlanFallbackTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SubscriptionService
    {
        return app(SubscriptionService::class);
    }

    public function test_user_without_subscription_gets_free_plan_limits(): void
    {
        // CSDL chưa có gói FREE -> planFor() phải dựng gói ảo kèm hạn mức.
        $user = User::factory()->create();

        $plan = $this->service()->planFor($user);

        $this->assertSame(Plan::TIER_FREE, $plan->plan_tier);
        $this->assertSame(Plan::DEFAULT_MAX_CLASSES, $plan->max_classes);
        $this->assertSame(Plan::DEFAULT_MAX_STUDENTS_PER_CLASS, $plan->max_students_per_class);
        $this->assertFalse($plan->can_export_excel);
    }

    public function test_service_limits_are_not_zero_for_user_without_subscription(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Plan::DEFAULT_MAX_STUDENTS_PER_CLASS, $this->service()->maxStudentsPerClass($user));
        $this->assertSame(Plan::DEFAULT_MAX_CLASSES, $this->service()->maxClasses($user));
        $this->assertTrue($this->service()->canCreateClass($user));
        $this->assertSame(Plan::DEFAULT_MAX_CLASSES, $this->service()->remainingClasses($user));
        $this->assertFalse($this->service()->canExportExcel($user));
    }

    public function test_saved_plan_missing_config_falls_back_instead_of_zero(): void
    {
        // Dữ liệu lỗi: gói FREE tồn tại nhưng thiếu bản ghi plan_configs.
        Plan::factory()->create(['plan_tier' => Plan::TIER_FREE]);
        $user = User::factory()->create();

        $this->assertSame(Plan::DEFAULT_MAX_STUDENTS_PER_CLASS, $this->service()->maxStudentsPerClass($user));
        $this->assertSame(Plan::DEFAULT_MAX_CLASSES, $this->service()->maxClasses($user));
    }

    public function test_configured_plan_limits_win_over_defaults(): void
    {
        // Có config thật -> mặc định không được lấn át số admin đã cấu hình.
        Plan::factory()
            ->withConfig(['max_classes' => 7, 'max_students_per_class' => 200, 'can_export_excel' => true])
            ->create(['plan_tier' => Plan::TIER_FREE]);

        $user = User::factory()->create();

        $this->assertSame(7, $this->service()->maxClasses($user));
        $this->assertSame(200, $this->service()->maxStudentsPerClass($user));
        $this->assertTrue($this->service()->canExportExcel($user));
    }
}
