<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingResilienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_get_returns_default_when_table_missing(): void
    {
        Schema::dropIfExists('settings');

        // Không được ném QueryException; trả về mặc định.
        $this->assertFalse((bool) Setting::get('maintenance_mode', false));
        $this->assertSame('fallback', Setting::get('app_name', 'fallback'));
    }

    public function test_app_does_not_500_when_settings_table_missing(): void
    {
        Schema::dropIfExists('settings');

        // Middleware toàn cục CheckSystemMaintenance gọi Setting::get trên mọi request;
        // thiếu bảng settings vẫn phải render bình thường (không 500).
        $this->get('/')->assertOk();
    }
}
