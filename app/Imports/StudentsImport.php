<?php

namespace App\Imports;

use App\Models\ClassMember;
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
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            // Index in startRow=2 means index 0 is row 2
            $actualRowNumber = $index + 2;

            $studentCode = trim((string) ($row[0] ?? ''));
            $fullName = trim((string) ($row[1] ?? ''));

            if (empty($studentCode) && empty($fullName)) {
                continue; // Skip empty rows
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
                ClassMember::create([
                    'class_id' => $this->classId,
                    'full_name' => $fullName,
                    'student_code' => strtoupper($studentCode),
                    'status' => 'active',
                ]);
            }

            $this->successCount++;
        }
    }
}
