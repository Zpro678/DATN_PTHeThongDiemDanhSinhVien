<?php

namespace App\Http\Controllers;

use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Http\Requests\StoreClassMemberRequest;
use App\Http\Requests\UpdateClassMemberRequest;
use Illuminate\Http\Request;

/**
 * Controller quản lý sinh viên trong lớp học
 * TV2 — Nguyễn Tuấn Khanh
 * Route resource: classes.members
 */
class ClassMemberController extends Controller
{
    /**
     * Danh sách sinh viên trong lớp
     * Route: GET /classes/{class}/members
     */
    public function index(Request $request, CourseClass $class)
    {
        $this->authorizeClass($class);

        $query = $class->members()->latest();

        // Tìm kiếm theo MSSV hoặc họ tên
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('student_code', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $members = $query->paginate(20)->withQueryString();

        return view('classes.members.index', compact('class', 'members'));
    }

    /**
     * Form thêm sinh viên
     * Route: GET /classes/{class}/members/create
     */
    public function create(CourseClass $class)
    {
        $this->authorizeClass($class);

        return view('classes.members.create', compact('class'));
    }

    /**
     * Lưu sinh viên mới vào lớp
     * Route: POST /classes/{class}/members
     */
    public function store(StoreClassMemberRequest $request, CourseClass $class)
    {
        $this->authorizeClass($class);

        // Kiểm tra MSSV đã có trong lớp chưa (kể cả đã xóa mềm)
        $existing = ClassMember::withTrashed()
            ->where('class_id', $class->id)
            ->where('student_code', $request->student_code)
            ->first();

        if ($existing && $existing->trashed()) {
            // Khôi phục nếu đã bị xóa mềm
            $existing->restore();
            $existing->update($request->validated());
            return redirect()
                ->route('classes.members.index', $class)
                ->with('success', "Đã khôi phục và cập nhật sinh viên {$request->student_code}.");
        }

        $class->members()->create([
            'tenant_id'    => $class->tenant_id,
            'student_code' => $request->student_code,
            'full_name'    => $request->full_name,
            'status'       => $request->status ?? 'active',
        ]);

        return redirect()
            ->route('classes.members.index', $class)
            ->with('success', "Thêm sinh viên {$request->full_name} thành công!");
    }

    /**
     * Chi tiết sinh viên
     * Route: GET /classes/{class}/members/{member}
     */
    public function show(CourseClass $class, ClassMember $member)
    {
        $this->authorizeClass($class);
        $this->authorizeMember($class, $member);

        $member->load(['records.classSesion', 'leaveRequests']);

        return view('classes.members.show', compact('class', 'member'));
    }

    /**
     * Form sửa thông tin sinh viên
     * Route: GET /classes/{class}/members/{member}/edit
     */
    public function edit(CourseClass $class, ClassMember $member)
    {
        $this->authorizeClass($class);
        $this->authorizeMember($class, $member);

        return view('classes.members.edit', compact('class', 'member'));
    }

    /**
     * Cập nhật thông tin sinh viên
     * Route: PUT/PATCH /classes/{class}/members/{member}
     */
    public function update(UpdateClassMemberRequest $request, CourseClass $class, ClassMember $member)
    {
        $this->authorizeClass($class);
        $this->authorizeMember($class, $member);

        $member->update($request->validated());

        return redirect()
            ->route('classes.members.index', $class)
            ->with('success', "Cập nhật sinh viên {$member->full_name} thành công!");
    }

    /**
     * Xóa mềm sinh viên khỏi lớp
     * Route: DELETE /classes/{class}/members/{member}
     */
    public function destroy(CourseClass $class, ClassMember $member)
    {
        $this->authorizeClass($class);
        $this->authorizeMember($class, $member);

        $member->delete();

        return redirect()
            ->route('classes.members.index', $class)
            ->with('success', "Đã xóa sinh viên {$member->full_name} khỏi lớp.");
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

    private function authorizeMember(CourseClass $class, ClassMember $member): void
    {
        if ($member->class_id !== $class->id) {
            abort(404);
        }
    }
}
