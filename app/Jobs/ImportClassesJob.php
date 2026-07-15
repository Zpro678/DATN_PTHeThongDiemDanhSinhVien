<?php

namespace App\Jobs;

use App\Imports\MultiClassWithStudentsImport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportClassesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $filePath;
    public int $authUserId;
    public string $readerType;
    public string $storedPath;

    public function __construct(
        string $filePath,
        int $authUserId,
        string $readerType,
        string $storedPath
    ) {
        $this->filePath = $filePath;
        $this->authUserId = $authUserId;
        $this->readerType = $readerType;
        $this->storedPath = $storedPath;

        // Chạy trên queue 'imports' riêng biệt
        $this->onQueue('imports');
    }

    public function handle(): void
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        try {
            // Sử dụng Batch ID làm import token duy nhất toàn cục
            $importToken = $this->batch()->id;
            
            Excel::import(
                new MultiClassWithStudentsImport($this->filePath, $this->authUserId, $importToken),
                $this->filePath,
                null,
                $this->readerType
            );
        } finally {
            // Dọn dẹp tệp tin trong storage khi import kết thúc (kể cả khi lỗi)
            if ($this->storedPath && Storage::disk('local')->exists($this->storedPath)) {
                Storage::disk('local')->delete($this->storedPath);
            }
        }
    }
}
