<?php

namespace App\Jobs;

use App\Imports\StudentsImport;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class StartImportJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $filePath;
    public $classId;
    public $dateHeaders;
    public $meetingHeaders;
    public $emailColIndex;
    public $nameColIndex;
    public $codeColIndex;
    public $headerRowNumber;
    public $authUserId;
    public $readerType;
    public $importToken;

    public function __construct(
        $filePath,
        $classId,
        $dateHeaders,
        $meetingHeaders,
        $emailColIndex,
        $nameColIndex,
        $codeColIndex,
        $headerRowNumber,
        $authUserId,
        $readerType,
        $importToken
    ) {
        $this->filePath = $filePath;
        $this->classId = $classId;
        $this->dateHeaders = $dateHeaders;
        $this->meetingHeaders = $meetingHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->nameColIndex = $nameColIndex;
        $this->codeColIndex = $codeColIndex;
        $this->headerRowNumber = $headerRowNumber;
        $this->authUserId = $authUserId;
        $this->readerType = $readerType;
        $this->importToken = $importToken;

        // Hàng đợi riêng cho import để cô lập tài nguyên (worker phải nghe queue này —
        // xem start-dev.bat: --queue=imports,mails,default).
        $this->onQueue('imports');
    }

    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        $import = new StudentsImport(
            $this->batch(),
            $this->classId,
            $this->dateHeaders,
            $this->meetingHeaders,
            $this->emailColIndex,
            $this->nameColIndex,
            $this->codeColIndex,
            $this->headerRowNumber,
            $this->authUserId,
            $this->batch()->id
        );

        Excel::import($import, $this->filePath, null, $this->readerType);
    }
}
