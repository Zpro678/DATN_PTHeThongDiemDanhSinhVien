<?php

namespace App\Http\Controllers;

use App\Models\ClassMember;
use App\Models\CourseClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * StudentClassController
 *
 * Phụ trách: TV2 — Nguyễn Tuấn Khanh
 * Hiển thị danh sách lớp học mà sinh viên đang tham gia.
 * Filter theo tên môn, mã môn, trạng thái (đang học / đã hoàn thành).
 */
class StudentClassController extends Controller
{
    /**
     * Dashboard tổng quan của sinh viên.
     * URL: /student
     */
    public function dashboard()
    {
        return view('student.dashboard.studentDashboard');
    }

    /**
     * Danh sách lớp học của sinh viên hiện tại.
     * Hỗ trợ: ?search=... &status=active|archived
     * URL: /student/classes
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Lấy tất cả class_id mà sinh viên này là thành viên (active)
        // Ghép qua bảng class_members dựa trên user_id hoặc student_code
        $memberClassIds = ClassMember::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);

                // Nếu user có student_code thì cũng match theo student_code
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->pluck('class_id');

        // Query lớp học từ danh sách class_id trên
        $query = CourseClass::query()
            ->whereIn('id', $memberClassIds)
            ->with(['members'])
            ->withCount('members');

        // --- Filter: tìm kiếm theo tên môn / mã môn / giảng viên ---
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('subject_code', 'like', "%{$search}%")
                    ->orWhere('semester', 'like', "%{$search}%");
            });
        }

        // --- Filter: trạng thái (active / archived) ---
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'archived') {
                $query->where('status', 'archived');
            }
        }

        $classes = $query->latest()->get();

        // --- Tính tỷ lệ chuyên cần cho từng lớp ---
        // Hiện tại dữ liệu attendance chưa có → dùng giá trị mock để UI hoạt động.
        $classesWithStats = $classes->map(function ($class) use ($user) {
            $totalSessions = $class->total_sessions ?: 0;

            $attended = $totalSessions;
            $attendanceRate = $totalSessions > 0
                ? round(($attended / $totalSessions) * 100, 1)
                : 100.0;

            $isWarning = $attendanceRate < 80;

            return array_merge($class->toArray(), [
                'attended_sessions'  => $attended,
                'total_sessions_disp' => $totalSessions,
                'attendance_rate'    => $attendanceRate,
                'is_warning'         => $isWarning,
            ]);
        });

        return view('student.class.studentClassList', [
            'classes'         => $classesWithStats,
            'totalClasses'    => $classes->count(),
            'warningCount'    => $classesWithStats->where('is_warning', true)->count(),
            'search'          => $request->input('search', ''),
            'statusFilter'    => $request->input('status', ''),
        ]);
    }

    /**
     * Chi tiết một lớp học (sinh viên xem).
     * TODO: Trang này sẽ làm ở bước tiếp theo.
     */
    public function show($classId)
    {
        /* --- TẠM TẮT CHECK DB ĐỂ XEM GIAO DIỆN MẪU ---
        $class = CourseClass::findOrFail($classId);
        // Kiểm tra sinh viên có trong lớp không
        $user = Auth::user();
        $isMember = ClassMember::where('class_id', $class->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->exists();

        abort_unless($isMember, 403, 'Bạn không có quyền xem lớp học này.');
        */

        // Dữ liệu rỗng để không bị lỗi undefined variable $class
        $class = new CourseClass();

        // MOCK DATA CHO GIAO DIỆN CHI TIẾT
        $mockStats = [
            'total_sessions' => 15,
            'present' => 14,
            'late' => 1,
            'absent' => 0,
            'attendance_rate' => 96.8,
            'status_label' => 'Xếp loại chuyên cần tốt'
        ];

        $mockSessions = [
            ['id' => 15, 'date' => '04/06/2026', 'status' => 'TRỄ HỌC', 'time' => '07:35', 'note' => 'Đi muộn (Vào lớp trễ > 15 phút)', 'color' => 'amber'],
            ['id' => 14, 'date' => '28/05/2026', 'status' => 'CÓ MẶT', 'time' => '07:35', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
            ['id' => 1,  'date' => '26/02/2026', 'status' => 'CÓ MẶT', 'time' => '07:35', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
        ];

        return view('student.class.studentClassDetail', compact('class', 'mockStats', 'mockSessions'));
    }
}
