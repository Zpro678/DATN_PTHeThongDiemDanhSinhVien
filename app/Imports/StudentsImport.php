<?php

namespace App\Imports;

use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\AttendanceRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsImport implements ToCollection, WithStartRow, WithMultipleSheets
{
    protected int $classId;

    public array $errors = [];

    public int $successCount = 0;

    public function __construct(int $classId)
    {
        $this->classId = $classId;
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
        $headerRowNumber = 1;
        
        // Tìm dòng tiêu đề (bỏ qua các dòng trống phía trên)
        foreach ($rows as $index => $row) {
            $col0Str = mb_strtolower(trim((string) ($row[0] ?? '')));
            $col1Str = mb_strtolower(trim((string) ($row[1] ?? '')));
            
            // Nếu dòng này có chứa các từ khóa quen thuộc, đây chính là dòng tiêu đề
            $isHeader = false;
            // Bao gồm cả trường hợp có dấu, không dấu, viết tắt
            $keywords = [
                'mã', 'ma', 'mssv', 'mshv', 'mahv', 'định danh', 'dinh danh', 
                'id', 'student', 'họ', 'ho', 'tên', 'ten', 'name'
            ];
            foreach ($keywords as $keyword) {
                if (str_contains($col0Str, $keyword) || str_contains($col1Str, $keyword)) {
                    $isHeader = true;
                    $headerRowNumber = $index + 2; // +2 vì index bắt đầu từ 0 và startRow là 1
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
            $this->errors[] = "Không tìm thấy dòng tiêu đề chứa 'Mã SV' hoặc 'Họ và tên'. Vui lòng kiểm tra lại xem bạn có để thừa Sheet rỗng nào không, hoặc cột tiêu đề đã viết đúng chưa.";
            return;
        }

        $emailColIndex = -1;

        // Map column indices to ClassSession IDs
        foreach ($header as $colIndex => $colValue) {
            $colValueLower = mb_strtolower(trim((string) $colValue));
            if (str_contains($colValueLower, 'email')) {
                $emailColIndex = $colIndex;
                continue;
            }

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
            $email = $emailColIndex !== -1 ? trim((string) ($row[$emailColIndex] ?? '')) : null;

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

            $user = null;
            if ($email) {
                $user = \App\Models\User::where('email', $email)->first();
            }

            if ($member) {
                // Đã tồn tại -> Cập nhật tên và khôi phục nếu đang bị lưu trữ
                $updateData = [
                    'full_name' => $fullName,
                    'status' => 'active',
                ];
                if ($email) {
                    $updateData['email'] = $email;
                }
                if ($user && is_null($member->user_id)) {
                    $updateData['user_id'] = $user->id;
                }
                $member->update($updateData);
                $member->restore();
            } else {
                // Tạo mới
                $member = ClassMember::create([
                    'class_id' => $this->classId,
                    'full_name' => $fullName,
                    'email' => $email,
                    'student_code' => strtoupper($studentCode),
                    'user_id' => $user ? $user->id : null,
                    'status' => 'active',
                ]);
            }

            // Gửi email mời tạo tài khoản nếu học viên chưa có tài khoản
            if ($email && !$user) {
                $courseClass = \App\Models\CourseClass::find($this->classId);
                if ($courseClass) {
                    \Illuminate\Support\Facades\Mail::to($email)->send(
                        new \App\Mail\StudentImportNotificationMail(
                            $courseClass->name,
                            $courseClass->code,
                            strtoupper($studentCode),
                            $fullName,
                            $email
                        )
                    );
                }
            }
            
            // Xử lý điểm danh
            foreach ($dateHeaders as $colIndex => $sessionId) {
                $statusChar = mb_strtolower(trim((string) ($row[$colIndex] ?? '')));
                
                $status = 'pending';
                if ($statusChar === 'c') {
                    $status = 'present';
                } elseif ($statusChar === 'm') {
                    $status = 'late';
                } elseif ($statusChar === 'v') {
                    $status = 'absent';
                } elseif ($statusChar === 'p') {
                    $status = 'excused';
                } elseif ($statusChar !== '') {
                    $colName = trim((string) ($header[$colIndex] ?? "Cột $colIndex"));
                    $this->errors[] = "Dòng {$actualRowNumber}, Cột '{$colName}': Điểm danh sai ('{$statusChar}'). Chỉ dùng c, m, v, p.";
                    continue;
                }
                
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
