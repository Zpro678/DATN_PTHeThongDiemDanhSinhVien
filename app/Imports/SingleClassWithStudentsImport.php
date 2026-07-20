<?php

namespace App\Imports;

use App\Models\CourseClass;
use App\Models\ImportError;
use App\Jobs\ImportStudentsChunkJob;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SingleClassWithStudentsImport implements ToCollection
{
    protected string $sheetName;
    protected int $authUserId;
    protected string $importToken;

    public function __construct(string $sheetName, int $authUserId, string $importToken)
    {
        $this->sheetName = $sheetName;
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;
    }

    public function collection(Collection $rows)
    {
        // 1. Tìm dòng tiêu đề học viên (chứa cột Họ và tên & Email) trước tiên để chia mảng động
        $headerRowIndex = -1;
        $emailColIndex = -1;
        $nameColIndex = -1;
        $headerRow = null;

        foreach ($rows as $index => $row) {
            $rowNameIndex = -1;
            $rowEmailIndex = -1;

            foreach ($row as $colIndex => $cellValue) {
                $cellStr = mb_strtolower(trim((string) $cellValue));
                if (empty($cellStr)) continue;

                if (str_contains($cellStr, 'họ và tên') || str_contains($cellStr, 'họ tên') || $cellStr === 'tên' || $cellStr === 'name') {
                    $rowNameIndex = $colIndex;
                }
                if (str_contains($cellStr, 'email')) {
                    $rowEmailIndex = $colIndex;
                }
            }

            if ($rowNameIndex !== -1 && $rowEmailIndex !== -1) {
                $headerRowIndex = $index;
                $nameColIndex = $rowNameIndex;
                $emailColIndex = $rowEmailIndex;
                $headerRow = $row;
                break;
            }
        }

        if ($headerRowIndex === -1) {
            ImportError::create([
                'import_token' => $this->importToken,
                'error_message' => "Sheet [{$this->sheetName}]: Không tìm thấy dòng tiêu đề chứa cột 'Họ và tên' và 'Email'."
            ]);
            return;
        }

        $className = null;
        $classCode = null;
        $description = null;
        $totalSessions = 15;
        $absenceLimitPercent = 20.0;
        $deductLate = 0.5;
        $deductAbsent = 1.0;
        $deductExcused = 0.0;
        $requireApproval = false;

        // 2. Phân tích các dòng cấu hình lớp học (quét qua mọi ô của mọi dòng, ngoại trừ dòng tiêu đề)
        foreach ($rows as $index => $row) {
            if ($index === $headerRowIndex) {
                continue;
            }

            $rowArray = $row instanceof Collection ? $row->toArray() : (array) $row;
            foreach ($rowArray as $colIndex => $cellValue) {
                $cellStr = mb_strtolower(trim((string) $cellValue));
                if (empty($cellStr)) {
                    continue;
                }

                if (str_contains($cellStr, 'tên lớp')) {
                    $className = trim((string)($rowArray[$colIndex + 1] ?? ''));
                } elseif (str_contains($cellStr, 'mã lớp')) {
                    $classCode = trim((string)($rowArray[$colIndex + 1] ?? ''));
                } elseif (str_contains($cellStr, 'mô tả')) {
                    $description = trim((string)($rowArray[$colIndex + 1] ?? ''));
                } elseif (str_contains($cellStr, 'tổng số buổi')) {
                    $val = trim((string)($rowArray[$colIndex + 1] ?? ''));
                    $totalSessions = is_numeric($val) ? (int)$val : 15;
                } elseif (str_contains($cellStr, 'ngưỡng vắng')) {
                    $val = trim((string)($rowArray[$colIndex + 1] ?? ''));
                    $absenceLimitPercent = is_numeric($val) ? (float)$val : 20.0;
                } elseif (str_contains($cellStr, 'điểm trừ tương ứng') || str_contains($cellStr, 'điểm trừ')) {
                    $deductLate = is_numeric($rowArray[$colIndex + 1] ?? null) ? (float)$rowArray[$colIndex + 1] : 0.5;
                    $deductAbsent = is_numeric($rowArray[$colIndex + 2] ?? null) ? (float)$rowArray[$colIndex + 2] : 1.0;
                    $deductExcused = is_numeric($rowArray[$colIndex + 3] ?? null) ? (float)$rowArray[$colIndex + 3] : 0.0;
                } elseif (str_contains($cellStr, 'yêu cầu duyệt')) {
                    $val = trim((string)($rowArray[$colIndex + 1] ?? ''));
                    $requireApproval = ($val === '1' || mb_strtolower($val) === 'có');
                }
            }
        }

        // 3. Validate cấu hình lớp
        $errors = [];
        if (empty($className)) {
            $errors[] = "Tên lớp không được để trống.";
        }
        if ($totalSessions < 1 || $totalSessions > 200) {
            $errors[] = "Tổng số buổi học dự kiến phải từ 1 đến 200.";
        }
        if ($absenceLimitPercent < 0 || $absenceLimitPercent > 100) {
            $errors[] = "Ngưỡng vắng cho phép phải từ 0 đến 100%.";
        }
        if ($deductLate < 0 || $deductLate > 10 || $deductAbsent < 0 || $deductAbsent > 10 || $deductExcused < 0 || $deductExcused > 10) {
            $errors[] = "Điểm trừ chuyên cần phải nằm trong khoảng từ 0 đến 10.";
        }

        if (!empty($errors)) {
            foreach ($errors as $err) {
                ImportError::create([
                    'import_token' => $this->importToken,
                    'error_message' => "Sheet [{$this->sheetName}]: {$err}"
                ]);
            }
            return;
        }

        // 4. Kiểm tra gói giới hạn lớp
        $subscription = app(\App\Services\SubscriptionService::class);
        $user = \App\Models\User::find($this->authUserId);
        if (!$subscription->canCreateClass($user)) {
            $max = $subscription->maxClasses($user);
            ImportError::create([
                'import_token' => $this->importToken,
                'error_message' => "Sheet [{$this->sheetName}]: Gói dịch vụ của bạn chỉ cho phép tạo tối đa {$max} lớp. Vui lòng nâng cấp gói."
            ]);
            return;
        }

        // 5. Đếm số lượng cột ngày học thực tế để tự động điều chỉnh tổng số buổi học dự kiến nếu cần
        $dateHeadersCount = 0;
        if ($headerRow) {
            foreach ($headerRow as $colIndex => $colValue) {
                if ($colIndex === $nameColIndex || $colIndex === $emailColIndex) {
                    continue;
                }
                $colValueLower = mb_strtolower(trim((string) $colValue));
                if (str_contains($colValueLower, 'mã') || str_contains($colValueLower, 'mssv') || str_contains($colValueLower, 'ms') || str_contains($colValueLower, 'stt')) {
                    continue;
                }
                $colValue = trim((string) $colValue);
                if (empty($colValue)) continue;

                $dateStr = $this->parseDate($colValue);
                if ($dateStr) {
                    $dateHeadersCount++;
                }
            }
        }

        // Tạo lớp học
        $code = CourseClass::generateUniqueCode($classCode ?: 'CLS');
        $courseClass = CourseClass::create([
            'owner_user_id' => $this->authUserId,
            'name' => $className,
            'join_key' => $code,
            'class_code' => $classCode ?: CourseClass::generateUniqueClassCode($className ?: 'LHP'),
            'description' => $description ?: null,
            'deduct_late' => $deductLate,
            'deduct_absent' => $deductAbsent,
            'deduct_excused' => $deductExcused,
            'deduct_excused_absence' => $deductExcused > 0,
            'total_sessions' => max($totalSessions, $dateHeadersCount),
            'absence_limit_percent' => $absenceLimitPercent,
            'require_approval' => $requireApproval,
            'status' => 'active',
        ]);

        // Ghi audit log
        app(\App\Services\AuditLogService::class)->log('class_created', [
            'class_id'   => $courseClass->id,
            'table_name' => 'classes',
            'row_id'     => null,
            'new_values' => [
                'name'       => $courseClass->name,
                'join_key'   => $courseClass->join_key,
                'class_code' => $courseClass->class_code,
            ],
        ]);

        // Cập nhật số lớp import thành công vào cache
        $successKey = 'class_import_success_' . $this->importToken;
        cache()->put($successKey, (int)cache()->get($successKey, 0) + 1, now()->addHours(6));

        // Thiết lập biến 1-indexed của header row để tương thích với phần code phía sau
        $headerRowNumber = $headerRowIndex + 1;

        // 6. Xử lý các cột ngày học (điểm danh lịch sử) từ dòng tiêu đề
        $dateSeenCounts = [];
        $dateHeaders = [];
        $meetingHeaders = [];

        foreach ($headerRow as $colIndex => $colValue) {
            if ($colIndex === $nameColIndex || $colIndex === $emailColIndex) {
                continue;
            }

            $colValueLower = mb_strtolower(trim((string) $colValue));
            if (str_contains($colValueLower, 'mã') || str_contains($colValueLower, 'mssv') || str_contains($colValueLower, 'ms') || str_contains($colValueLower, 'stt')) {
                continue;
            }

            $colValue = trim((string) $colValue);
            if (empty($colValue)) continue;

            $dateStr = $this->parseDate($colValue);
            if ($dateStr) {
                $dateSeenCounts[$dateStr] = ($dateSeenCounts[$dateStr] ?? 0) + 1;
                $nth = $dateSeenCounts[$dateStr];

                // Tìm hoặc tạo Meeting & Session
                $meeting = \App\Models\ClassMeeting::create([
                    'class_id' => $courseClass->id,
                    'date' => $dateStr,
                    'user_Created' => $this->authUserId,
                    'name' => 'Buổi học ngày ' . \Illuminate\Support\Carbon::parse($dateStr)->format('d/m/Y') . ($nth > 1 ? " (Lần $nth)" : ''),
                    'status' => 'closed',
                ]);

                $session = \App\Models\ClassSession::create([
                    'class_id' => $courseClass->id,
                    'meeting_id' => $meeting->id,
                    'date' => $dateStr,
                    'name' => 'Lần 1',
                    'created_by' => $this->authUserId,
                    'status' => 'closed',
                ]);

                $dateHeaders[$colIndex] = $session->id;
                $meetingHeaders[$colIndex] = $meeting->id;
            }
        }

        // 7. Thu thập danh sách sinh viên bên dưới dòng tiêu đề
        $studentRows = [];
        $startStudentIndex = $headerRowNumber; // Dòng tiếp theo

        for ($i = $startStudentIndex; $i < $rows->count(); $i++) {
            $row = $rows->get($i);

            // Bỏ qua dòng trống
            $hasData = false;
            foreach ($row as $cell) {
                if ($cell !== null && trim((string)$cell) !== '') {
                    $hasData = true;
                    break;
                }
            }
            if (!$hasData) continue;

            // Bỏ qua các dòng cấu hình lớp học (nếu có bất kỳ ô nào chứa từ khóa cấu hình)
            $isConfigRow = false;
            foreach ($row as $cell) {
                $cellStr = mb_strtolower(trim((string)$cell));
                if (
                    str_contains($cellStr, 'tên lớp') ||
                    str_contains($cellStr, 'mã lớp') ||
                    str_contains($cellStr, 'mô tả') ||
                    str_contains($cellStr, 'tổng số buổi') ||
                    str_contains($cellStr, 'ngưỡng vắng') ||
                    str_contains($cellStr, 'điểm trừ') ||
                    str_contains($cellStr, 'yêu cầu duyệt') ||
                    str_contains($cellStr, 'cấu hình lớp') ||
                    str_contains($cellStr, 'cấu hình điểm trừ') ||
                    str_contains($cellStr, 'các trường có dấu')
                ) {
                    $isConfigRow = true;
                    break;
                }
            }
            if ($isConfigRow) {
                continue;
            }

            $fullName = trim((string) ($row[$nameColIndex] ?? ''));
            $email = trim((string) ($row[$emailColIndex] ?? ''));

            // Bỏ qua dòng trống hoàn toàn (cả Họ tên và Email đều trống)
            if (empty($fullName) && empty($email)) {
                continue;
            }

            // Bỏ qua dòng chú thích/hướng dẫn
            $combinedText = mb_strtolower($fullName . ' ' . $email);
            if (str_contains($combinedText, 'ký hiệu') || str_contains($combinedText, 'điểm danh') || str_contains($combinedText, 'có mặt') || str_contains($combinedText, 'vắng')) {
                continue;
            }

            if (empty($fullName) || empty($email)) {
                $msg = [];
                if (empty($fullName)) $msg[] = "Họ tên";
                if (empty($email)) $msg[] = "Email";

                ImportError::create([
                    'import_token' => $this->importToken,
                    'row_index' => $i + 1,
                    'error_message' => "Sheet [{$this->sheetName}]: Thiếu thông tin tại dòng " . ($i + 1) . " (" . implode(', ', $msg) . ")"
                ]);
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                ImportError::create([
                    'import_token' => $this->importToken,
                    'row_index' => $i + 1,
                    'error_message' => "Sheet [{$this->sheetName}]: Email '{$email}' không hợp lệ tại dòng " . ($i + 1)
                ]);
                continue;
            }

            $studentRows[] = $row->toArray();
        }

        // 8. Đẩy xử lý danh sách học viên chạy song song trong queue sử dụng ImportStudentsChunkJob
        if (!empty($studentRows)) {
            // Lưu các meetingId vào cache để đồng bộ sau khi toàn bộ batch kết thúc
            if (!empty($meetingHeaders)) {
                $cacheKey = 'import_meetings_' . $this->importToken;
                $existing = cache()->get($cacheKey, []);
                $merged = array_unique(array_merge($existing, $meetingHeaders));
                cache()->put($cacheKey, $merged, now()->addHours(2));
            }

            $chunkSize = 500;
            $chunks = array_chunk($studentRows, $chunkSize);
            $jobs = [];
            foreach ($chunks as $chunk) {
                $jobs[] = new ImportStudentsChunkJob(
                    $courseClass->id,
                    $chunk,
                    $dateHeaders,
                    $meetingHeaders,
                    $emailColIndex,
                    $nameColIndex,
                    $this->authUserId,
                    $this->importToken,
                    true // đồng bộ điểm danh
                );
            }

            // Tìm batch hiện tại và thêm các job vào để chạy song song
            $batch = \Illuminate\Support\Facades\Bus::findBatch($this->importToken);
            if ($batch) {
                $batch->add($jobs);
            } else {
                foreach ($jobs as $job) {
                    dispatch($job);
                }
            }
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
