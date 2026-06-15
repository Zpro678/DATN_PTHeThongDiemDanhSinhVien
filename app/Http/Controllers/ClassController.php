<?php

namespace App\Http\Controllers;

use App\Models\CourseClass;
use App\Http\Requests\StoreClassRequest;
use App\Http\Requests\UpdateClassRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controller quản lý lớp học
 * TV2 — Nguyễn Tuấn Khanh
 * Bảng DB: classes | Model: CourseClass
 */
class ClassController extends Controller
{
    /**
     * Danh sách lớp học của giảng viên
     * Route: GET /classes
     */
    public function index(Request $request)
    {
        $query = CourseClass::query()
            ->where('owner_id', auth()->id())
            ->with(['members' => fn($q) => $q->where('status', 'active')])
            ->latest();

        // Tìm kiếm theo tên hoặc mã lớp
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('subject_code', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $classes = $query->paginate(10)->withQueryString();

        return view('classes.index', compact('classes'));
    }

    /**
     * Form tạo lớp học mới
     * Route: GET /classes/create
     */
    public function create()
    {
        return view('classes.create');
    }

    /**
     * Lưu lớp học mới
     * Route: POST /classes
     */
    public function store(StoreClassRequest $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            // Tự động tìm hoặc tạo Tenant cho user mới đăng ký
            $tenant = \App\Models\Tenant::where('owner_id', $user->id)->first();
            if (!$tenant) {
                $tenant = \App\Models\Tenant::create([
                    'owner_id' => $user->id,
                    'name' => 'Trường của ' . $user->name,
                    'status' => 'active',
                ]);
                \App\Models\TenantSetting::create([
                    'tenant_id' => $tenant->id,
                    'default_gps_radius' => 50,
                    'default_absence_warning' => 20.00,
                ]);
            }
            $user->update(['tenant_id' => $tenant->id]);
            $tenantId = $tenant->id;
        }

        $class = CourseClass::create([
            'tenant_id'          => $tenantId,
            'owner_id'           => $user->id,
            'code'               => strtoupper($request->code),
            'name'               => $request->name,
            'description'        => $request->description,
            'subject_code'       => $request->subject_code,
            'semester'           => $request->semester,
            'require_approval'   => $request->boolean('require_approval'),
            'total_sessions'     => $request->total_sessions,
            'lessons_per_session'=> $request->lessons_per_session,
            'status'             => 'active',
        ]);

        return redirect()
            ->route('classes.show', $class)
            ->with('success', "Tạo lớp học \"{$class->name}\" thành công!");
    }

    /**
     * Chi tiết lớp học
     * Route: GET /classes/{class}
     */
    public function show(CourseClass $class)
    {
        $this->authorizeClass($class);

        $class->load([
            'members' => fn($q) => $q->orderBy('full_name'),
            'sessions' => fn($q) => $q->orderBy('date', 'desc'),
        ]);

        return view('classes.show', compact('class'));
    }

    /**
     * Form sửa lớp học
     * Route: GET /classes/{class}/edit
     */
    public function edit(CourseClass $class)
    {
        $this->authorizeClass($class);

        return view('classes.edit', compact('class'));
    }

    /**
     * Cập nhật thông tin lớp học
     * Route: PUT/PATCH /classes/{class}
     */
    public function update(UpdateClassRequest $request, CourseClass $class)
    {
        $this->authorizeClass($class);

        $class->update($request->validated());

        return redirect()
            ->route('classes.show', $class)
            ->with('success', 'Cập nhật lớp học thành công!');
    }

    /**
     * Xóa mềm lớp học
     * Route: DELETE /classes/{class}
     */
    public function destroy(CourseClass $class)
    {
        $this->authorizeClass($class);

        $class->delete();

        return redirect()
            ->route('classes.index')
            ->with('success', "Đã xóa lớp học \"{$class->name}\".");
    }

    /**
     * Lưu trữ lớp học (chuyển sang archived)
     * Route: PATCH /classes/{class}/archive
     */
    public function archive(CourseClass $class)
    {
        $this->authorizeClass($class);

        $class->update(['status' => 'archived']);

        return redirect()
            ->back()
            ->with('success', "Đã lưu trữ lớp học \"{$class->name}\".");
    }

    /**
     * Tạo lại mã lớp mới (khi mã cũ bị lộ)
     * Route: PATCH /classes/{class}/regenerate-code
     */
    public function regenerateCode(CourseClass $class)
    {
        $this->authorizeClass($class);

        $newCode = strtoupper(Str::random(8));
        $class->update(['code' => $newCode]);

        return redirect()
            ->back()
            ->with('success', "Mã lớp mới: {$newCode}");
    }

    // =====================
    // Private helpers
    // =====================

    /**
     * Kiểm tra giảng viên có quyền với lớp này không
     */
    private function authorizeClass(CourseClass $class): void
    {
        if ($class->owner_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền thao tác với lớp học này.');
        }
    }
}
