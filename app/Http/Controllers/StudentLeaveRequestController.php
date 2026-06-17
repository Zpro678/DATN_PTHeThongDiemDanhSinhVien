<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentLeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests and the submission form.
     */
    public function index()
    {
        $user = Auth::user();

        // Lấy tất cả memberships của sinh viên này
        $memberships = ClassMember::query()
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->get();

        $memberClassIds = $memberships->pluck('class_id');

        // Lấy danh sách lớp học active
        $classes = CourseClass::whereIn('id', $memberClassIds)
            ->where('status', 'active')
            ->get();

        // Lấy lịch sử yêu cầu của sinh viên này
        $leaveRequests = LeaveRequest::query()
            ->whereIn('class_member_id', $memberships->pluck('id'))
            ->with(['classSession.courseClass', 'reviewer'])
            ->latest('id')
            ->paginate(10);

        return view('student.leave.studentLeaveRequest', compact('classes', 'leaveRequests'));
    }

    /**
     * Get sessions for a specific class to populate dropdown.
     */
    public function getSessions($classId)
    {
        $user = Auth::user();

        // Bảo mật: kiểm tra sinh viên có thuộc lớp này không
        $isMember = ClassMember::where('class_id', $classId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->exists();

        if (!$isMember) {
            return response()->json([], 403);
        }

        // Lấy danh sách buổi học
        $sessions = ClassSession::where('class_id', $classId)
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get(['id', 'name', 'date', 'start_time']);

        // Định dạng lại ngày để hiển thị đẹp mắt
        $formattedSessions = $sessions->map(function ($session) {
            $dateStr = $session->date ? $session->date->format('d/m/Y') : '';
            $timeStr = $session->start_time ? substr($session->start_time, 0, 5) : '';
            return [
                'id' => $session->id,
                'name' => "{$session->name} ({$dateStr}" . ($timeStr ? " - {$timeStr}" : "") . ")",
            ];
        });

        return response()->json($formattedSessions);
    }

    /**
     * Store a newly created leave request in storage.
     */
    public function store(StoreLeaveRequest $request)
    {
        $user = Auth::user();
        $classId = $request->input('class_id');

        // Lấy membership tương ứng của sinh viên trong lớp này
        $membership = ClassMember::where('class_id', $classId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->student_code) {
                    $q->orWhere('student_code', $user->student_code);
                }
            })
            ->where('status', 'active')
            ->firstOrFail();

        // Xử lý upload file minh chứng
        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $proofPath = $file->storeAs('proofs', $filename, 'public');
        }

        // Tạo đơn xin nghỉ
        LeaveRequest::create([
            'tenant_id' => $membership->tenant_id,
            'class_member_id' => $membership->id,
            'class_session_id' => $request->input('class_session_id'),
            'reason' => $request->input('reason'),
            'proof_image' => $proofPath,
            'status' => 'pending',
        ]);

        return redirect()->route('student.leaves.index')->with('success', 'Đơn xin nghỉ học đã được gửi thành công và đang chờ duyệt.');
    }
}
