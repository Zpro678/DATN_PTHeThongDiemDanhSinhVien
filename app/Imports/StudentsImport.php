<?php

namespace App\Imports;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class StudentsImport implements ToCollection, WithStartRow
{
    protected int $classId;

    public array $errors = [];

    public int $successCount = 0;

    public function __construct(int $classId)
    {
        $this->classId = $classId;
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
        $header = null;
        $dateHeaders = [];
        
        // Tìm dòng tiêu đề (bỏ qua các dòng trống phía trên)
        foreach ($rows as $index => $row) {
            $col0 = trim((string) ($row[0] ?? ''));
            $col1 = trim((string) ($row[1] ?? ''));
            
            // Nếu dòng này có chứa các từ khóa quen thuộc, đây chính là dòng tiêu đề
            $isHeader = false;
            $keywords = ['mã', 'mssv', 'định danh', 'id', 'student', 'họ', 'tên', 'name'];
            foreach ($keywords as $keyword) {
                if (mb_stripos($col0, $keyword) !== false || mb_stripos($col1, $keyword) !== false) {
                    $isHeader = true;
                    break;
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

        if (!$header) {
            $this->errors[] = "Không tìm thấy dòng tiêu đề chứa 'Mã SV' hoặc 'Họ và tên'. Vui lòng kiểm tra lại cấu trúc file.";
            return;
        }

        // Map column indices to ClassSession IDs
        foreach ($header as $colIndex => $colValue) {
            if ($colIndex < 2) continue;
            
            $colValue = trim((string) $colValue);
            if (empty($colValue)) continue;
            
            $dateStr = $this->parseDate($colValue);
            if ($dateStr) {
                $session = ClassSession::firstOrCreate([
                    'class_id' => $this->classId,
                    'date' => $dateStr,
                ], [
                    'created_by' => auth()->id(),
                    'name' => 'Điểm danh ngày ' . Carbon::parse($dateStr)->format('d/m/Y'),
                    'status' => 'closed',
                ]);
                
                // Đảm bảo buổi điểm danh được chốt nếu nó đang active
                if ($session->status === 'active') {
                    $session->update(['status' => 'closed']);
                }

                $dateHeaders[$colIndex] = $session->id;
            }
        }

        foreach ($rows as $index => $row) {
            // Index in startRow=1 means index 0 is row 2
            $actualRowNumber = $index + 2;

            $studentCode = trim((string) ($row[0] ?? ''));
            $fullName = trim((string) ($row[1] ?? ''));

            if (empty($studentCode) && empty($fullName)) {
                continue; // Skip empty rows
            }

            // Bỏ qua các dòng dữ liệu rác (ví dụ Mã SV lại là một ngày tháng, hoặc Tên là công thức Excel)
            if (preg_match('/^\d{1,2}[\/\-\.]\d{1,2}/', $studentCode) || str_starts_with($fullName, '=')) {
                continue;
            }

            if (empty($studentCode) || empty($fullName)) {
                $this->errors[] = "Dòng {$actualRowNumber}: Thiếu thông tin";

                continue;
            }

            // Upsert (Cập nhật nếu trùng Mã SV trong lớp, nếu không thì Tạo mới)
            // Lấy sinh viên (kể cả đã bị đưa vào thùng rác - lưu trữ)
            $member = ClassMember::withTrashed()
                ->where('class_id', $this->classId)
                ->where('student_code', strtoupper($studentCode))
                ->first();

            if ($member) {
                // Đã tồn tại -> Cập nhật tên và khôi phục nếu đang bị lưu trữ
                $member->update([
                    'full_name' => $fullName,
                    'status' => 'active',
                ]);
                $member->restore();
            } else {
                // Tạo mới
                $member = ClassMember::create([
                    'class_id' => $this->classId,
                    'full_name' => $fullName,
                    'student_code' => strtoupper($studentCode),
                    'status' => 'active',
                ]);
            }
            
            // Xử lý điểm danh
            foreach ($dateHeaders as $colIndex => $sessionId) {
                $statusChar = mb_strtolower(trim((string) ($row[$colIndex] ?? '')));
                
                $status = 'pending';
                if ($statusChar === 'c') $status = 'present';
                elseif ($statusChar === 'm') $status = 'late';
                elseif ($statusChar === 'v') $status = 'absent';
                
                if ($statusChar !== '') {
                    AttendanceRecord::updateOrCreate([
                        'class_session_id' => $sessionId,
                        'class_member_id' => $member->id,
                    ], [
                        'status' => $status,
                        'is_verified' => $member->user_id !== null,
                    ]);
                }
            }

            $this->successCount++;
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
