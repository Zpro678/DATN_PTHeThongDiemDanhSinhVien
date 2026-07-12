<?php

namespace App\Imports;

use App\Models\ClassMember;
use App\Models\ClassMeeting;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
use App\Jobs\ImportStudentsChunkJob;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements ToCollection, WithChunkReading, WithStartRow
{
    public $batch;
    public $classId;
    public $dateHeaders;
    public $meetingHeaders;
    public $emailColIndex;
    public $nameColIndex;
    public $headerRowNumber;
    public $authUserId;
    public $importToken;
    public $syncAttendance;

    public function __construct(
        $batch,
        $classId,
        $dateHeaders,
        $meetingHeaders,
        $emailColIndex,
        $nameColIndex,
        $headerRowNumber,
        $authUserId,
        $importToken,
        $syncAttendance = false
    ) {
        $this->batch = $batch;
        $this->classId = $classId;
        $this->dateHeaders = $dateHeaders;
        $this->meetingHeaders = $meetingHeaders;
        $this->emailColIndex = $emailColIndex;
        $this->nameColIndex = $nameColIndex;
        $this->headerRowNumber = $headerRowNumber;
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;
        $this->syncAttendance = $syncAttendance;
    }

    public function startRow(): int
    {
        return $this->headerRowNumber > 0 ? $this->headerRowNumber + 1 : 2;
    }

    public function collection(Collection $rows)
    {
        if ($this->emailColIndex === -1) {
            \App\Models\ImportError::create([
                'import_token' => $this->importToken,
                'error_message' => "Không tìm thấy cột 'Email' (bắt buộc phải có) ở dòng tiêu đề."
            ]);
            return;
        }

        $validRows = [];
        $actualRowNumber = $this->startRow(); // Tính dòng lỗi tạm thời

        foreach ($rows as $row) {
            $rowArray = $row instanceof Collection ? $row->toArray() : (array) $row;
            
            // Bỏ qua dòng rỗng
            $hasData = false;
            foreach ($rowArray as $cell) {
                if ($cell !== null && trim((string)$cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            
            if (!$hasData) {
                $actualRowNumber++;
                continue;
            }

            $fullName = $this->nameColIndex !== -1 ? trim((string) ($rowArray[$this->nameColIndex] ?? '')) : null;
            $email = $this->emailColIndex !== -1 ? trim((string) ($rowArray[$this->emailColIndex] ?? '')) : null;

            if (str_starts_with((string)$fullName, '=')) {
                $actualRowNumber++;
                continue;
            }

            if (empty($fullName) || empty($email)) {
                $msg = [];
                if (empty($fullName) && $this->nameColIndex !== -1) {
                    $nameColName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->nameColIndex + 1);
                    $msg[] = "Cột {$nameColName} (Họ Tên)";
                }
                if (empty($email) && $this->emailColIndex !== -1) {
                    $emailColName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($this->emailColIndex + 1);
                    $msg[] = "Cột {$emailColName} (Email)";
                }
                
                \App\Models\ImportError::create([
                    'import_token' => $this->importToken,
                    'row_index' => $actualRowNumber,
                    'error_message' => "Thiếu thông tin tại: " . implode(', ', $msg)
                ]);
                $actualRowNumber++;
                continue;
            }

            $isValidRow = true;
            foreach ($this->dateHeaders as $colIndex => $sessionId) {
                $statusChar = mb_strtolower(trim((string) ($rowArray[$colIndex] ?? '')));
                if ($statusChar !== '' && !in_array($statusChar, ['c', 'm', 'v', 'p'])) {
                    $colName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    \App\Models\ImportError::create([
                        'import_token' => $this->importToken,
                        'row_index' => $actualRowNumber,
                        'error_message' => "Cột {$colName}: Điểm danh sai ('{$statusChar}'). Chỉ dùng c, m, v, p"
                    ]);
                    $isValidRow = false;
                }
            }

            if ($isValidRow) {
                $validRows[] = $rowArray;
            }
            $actualRowNumber++;
        }

        if (!empty($validRows)) {
            $this->batch->add(new ImportStudentsChunkJob(
                $this->classId,
                $validRows,
                $this->dateHeaders,
                $this->meetingHeaders,
                $this->emailColIndex,
                $this->nameColIndex,
                $this->authUserId,
                $this->importToken,
                $this->syncAttendance
            ));
        }
    }

    public function chunkSize(): int
    {
        // 500 dòng/chunk: cân bằng giữa số job phải điều phối và bộ nhớ đọc file.
        // Với 10.000 SV => ~20 chunk job thay vì 200, giảm mạnh overhead hàng đợi.
        return 500;
    }
}
