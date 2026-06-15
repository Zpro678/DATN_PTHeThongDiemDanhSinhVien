<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Models\ClassMember;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

/**
 * Controller import sinh viên từ Excel/CSV
 * TV2 — Nguyễn Tuấn Khanh
 *
 * Cột bắt buộc trong file Excel/CSV:
 *   - mssv (hoặc student_code)
 *   - ho_ten (hoặc full_name)
 *
 * Cột tùy chọn:
 *   - (email — hiện tại DB chưa có cột email trong class_members)
 */
class ImportStudentController extends Controller
{
    /**
     * Hiển thị form import
     * Route: GET /classes/{class}/import
     */
    public function form(CourseClass $class)
    {
        $this->authorizeClass($class);

        return view('classes.import', compact('class'));
    }

    /**
     * Xử lý import file Excel/CSV
     * Route: POST /classes/{class}/import
     */
    public function store(Request $request, CourseClass $class)
    {
        $this->authorizeClass($class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'], // Tối đa 5MB
        ], [
            'file.required' => 'Vui lòng chọn file để import.',
            'file.mimes'    => 'Chỉ chấp nhận file Excel (.xlsx, .xls) hoặc CSV (.csv).',
            'file.max'      => 'Kích thước file tối đa 5MB.',
        ]);

        try {
            $rows = Excel::toArray(new HeadingRowImport, $request->file('file'));

            if (empty($rows[0])) {
                return back()->withErrors(['file' => 'File trống hoặc không đúng định dạng.']);
            }

            $data     = $rows[0];
            $errors   = [];
            $imported = 0;
            $updated  = 0;

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 vì row 1 là header

                // Lấy giá trị từ row (chấp nhận cả 2 tên cột)
                $studentCode = trim($row['mssv'] ?? $row['student_code'] ?? '');
                $fullName    = trim($row['ho_ten'] ?? $row['full_name'] ?? '');

                // Validate từng dòng
                if (empty($studentCode)) {
                    $errors[] = "Dòng {$rowNumber}: Thiếu MSSV.";
                    continue;
                }

                if (empty($fullName)) {
                    $errors[] = "Dòng {$rowNumber}: Thiếu họ tên (MSSV: {$studentCode}).";
                    continue;
                }

                // Upsert: nếu MSSV đã có thì update, chưa có thì tạo mới
                $existing = ClassMember::withTrashed()
                    ->where('class_id', $class->id)
                    ->where('student_code', $studentCode)
                    ->first();

                if ($existing) {
                    // Khôi phục nếu đã xóa mềm
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                    $existing->update([
                        'full_name' => $fullName,
                        'status'    => 'active',
                    ]);
                    $updated++;
                } else {
                    ClassMember::create([
                        'tenant_id'    => $class->tenant_id,
                        'class_id'     => $class->id,
                        'student_code' => $studentCode,
                        'full_name'    => $fullName,
                        'status'       => 'active',
                    ]);
                    $imported++;
                }
            }

            $message = "Import hoàn tất: {$imported} sinh viên mới, {$updated} sinh viên cập nhật.";

            if (!empty($errors)) {
                return redirect()
                    ->route('classes.members.index', $class)
                    ->with('success', $message)
                    ->with('import_errors', $errors);
            }

            return redirect()
                ->route('classes.members.index', $class)
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Lỗi đọc file: ' . $e->getMessage()]);
        }
    }

    /**
     * Tải file mẫu Excel
     * Route: GET /import/template
     */
    public function downloadTemplate()
    {
        $filename = 'mau_import_sinh_vien.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // BOM để Excel nhận UTF-8 đúng
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header
            fputcsv($file, ['mssv', 'ho_ten']);

            // Dòng mẫu
            fputcsv($file, ['SV001', 'Nguyễn Văn A']);
            fputcsv($file, ['SV002', 'Trần Thị B']);
            fputcsv($file, ['SV003', 'Lê Văn C']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =====================
    // Private helpers
    // =====================

    private function authorizeClass(CourseClass $class): void
    {
        if ($class->owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác với lớp học này.');
        }
    }
}
