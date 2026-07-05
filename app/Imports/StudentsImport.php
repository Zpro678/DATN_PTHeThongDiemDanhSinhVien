<?php

namespace App\Imports;

use App\Models\ClassMember;
use App\Models\ClassMeeting;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    protected string $classId;
    protected ?string $importToken;
    protected bool $syncAttendance;

    public array $errors = [];

    public int $successCount = 0;

    public function __construct(string $classId, ?string $importToken = null, bool $syncAttendance = false)
    {
        $this->classId = $classId;
        $this->importToken = $importToken;
        $this->syncAttendance = $syncAttendance;
    }

    public function sheets(): array
    {
        return [
            0 => $this, // Chỉ import Sheet đầu tiên
        ];
    }

    /**
     * Bỏ qua dòng tiêu đề
     */
    public function startRow(): int
    {
        return 1;
    }

    public function collection(Collection $rows)
    {
        $hasData = false;
        foreach ($rows as $r) {
            $cells = $r instanceof \Illuminate\Support\Collection ? $r->toArray() : (array)$r;
            foreach ($cells as $cell) {
                if ($cell !== null && trim((string)$cell) !== '') {
                    $hasData = true;
                    break 2;
                }
            }
        }
        
        if (!$hasData) {
            return; // Bỏ qua sheet hoàn toàn rỗng
        }

        $header = null;
        $dateHeaders = [];
        $meetingHeaders = [];
        $headerRowNumber = 1;
        
        // Tìm dòng tiêu đề (bỏ qua các dòng trống phía trên)
        foreach ($rows as $index => $row) {
            $isHeader = false;
            // Duyệt qua tất cả các cột trong dòng để tìm từ khóa tiêu đề
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
                        $headerRowNumber = $index + 2;
                        break 2;
                    }
                }
            }

            if ($isHeader) {
                $header = $row;
                $rows->forget($index); // Xóa dòng tiêu đề khỏi danh sách để các dòng sau là dữ liệu
                break;
            }
            
            // Xóa các dòng trống hoặc rác nằm trên dòng tiêu đề
            $rows->forget($index);
        }

        if ($headerRowNumber === -1) {
            $this->errors[] = "Không tìm thấy dòng tiêu đề chứa 'Họ và tên' hoặc 'Email'. Vui lòng kiểm tra lại xem bạn có để thừa Sheet rỗng nào không, hoặc cột tiêu đề đã viết đúng chưa.";
            return;
        }

        $emailColIndex = -1;
        $nameColIndex = -1;
        $codeColIndex = -1;
        $dateSeenCounts = []; // Theo dõi số lần xuất hiện của một ngày để xử lý nhiều buổi/ngày

        // Map column indices to ClassSession IDs
        foreach ($header as $colIndex => $colValue) {
            $colValueLower = mb_strtolower(trim((string) $colValue));
            if (str_contains($colValueLower, 'email')) {
                $emailColIndex = $colIndex;
                continue;
            }
            if (str_contains($colValueLower, 'họ và tên') || str_contains($colValueLower, 'họ tên') || $colValueLower === 'tên' || $colValueLower === 'ten' || $colValueLower === 'name' || $colValueLower === 'full name' || $colValueLower === 'fullname') {
                $nameColIndex = $colIndex;
                continue;
            }
            if (str_contains($colValueLower, 'mã') || str_contains($colValueLower, 'mssv') || str_contains($colValueLower, 'ms') || str_contains($colValueLower, 'stt')) {
                $codeColIndex = $colIndex;
                continue;
            }

            if ($colIndex < max(1, $nameColIndex, $codeColIndex, $emailColIndex) && !preg_match('/^\d{1,2}[\/\-\.]\d{1,2}/', trim((string) $colValue))) continue;
            
            $colValue = trim((string) $colValue);
            if (empty($colValue)) continue;
            
            $dateStr = $this->parseDate($colValue);
            if ($dateStr) {
                $dateSeenCounts[$dateStr] = ($dateSeenCounts[$dateStr] ?? 0) + 1;
                $nth = $dateSeenCounts[$dateStr];

                if ($nth > 1) {
                    $this->errors[] = "[Cảnh báo] Cột '{$colValue}' bị trùng ngày. Hệ thống tự động tạo thành 'Buổi học ngày " . Carbon::parse($dateStr)->format('d/m/Y') . " (Lần {$nth})'.";
                }

                // Tìm buổi học thứ $nth trong ngày này, nếu không có thì tạo mới
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

                // Tạo hoặc lấy phiên đầu tiên của buổi
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
                
                // Đảm bảo buổi điểm danh được chốt nếu nó đang active
                if ($session->status === 'active') {
                    $session->update(['status' => 'closed']);
                }

                $dateHeaders[$colIndex] = $session->id;
                $meetingHeaders[$colIndex] = $meeting->id;
            } else {
                // Silently ignore known summary columns from export
                $lowerVal = mb_strtolower($colValue);
                $isSummaryColumn = preg_match('/^(tổng|có mặt|đi muộn|vắng|có phép|điểm trừ|chuyên cần|kết quả|c|m|v|p|cp|\%)/i', $lowerVal) 
                                   || in_array($lowerVal, ['c', 'm', 'v', 'p', 'cp', 'tc']);
                
                if (!$isSummaryColumn) {
                    $this->errors[] = "Cột '{$colValue}' bị bỏ qua vì không đúng định dạng ngày tháng.";
                }
            }
        }

        if ($emailColIndex === -1) {
            $this->errors[] = "Không tìm thấy cột 'Email' (bắt buộc phải có) ở dòng tiêu đề.";
            return;
        }

        $validRows = [];
        foreach ($rows as $index => $row) {
            // Index in startRow=1 means index 0 is row 2
            $actualRowNumber = $index + 2;

            $studentCode = $codeColIndex !== -1 ? trim((string) ($row[$codeColIndex] ?? '')) : null;
            $fullName = $nameColIndex !== -1 ? trim((string) ($row[$nameColIndex] ?? '')) : null;
            $email = $emailColIndex !== -1 ? trim((string) ($row[$emailColIndex] ?? '')) : null;

            if (empty($fullName) && empty($email)) {
                continue; // Skip empty rows
            }

            // Bỏ qua các dòng dữ liệu rác (ví dụ Tên là công thức Excel)
            if (str_starts_with($fullName, '=')) {
                continue;
            }

            if (empty($fullName) || empty($email)) {
                $this->errors[] = "Dòng {$actualRowNumber}: Thiếu thông tin Họ tên hoặc Email";
                continue;
            }

            // Kiểm tra trước lỗi nhập liệu cột ngày học điểm danh để báo lỗi nếu có
            $isValidRow = true;
            foreach ($dateHeaders as $colIndex => $sessionId) {
                $statusChar = mb_strtolower(trim((string) ($row[$colIndex] ?? '')));
                // c=có mặt, m=muộn, v=vắng, p=có phép.
                if ($statusChar !== '' && !in_array($statusChar, ['c', 'm', 'v', 'p'])) {
                    $colName = trim((string) ($header[$colIndex] ?? "Cột $colIndex"));
                    $this->errors[] = "Dòng {$actualRowNumber}, Cột '{$colName}': Điểm danh sai ('{$statusChar}'). Chỉ dùng c, m, v, p.";
                    $isValidRow = false;
                }
            }

            if ($isValidRow) {
                $validRows[] = $row instanceof \Illuminate\Support\Collection ? $row->toArray() : (array)$row;
            }
        }

        if (!empty($validRows)) {
            // Chia nhỏ danh sách thành các cụm 50 sinh viên và đẩy vào hàng đợi Job xử lý nền
            $chunks = array_chunk($validRows, 50);
            
            if ($this->importToken) {
                \Illuminate\Support\Facades\Cache::put("import_progress_{$this->importToken}", [
                    'total_chunks' => count($chunks),
                    'completed_chunks' => 0,
                    'total_rows' => count($validRows),
                    'processed_rows' => 0,
                    'status' => 'processing'
                ], now()->addMinutes(15));
            }

            foreach ($chunks as $chunk) {
                \App\Jobs\ImportStudentsChunkJob::dispatch(
                    $this->classId,
                    $chunk,
                    $dateHeaders,
                    $meetingHeaders,
                    $emailColIndex,
                    $nameColIndex,
                    $codeColIndex,
                    (int) auth()->id(),
                    $this->importToken,
                    $this->syncAttendance
                );
            }
            $this->successCount = count($validRows);
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
