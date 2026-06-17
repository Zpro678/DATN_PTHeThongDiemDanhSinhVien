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
     */
    public function show($classId)
    {
        // MOCK DATA DETAILS CHO CÁC MÔN HỌC
        $mockClasses = [
            'DB101' => [
                'code' => 'DB101',
                'class_code' => 'DB101_L02',
                'name' => 'Thiết kế & Quản trị SQL',
                'teacher' => 'Thầy Lê Hoàng Đạt',
                'credits' => '3',
                'room' => 'Lab 3 Lầu 1',
                'schedule' => 'Thứ 4 (Tiết 1-3)',
                'time_window' => '07:30 - 10:15',
                'semester' => 'Học kỳ I (2025-2026)',
                'stats' => [
                    'total_sessions' => 16,
                    'present' => 15,
                    'late' => 1,
                    'absent' => 0,
                    'permitted' => 0,
                    'attendance_rate' => 93.8,
                    'status_label' => 'Xếp loại chuyên cần tốt'
                ],
                'sessions' => [
                    ['id' => 16, 'date' => '04/06/2026', 'status' => 'CÓ MẶT', 'time' => '08:15', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                    ['id' => 15, 'date' => '27/05/2026', 'status' => 'CÓ MẶT', 'time' => '08:02', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                    ['id' => 14, 'date' => '20/05/2026', 'status' => 'TRỄ HỌC', 'time' => '08:35', 'note' => 'Đi muộn (Vào lớp trễ > 15 phút)', 'color' => 'amber'],
                    ['id' => 13, 'date' => '13/05/2026', 'status' => 'CÓ MẶT', 'time' => '08:05', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                ]
            ],
            'PY201' => [
                'code' => 'PY201',
                'class_code' => 'PY201_L01',
                'name' => 'Phát triển Web Python',
                'teacher' => 'Cô Trần Thị Thu Thủy',
                'credits' => '3',
                'room' => 'Phòng thực hành máy tính 5',
                'schedule' => 'Thứ 5 (Tiết 4-6)',
                'time_window' => '10:30 - 13:15',
                'semester' => 'Học kỳ I (2025-2026)',
                'stats' => [
                    'total_sessions' => 15,
                    'present' => 13,
                    'late' => 2,
                    'absent' => 0,
                    'permitted' => 0,
                    'attendance_rate' => 86.7,
                    'status_label' => 'Xếp loại chuyên cần tốt'
                ],
                'sessions' => [
                    ['id' => 15, 'date' => '03/06/2026', 'status' => 'TRỄ HỌC', 'time' => '13:35', 'note' => 'Đi muộn (Vào lớp trễ > 15 phút)', 'color' => 'amber'],
                    ['id' => 14, 'date' => '28/05/2026', 'status' => 'CÓ MẶT', 'time' => '13:02', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                    ['id' => 13, 'date' => '21/05/2026', 'status' => 'TRỄ HỌC', 'time' => '13:45', 'note' => 'Đi muộn (Vào lớp trễ > 15 phút)', 'color' => 'amber'],
                    ['id' => 12, 'date' => '14/05/2026', 'status' => 'CÓ MẶT', 'time' => '13:05', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                ]
            ],
            'PH102' => [
                'code' => 'PH102',
                'class_code' => 'PH102_L04',
                'name' => 'Vật lý đại cương 2',
                'teacher' => 'Thầy Lâm Văn Tiến',
                'credits' => '2',
                'room' => 'Giảng đường lý thuyết B.302',
                'schedule' => 'Thứ 3 (Tiết 7-9)',
                'time_window' => '13:30 - 16:15',
                'semester' => 'Học kỳ I (2025-2026)',
                'stats' => [
                    'total_sessions' => 14,
                    'present' => 10,
                    'late' => 0,
                    'absent' => 4,
                    'permitted' => 0,
                    'attendance_rate' => 71.4,
                    'status_label' => 'Nguy cơ cấm thi (Cảnh báo đỏ)'
                ],
                'sessions' => [
                    ['id' => 14, 'date' => '02/06/2026', 'status' => 'VẮNG HỌC', 'time' => '--:--', 'note' => 'Vắng không phép (Hệ thống tự động ghi nhận)', 'color' => 'rose'],
                    ['id' => 13, 'date' => '26/05/2026', 'status' => 'VẮNG HỌC', 'time' => '--:--', 'note' => 'Vắng không phép (Hệ thống tự động ghi nhận)', 'color' => 'rose'],
                    ['id' => 12, 'date' => '19/05/2026', 'status' => 'CÓ MẶT', 'time' => '15:10', 'note' => 'Ghi nhận điểm danh thủ công bởi giảng viên', 'color' => 'emerald'],
                    ['id' => 11, 'date' => '12/05/2026', 'status' => 'VẮNG HỌC', 'time' => '--:--', 'note' => 'Vắng không phép (Hệ thống tự động ghi nhận)', 'color' => 'rose'],
                ]
            ],
            'NET301' => [
                'code' => 'NET301',
                'class_code' => 'NET301_L01',
                'name' => 'Lý thuyết Mạng Máy Tính',
                'teacher' => 'TS. Lê Quang Linh',
                'credits' => '3',
                'room' => 'A.205 (Lab A lầu 2)',
                'schedule' => 'Thứ 6 (Tiết 4-6)',
                'time_window' => '08:00 - 11:30',
                'semester' => 'Học kỳ I (2025-2026)',
                'stats' => [
                    'total_sessions' => 12,
                    'present' => 11,
                    'late' => 0,
                    'absent' => 0,
                    'permitted' => 1,
                    'attendance_rate' => 91.7,
                    'status_label' => 'Xếp loại chuyên cần tốt'
                ],
                'sessions' => [
                    ['id' => 12, 'date' => '29/05/2026', 'status' => 'CÓ PHÉP', 'time' => '08:00', 'note' => 'Nộp đơn nghỉ học được giảng viên phê duyệt', 'color' => 'blue'],
                    ['id' => 11, 'date' => '22/05/2026', 'status' => 'CÓ MẶT', 'time' => '08:05', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                    ['id' => 10, 'date' => '15/05/2026', 'status' => 'CÓ MẶT', 'time' => '08:02', 'note' => 'Xác nhận kiểm tra GPS & QR thành công', 'color' => 'emerald'],
                ]
            ],
        ];

        // Lấy thông tin lớp học tương ứng (mặc định lấy DB101 nếu không tìm thấy)
        $classData = $mockClasses[$classId] ?? $mockClasses['DB101'];

        // Chuyển thành đối tượng CourseClass giả lập để không bị lỗi view
        $class = new CourseClass();
        $class->id = 1;
        $class->code = $classData['code'];
        $class->name = $classData['name'];
        $class->total_sessions = $classData['stats']['total_sessions'];

        $mockStats = $classData['stats'];
        $mockSessions = $classData['sessions'];

        return view('student.class.studentClassDetail', compact('class', 'classData', 'mockStats', 'mockSessions'));
    }
}
