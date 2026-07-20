<?php

namespace App\Imports;

use App\Models\ClassMeeting;
use App\Models\ClassSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HeadingRowImport implements ToCollection, WithLimit, WithMultipleSheets
{
    public string $classId;
    public array $dateHeaders = [];
    public array $meetingHeaders = [];
    public int $emailColIndex = -1;
    public int $nameColIndex = -1;
    public int $headerRowNumber = -1;
    public array $errors = [];
    public array $warnings = [];

    public function __construct(string $classId)
    {
        $this->classId = $classId;
    }

    /**
     * Chỉ đọc sheet ĐẦU TIÊN — file mẫu có thể chứa nhiều sheet (Cơ bản/Đầy đủ).
     * Nếu đọc mọi sheet, cấu hình cột của sheet sau sẽ ghi đè sheet trước, khiến
     * emailColIndex/nameColIndex bị lệch và mọi dòng của sheet còn lại báo thiếu Email.
     */
    public function sheets(): array
    {
        return [0 => $this];
    }

    public function limit(): int
    {
        return 30; // Đọc tối đa 30 dòng đầu tiên để tìm header
    }

    public function collection(Collection $rows)
    {
        $header = null;

        // Tìm dòng tiêu đề
        foreach ($rows as $index => $row) {
            $isHeader = false;
            foreach ($row as $cellValue) {
                $cellStr = mb_strtolower(trim((string) $cellValue));
                if (empty($cellStr)) {
                    continue;
                }

                $keywords = [
                    'mã sv', 'mssv', 'mã học viên', 'mahv', 'email', 'họ và tên', 'họ tên', 'tên học viên', 'tên sinh viên'
                ];
                foreach ($keywords as $keyword) {
                    if (str_contains($cellStr, $keyword)) {
                        $isHeader = true;
                        $this->headerRowNumber = $index + 1;
                        break 2;
                    }
                }
            }

            if ($isHeader) {
                $header = $row;
                break;
            }
        }

        if ($this->headerRowNumber === -1) {
            $this->errors[] = "Không tìm thấy dòng tiêu đề chứa 'Họ và tên' hoặc 'Email'. Vui lòng kiểm tra lại file Excel.";
            return;
        }

        $dateSeenCounts = [];

        foreach ($header as $colIndex => $colValue) {
            $colValueLower = mb_strtolower(trim((string) $colValue));
            if (str_contains($colValueLower, 'email')) {
                $this->emailColIndex = $colIndex;
                continue;
            }
            if (str_contains($colValueLower, 'họ và tên') || str_contains($colValueLower, 'họ tên') || $colValueLower === 'tên' || $colValueLower === 'ten' || $colValueLower === 'name' || $colValueLower === 'full name' || $colValueLower === 'fullname') {
                $this->nameColIndex = $colIndex;
                continue;
            }
            // Bỏ qua các cột định danh cũ (MSSV/mã học viên/STT): hệ thống không còn lưu MSSV,
            // nhưng vẫn cần nhận diện để KHÔNG nhầm chúng thành cột ngày/buổi.
            if (str_contains($colValueLower, 'mã') || str_contains($colValueLower, 'mssv') || str_contains($colValueLower, 'ms') || str_contains($colValueLower, 'stt')) {
                continue;
            }

            if ($colIndex < max(1, $this->nameColIndex, $this->emailColIndex) && !preg_match('/^\d{1,2}[\/\-\.]\d{1,2}/', trim((string) $colValue))) {
                continue;
            }

            $colValue = trim((string) $colValue);
            if (empty($colValue)) continue;

            $colName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);

            $dateStr = $this->parseDate($colValue);
            if ($dateStr) {
                $dateSeenCounts[$dateStr] = ($dateSeenCounts[$dateStr] ?? 0) + 1;
                $nth = $dateSeenCounts[$dateStr];

                if ($nth > 1) {
                    $this->warnings[] = "Cột {$colName} ('{$colValue}') bị trùng ngày. Đã tạo tự động buổi học lặp lại.";
                }

                $meetingsOnDate = ClassMeeting::where('class_id', $this->classId)
                    ->where('date', $dateStr)
                    ->orderBy('id')
                    ->get();
                $meeting = $meetingsOnDate->skip($nth - 1)->first();

                if (!$meeting) {
                    $meeting = ClassMeeting::create([
                        'class_id' => $this->classId,
                        'date' => $dateStr,
                        'user_Created' => auth()->id(),
                        'name' => 'Buổi học ngày ' . Carbon::parse($dateStr)->format('d/m/Y') . ($nth > 1 ? " (Lần $nth)" : ''),
                        'status' => 'closed',
                    ]);
                }

                $session = ClassSession::firstOrCreate([
                    'meeting_id' => $meeting->id,
                ], [
                    'class_id' => $this->classId,
                    'date' => $dateStr,
                    'name' => 'Lần 1',
                    'created_by' => auth()->id(),
                    'status' => 'closed',
                ]);

                if ($meeting->status === 'active') {
                    $meeting->update(['status' => 'closed']);
                }

                if ($session->status === 'active') {
                    $session->update(['status' => 'closed']);
                }

                $this->dateHeaders[$colIndex] = $session->id;
                $this->meetingHeaders[$colIndex] = $meeting->id;
            } else {
                $lowerVal = mb_strtolower($colValue);
                $isSummaryColumn = preg_match('/^(tổng|có mặt|đi muộn|vắng|có phép|điểm trừ|chuyên cần|kết quả|c|m|v|p|cp|\%)/i', $lowerVal) 
                                   || in_array($lowerVal, ['c', 'm', 'v', 'p', 'cp', 'tc']);
                
                if (!$isSummaryColumn) {
                    $this->warnings[] = "Cột {$colName} ('{$colValue}') được bỏ qua vì không phải định dạng ngày học hoặc cột tổng kết.";
                }
            }
        }

        if ($this->emailColIndex === -1) {
            $this->errors[] = "Không tìm thấy cột 'Email' (bắt buộc phải có) ở dòng tiêu đề.";
        }
    }

    private function parseDate($value): ?string
    {
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
        
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})(?:[\/\-\.](\d{4}|\d{2}))?$/', $value, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = isset($matches[3]) ? $matches[3] : date('Y');
            if (strlen($year) == 2) $year = "20$year";
            return "$year-$month-$day";
        }
        
        return null;
    }
}
