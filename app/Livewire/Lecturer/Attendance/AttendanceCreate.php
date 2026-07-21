<?php

namespace App\Livewire\Lecturer\Attendance;

use App\Livewire\Lecturer\Attendance\Concerns\OwnsAttendanceSessions;
use App\Models\AttendanceRecord;
use App\Models\ClassMeeting;
use App\Models\ClassMember;
use App\Models\ClassSession;
use App\Models\CourseClass;
use App\Services\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

/**
 * Class AttendanceCreate
 * 
 * Component Livewire đảm nhận việc tạo buổi điểm danh mới cho giảng viên.
 * Hỗ trợ 2 phương thức điểm danh:
 * 1. Thủ công (Manual Session): Tạo phiên điểm danh và mặc định đánh dấu 'present' (có mặt) cho tất cả sinh viên.
 * 2. Mã QR (QR Session): Tạo buổi học (ClassMeeting) và chuyển hướng giảng viên sang màn hình tạo/quản lý mã QR.
 */
class AttendanceCreate extends Component
{
    use OwnsAttendanceSessions;

    /** @var int|null ID phiên điểm danh khi mở lại phiên sẵn có từ query string (?session_id=...) */
    public ?int $sessionId = null;

    /** @var int|null ID phiên điểm danh khi nhân bản (clone) từ phiên cũ qua query string (?clone_session=...) */
    public ?int $cloneSessionId = null;

    // --- THÔNG TIN CƠ BẢN ---
    
    /** @var string ID lớp học được chọn để điểm danh (wire:model="classId") */
    public string $classId = '';

    /** @var string Tiêu đề/Tên của buổi điểm danh (wire:model="name") */
    public string $name = '';

    /** @var string Ngày diễn ra điểm danh (định dạng Y-m-d), chỉ dùng tham chiếu khi mở lại/clone phiên */
    public string $date = '';

    /** @var string Giờ bắt đầu mặc định ('07:00'), giữ để tham chiếu */
    public string $startTime = '07:00';

    /** @var string Giờ kết thúc mặc định ('09:30'), giữ để tham chiếu */
    public string $endTime = '09:30';

    /** @var string Giờ kết thúc buổi điểm danh do giảng viên chọn (wire:model="meetingEndTime") */
    public string $meetingEndTime = '';

    /**
     * Khởi tạo dữ liệu ban đầu cho Component (Lifecycle hook của Livewire).
     * 
     * - Kiểm tra query string xem có truyền `session_id` hoặc `clone_session` hay không.
     * - Nếu có: Kiểm tra quyền quản lý lớp (hỗ trợ cả Đồng chủ lớp) và load thông tin phiên cũ qua `fillFromSession()`.
     * - Nếu không: Đặt ngày mặc định là hôm nay, giờ kết thúc mặc định là +90 phút (làm tròn bội số 5 phút),
     *   và tự động chọn lớp học dựa trên query parameter `class_id` hoặc chọn lớp đầu tiên khả dụng.
     * 
     * @return void
     */
    public function mount(): void
    {
        $this->sessionId = request()->query('session_id');
        $this->cloneSessionId = request()->query('clone_session');

        if ($this->sessionId) {
            // Quyền xét theo "ai QUẢN LÝ lớp" (ownedSession -> scope managedBy), không theo
            // "ai bấm nút tạo phiên" (created_by), để ĐỒNG CHỦ LỚP mở được phiên do chủ chính
            // tạo. Không tìm thấy / không có quyền -> 404 (không lộ phiên đó tồn tại hay không).
            $session = $this->ownedSession($this->sessionId);
            $this->fillFromSession($session);
        } elseif ($this->cloneSessionId) {
            $session = $this->ownedSession($this->cloneSessionId);
            $this->fillFromSession($session);
        } else {
            // Buổi luôn diễn ra hôm nay, bắt đầu tại thời điểm tạo; chỉ cần nhập giờ kết thúc.
            $this->date = now()->toDateString();
            $defaultEnd = now()->addMinutes(90)->second(0);
            
            // Nếu +90 phút vượt sang ngày hôm sau (tạo buổi tối muộn) thì kẹp về 23:55 hôm nay.
            if (! $defaultEnd->isSameDay(now())) {
                $defaultEnd = now()->copy()->setTime(23, 55, 0);
            }
            $defaultEnd->minute(intdiv($defaultEnd->minute, 5) * 5); // Làm tròn xuống bội số 5 phút cho khớp lưới chọn.
            $this->meetingEndTime = $defaultEnd->format('H:i');

            $preselectedClassId = request()->query('class_id');
            if ($preselectedClassId && $this->availableClasses()->contains('id', $preselectedClassId)) {
                $this->classId = (string) $preselectedClassId;
            } else {
                $firstClass = $this->availableClasses()->first();
                $this->classId = (string) ($firstClass?->id ?? '');
            }
        }
    }

    /**
     * Điền sẵn dữ liệu form từ một phiên có sẵn (mở lại qua session_id, hoặc nhân bản qua clone_session).
     * 
     * @param ClassSession $session Đối tượng phiên điểm danh lấy từ CSDL.
     * @return void
     */
    private function fillFromSession(ClassSession $session): void
    {
        $this->classId = (string) $session->class_id;
        $this->name = $session->name;
        $this->date = $session->date->format('Y-m-d');
        $this->startTime = $session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('H:i') : '07:00';
        $this->endTime = $session->end_time ? \Carbon\Carbon::parse($session->end_time)->format('H:i') : '09:30';
        $this->meetingEndTime = $this->endTime;
    }

    // ========== LƯU THÔNG TIN CƠ BẢN VÀ CHỌN PHƯƠNG THỨC ==========

    /**
     * Xử lý sự kiện khi giảng viên bấm chọn nút "Tạo điểm danh thủ công".
     * 
     * - Gọi `createBaseSession()` để tạo đồng thời ClassMeeting và ClassSession + khởi tạo danh sách sinh viên.
     * - Ghi Audit Log cho hành động tạo phiên thủ công.
     * - Hiển thị thông báo thành công và chuyển hướng về danh sách điểm danh (`lecturer.attendance.index`).
     * 
     * @return void
     */
    public function createManualSession(): void
    {
        $session = $this->createBaseSession();
        if (!$session) {
            return;
        }

        app(AuditLogService::class)->log('session_created', [
            'class_id'   => $session->class_id,
            'table_name' => 'class_sessions',
            'row_id'     => $session->id,
            'new_values' => ['name' => $session->name, 'date' => $session->date, 'type' => 'manual'],
        ]);

        session()->flash('success', 'Bạn đã tạo buổi điểm danh thành công.');
        $this->redirectRoute('lecturer.attendance.index', navigate: true);
    }

    /**
     * Xử lý sự kiện khi giảng viên bấm chọn nút "Tạo điểm danh qua mã QR".
     * 
     * - Gọi `createBaseMeeting()` để tạo bản ghi ClassMeeting.
     * - Ghi Audit Log cho hành động tạo buổi QR.
     * - Hiển thị thông báo thành công và chuyển hướng sang trang tạo mã QR (`lecturer.attendance.qr.create`).
     * 
     * @return void
     */
    public function createQrSession(): void
    {
        $meeting = $this->createBaseMeeting();
        if (!$meeting) {
            return;
        }

        app(AuditLogService::class)->log('session_created', [
            'class_id'   => $meeting->class_id,
            'table_name' => 'class_meetings',
            'row_id'     => $meeting->id,
            'new_values' => ['name' => $meeting->name, 'date' => $meeting->date, 'type' => 'qr'],
        ]);

        session()->flash('success', 'Bạn đã tạo buổi điểm danh thành công.');
        $this->redirectRoute('lecturer.attendance.qr.create', ['ma_user' => auth()->id(), 'meeting' => $meeting->id], navigate: true);
    }

    /**
     * Hàm dùng chung tạo bản ghi Buổi học cơ sở (`ClassMeeting`).
     * 
     * Thực hiện các bước:
     * 1. Validate dữ liệu nhập vào (lớp học, tiêu đề, giờ kết thúc).
     * 2. Kiểm tra giờ kết thúc phải sau thời điểm hiện tại ít nhất 10 phút.
     * 3. Kiểm tra danh sách thành viên trong lớp: nếu lớp chưa có sinh viên active, 
     *    thêm lỗi và chuyển hướng giảng viên tới trang quản lý lớp để import sinh viên.
     * 4. Tạo và trả về bản ghi `ClassMeeting` mới.
     * 
     * @return ClassMeeting|null Trả về đối tượng ClassMeeting vừa tạo, hoặc null nếu validate/kiểm tra thất bại.
     */
    private function createBaseMeeting(): ?ClassMeeting
    {
        $validated = $this->validate([
            'classId' => ['required', 'string', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'meetingEndTime' => ['required', 'date_format:H:i'],
        ], [
            'classId.required' => 'Vui lòng chọn lớp học.',
            'classId.exists' => 'Lớp học không hợp lệ.',
            'name.required' => 'Vui lòng nhập tiêu đề buổi học.',
            'meetingEndTime.required' => 'Vui lòng nhập giờ kết thúc buổi điểm danh.',
            'meetingEndTime.date_format' => 'Giờ kết thúc không hợp lệ.',
        ]);

        // Ngày = hôm nay, giờ bắt đầu = lúc tạo; chỉ nhận giờ kết thúc và phải cách hiện tại >= 10 phút.
        $date = now()->toDateString();
        $startTime = now()->format('H:i');
        $endsAt = \Carbon\Carbon::parse($date.' '.$validated['meetingEndTime'].':00');
        if ($endsAt->lessThanOrEqualTo(now())) {
            $endsAt->addDay();
        }

        if ($endsAt->lessThanOrEqualTo(now()->addMinutes(10))) {
            $this->addError('meetingEndTime', 'Giờ kết thúc phải sau thời điểm hiện tại ít nhất 10 phút.');
            return null;
        }

        $courseClass = $this->ownedClass($validated['classId']);

        if ($courseClass->members()->where('status', ClassMember::STATUS_ACTIVE)->count() === 0) {
            $message = 'Vui lòng import danh sách lớp trước khi điểm danh.';
            $this->addError('classId', $message);
            
            session()->flash('error', $message);
            $this->redirectRoute('lecturer.classes.show', ['ma_user' => auth()->id(), 'courseClass' => $courseClass->id, 'openImport' => 1], navigate: true);
            return null;
        }

        // Mỗi lần "Tạo buổi điểm danh" tạo một BUỔI mới.
        return ClassMeeting::query()->create([
            'class_id' => $courseClass->id,
            'user_Created' => auth()->id(),
            'name' => $validated['name'],
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $validated['meetingEndTime'],
            'status' => 'active',
        ]);
    }

    /**
     * Tạo bản ghi Phiên điểm danh cơ sở (`ClassSession`) và khởi tạo bảng điểm danh cho sinh viên.
     * 
     * - Trước tiên gọi `createBaseMeeting()` để tạo buổi học.
     * - Tạo bản ghi `ClassSession` liên kết với `ClassMeeting` vừa tạo.
     * - Mặc định cho điểm danh THỦ CÔNG: Tạo các bản ghi `AttendanceRecord` cho toàn bộ sinh viên đang 
     *   active trong lớp với trạng thái mặc định là 'present' (có mặt).
     * 
     * @return ClassSession|null Trả về phiên điểm danh ClassSession vừa tạo, hoặc null nếu thất bại.
     */
    private function createBaseSession(): ?ClassSession
    {
        $meeting = $this->createBaseMeeting();
        if (!$meeting) return null;

        $session = ClassSession::query()->create([
            'class_id' => $meeting->class_id,
            'meeting_id' => $meeting->id,
            'created_by' => $meeting->user_Created,
            'name' => $meeting->name,
            'date' => $meeting->date,
            'start_time' => $meeting->start_time,
            'end_time' => $meeting->end_time,
            'status' => 'active',
        ]);

        // Phiên thủ công ĐẦU TIÊN của buổi: mặc định mọi SV active 'present' (điểm danh thủ
        // công hiểu là "có mặt trừ khi GV đánh dấu khác"). Khác với phiên tạo qua
        // ClassMeeting::createSession() (QR, hoặc thêm phiên sau từ MeetingSessions/QrAttendanceCreate)
        // vốn mặc định 'pending'/'absent' — xem docs/LUONG_XU_LY_DIEM_DANH.md mục 3.
        $meeting->courseClass->members()->where('status', ClassMember::STATUS_ACTIVE)->get()->each(fn ($member) => AttendanceRecord::query()->firstOrCreate([
            'class_session_id' => $session->id,
            'class_member_id' => $member->id,
        ], [
            'status' => 'present',
            'is_account' => $member->user_id !== null,
        ]));

        return $session;
    }

    // ========== RENDERING ==========

    /**
     * Render giao diện của component Livewire (`create.blade.php`).
     * 
     * - Lấy danh sách các lớp học khả dụng của giảng viên kèm số lượng sinh viên đang active.
     * - Xác định lớp học hiện đang được chọn.
     * - Trả về view với layout `layouts.user` và tiêu đề "Tạo buổi điểm danh".
     * 
     * @return View Giao diện Blade đã render.
     */
    public function render(): View
    {
        $classes = $this->availableClasses()
            ->loadCount(['members' => fn ($query) => $query->where('status', ClassMember::STATUS_ACTIVE)]);

        $selectedClass = $classes->firstWhere('id', $this->classId) ?? $classes->first();

        return view('livewire.lecturer.attendance.create', compact('classes', 'selectedClass'))
            ->layout('layouts.user', ['title' => 'Tạo buổi điểm danh']);
    }

    /**
     * Lấy danh sách tất cả các lớp học thuộc quyền quản lý của giảng viên hiện tại.
     * 
     * - Nếu giảng viên chưa có lớp học nào trong hệ thống, hàm sẽ tự động gọi `createDemoClassForCurrentUser()` 
     *   để tạo một lớp demo kèm danh sách sinh viên giả lập, phục vụ việc trải nghiệm/kiểm thử.
     * 
     * @return Collection Danh sách các lớp học (CourseClass).
     */
    private function availableClasses(): Collection
    {
        $classes = $this->ownedClasses();

        if ($classes->isNotEmpty()) {
            return $classes;
        }

        $this->createDemoClassForCurrentUser();

        return $this->ownedClasses();
    }

    /**
     * Tạo một lớp học giả lập (Demo Class) kèm theo 4 sinh viên mẫu cho giảng viên hiện tại.
     * 
     * Dùng khi giảng viên mới tạo tài khoản chưa có lớp thật nào, giúp tránh lỗi giao diện 
     * và cho phép dùng thử chức năng tạo buổi điểm danh ngay lập tức.
     * 
     * @return void
     */
    private function createDemoClassForCurrentUser(): void
    {
        $userId = auth()->id();
        $code = 'DEMO-'.$userId.'-ATT';

        $courseClass = CourseClass::withTrashed()->firstOrCreate(
            ['join_key' => $code],
            [
                'owner_user_id' => $userId,
                'name' => 'Lớp demo điểm danh',
                'description' => 'Dữ liệu giả để kiểm thử trang tạo điểm danh.',
                'require_approval' => false,
                'status' => 'active',
                'total_sessions' => 15,
            ],
        );

        $courseClass->restore();
        $courseClass->update([
            'owner_user_id' => $userId,
            'status' => 'active',
        ]);

        collect([
            'Nguyễn Văn An',
            'Trần Thị Bình',
            'Lê Minh Cường',
            'Phạm Thanh Duy',
        ])->each(function (string $fullName) use ($courseClass): void {
            $member = ClassMember::withTrashed()
                ->where('class_id', $courseClass->id)
                ->whereHas('profile', fn ($p) => $p->where('full_name', $fullName))
                ->first();

            if (! $member) {
                $member = ClassMember::create([
                    'class_id' => $courseClass->id,
                    'user_id' => null,
                    'status' => ClassMember::STATUS_ACTIVE,
                ]);
            } else {
                $member->restore();
                $member->update(['status' => ClassMember::STATUS_ACTIVE]);
            }

            $member->syncProfile(['full_name' => $fullName]);
        });
    }
}

