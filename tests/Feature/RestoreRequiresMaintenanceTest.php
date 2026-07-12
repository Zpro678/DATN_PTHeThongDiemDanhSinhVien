<?php

namespace Tests\Feature;

use App\Livewire\Admin\Settings\MaintenanceSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class RestoreRequiresMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeBackupService(): void
    {
        // Không chạm DB thật khi test: giả BackupService.
        $mock = Mockery::mock(BackupService::class);
        $mock->shouldReceive('getBackups')->andReturn([]);
        $mock->shouldReceive('hasRecentBackup')->andReturn(true);
        $this->restoreMock = $mock;
        $this->instance(BackupService::class, $mock);
    }

    private $restoreMock;

    private function enableMaintenance(): void
    {
        Setting::set('maintenance_mode', true);
        Setting::set('maintenance_start', now()->subHour()->toDateTimeString());
        Setting::set('maintenance_end', now()->addHour()->toDateTimeString());
    }

    public function test_open_restore_modal_blocked_when_not_in_maintenance(): void
    {
        $this->fakeBackupService();
        $admin = User::factory()->superAdmin()->create();

        Livewire::actingAs($admin)->test(MaintenanceSettings::class)
            ->call('openRestoreModal', 'backup_x.sql')
            ->assertSet('showRestoreModal', false); // vẫn đóng vì chưa bảo trì
    }

    public function test_open_restore_modal_allowed_when_in_maintenance(): void
    {
        $this->fakeBackupService();
        $this->enableMaintenance();
        $admin = User::factory()->superAdmin()->create();

        Livewire::actingAs($admin)->test(MaintenanceSettings::class)
            ->call('openRestoreModal', 'backup_x.sql')
            ->assertSet('showRestoreModal', true)
            ->assertSet('selectedBackup', 'backup_x.sql');
    }

    public function test_restore_execution_blocked_when_not_in_maintenance(): void
    {
        $this->fakeBackupService();
        // Nếu cổng thủng, restoreBackup() sẽ được gọi -> fail test.
        $this->restoreMock->shouldNotReceive('restoreBackup');

        $admin = User::factory()->superAdmin()->create(['password' => Hash::make('secret123')]);

        Livewire::actingAs($admin)->test(MaintenanceSettings::class)
            ->set('selectedBackup', 'backup_x.sql')
            ->set('super_admin_password', 'secret123')
            ->call('restoreBackup')
            ->assertSet('showRestoreModal', false);
    }

    public function test_restore_execution_runs_when_in_maintenance(): void
    {
        $this->fakeBackupService();
        $this->enableMaintenance();
        // Đang bảo trì + mật khẩu đúng -> phải gọi restore đúng 1 lần.
        $this->restoreMock->shouldReceive('restoreBackup')->once()->with('backup_x.sql');

        $admin = User::factory()->superAdmin()->create(['password' => Hash::make('secret123')]);

        Livewire::actingAs($admin)->test(MaintenanceSettings::class)
            ->set('selectedBackup', 'backup_x.sql')
            ->set('super_admin_password', 'secret123')
            ->call('restoreBackup')
            ->assertHasNoErrors();
    }
}
