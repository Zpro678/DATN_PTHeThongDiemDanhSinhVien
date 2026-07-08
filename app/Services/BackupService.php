<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Ifsnop\Mysqldump\Mysqldump;

class BackupService
{
    /**
     * Create a backup of the MySQL database using pure PHP (no external binaries needed)
     *
     * @return string The filename of the created backup
     * @throws \Exception
     */
    public function createBackup(): string
    {
        $fileName = 'backup_' . now()->format('Y_m_d_His') . '.sql';
        
        // Ensure backups directory exists
        if (!Storage::disk('local')->exists('backups')) {
            Storage::disk('local')->makeDirectory('backups');
        }

        $filePath = Storage::disk('local')->path('backups/' . $fileName);

        // Fetch DB configs
        $dbName = config('database.connections.mysql.database');
        $userName = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName}";
            $dump = new Mysqldump($dsn, $userName, $password, [
                'add-drop-table' => true,
            ]);
            $dump->start($filePath);
        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi tạo file sao lưu: ' . $e->getMessage());
        }

        return $fileName;
    }

    /**
     * Restore the MySQL database from a given backup file using PDO
     *
     * @param string $fileName
     * @return void
     * @throws \Exception
     */
    public function restoreBackup(string $fileName): void
    {
        $filePath = Storage::disk('local')->path('backups/' . $fileName);

        if (!File::exists($filePath)) {
            throw new \Exception('File sao lưu không tồn tại.');
        }

        try {
            // Đọc toàn bộ nội dung file SQL
            $sql = File::get($filePath);
            
            // Tắt kiểm tra khóa ngoại (foreign key checks) trước khi import
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Thực thi SQL
            DB::unprepared($sql);
            
            // Bật lại kiểm tra khóa ngoại
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $e) {
            // Đảm bảo bật lại FK checks dù có lỗi xảy ra
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            throw new \Exception('Lỗi phục hồi dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Get a list of all backup files, sorted by newest first
     *
     * @return array
     */
    public function getBackups(): array
    {
        if (!Storage::disk('local')->exists('backups')) {
            return [];
        }

        $files = Storage::disk('local')->files('backups');
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $basename = basename($file);
                $createdAt = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($file));
                
                if (preg_match('/backup_(\d{4}_\d{2}_\d{2}_\d{6})\.sql/', $basename, $matches)) {
                    try {
                        $createdAt = Carbon::createFromFormat('Y_m_d_His', $matches[1]);
                    } catch (\Exception $e) {}
                }

                $backups[] = [
                    'name' => $basename,
                    'size' => Storage::disk('local')->size($file),
                    'created_at' => $createdAt,
                ];
            }
        }

        // Sort descending by created_at
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Delete a backup file
     *
     * @param string $fileName
     * @return bool
     */
    public function deleteBackup(string $fileName): bool
    {
        return Storage::disk('local')->delete('backups/' . $fileName);
    }

    /**
     * Check if a backup has been made within the last $hours hours
     *
     * @param int $hours
     * @return bool
     */
    public function hasRecentBackup(int $hours = 12): bool
    {
        $backups = $this->getBackups();
        
        if (empty($backups)) {
            return false;
        }

        $latestBackup = $backups[0]; // because it's sorted newest first
        $now = now();
        
        return $latestBackup['created_at']->diffInHours($now) <= $hours;
    }
}
