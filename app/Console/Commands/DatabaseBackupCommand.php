<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sao lưu cơ sở dữ liệu và xóa các bản sao lưu cũ';

    /**
     * Số lượng bản sao lưu tối đa được giữ lại.
     */
    const MAX_BACKUPS_TO_KEEP = 7;

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService)
    {
        $this->info('Bắt đầu quá trình sao lưu cơ sở dữ liệu...');
        
        try {
            // 1. Tạo bản sao lưu mới
            $fileName = $backupService->createBackup();
            $this->info("Đã tạo thành công bản sao lưu: {$fileName}");
            Log::info("DatabaseBackupCommand: Đã tạo thành công bản sao lưu: {$fileName}");

            // 2. Lấy danh sách tất cả các bản sao lưu hiện có (đã được sắp xếp mới nhất lên đầu)
            $backups = $backupService->getBackups();
            
            // 3. Nếu số lượng vượt quá mức cho phép, xóa các bản cũ nhất
            if (count($backups) > self::MAX_BACKUPS_TO_KEEP) {
                // Lấy các bản sao lưu từ vị trí MAX_BACKUPS_TO_KEEP trở đi để xóa
                $backupsToDelete = array_slice($backups, self::MAX_BACKUPS_TO_KEEP);
                
                foreach ($backupsToDelete as $backup) {
                    $deleted = $backupService->deleteBackup($backup['name']);
                    if ($deleted) {
                        $this->line("Đã dọn dẹp bản sao lưu cũ: {$backup['name']}");
                        Log::info("DatabaseBackupCommand: Đã xóa bản sao lưu cũ: {$backup['name']}");
                    }
                }
            }
            
            $this->info('Hoàn tất quá trình sao lưu!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Lỗi khi sao lưu cơ sở dữ liệu: ' . $e->getMessage());
            Log::error('DatabaseBackupCommand Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
