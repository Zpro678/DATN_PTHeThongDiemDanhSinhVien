<?php

namespace App\Livewire\Lecturer\Students;

use App\Imports\StudentsImport;
use App\Models\ClassMember;
use App\Models\CourseClass;
use App\Services\LectureManageStudentService;
use App\Services\SubscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Exports\StudentsExport;
use App\Models\ClassJoinRequest;
use Maatwebsite\Excel\Facades\Excel;

class StudentIndex extends Component
{
    use WithFileUploads, WithPagination;

    // Từ khóa tìm kiếm sinh viên
    public string $search = '';

    // Bộ lọc theo ID lớp học
    #[Url(as: 'class_id')]
    public string $classFilter = '';

    // Tham số hành động từ URL (ví dụ: 'import' để tự động mở modal)
    #[Url(as: 'action')]
    public string $action = '';

    // Bộ lọc trạng thái học viên (đang học hoặc đã lưu trữ)
    #[Url(as: 'status')]
    public string $statusFilter = 'active';

    // ID của thành viên lớp đang được chỉnh sửa
    public ?int $editingMemberId = null;

    // Họ tên của sinh viên đang được chỉnh sửa
    public string $editingName = '';

    // Email của sinh viên đang được chỉnh sửa
    public string $editingEmail = '';

    // Trạng thái của sinh viên đang được chỉnh sửa
    public string $editingStatus = 'active';

    // Thao tác với Modal Export
    public bool $isExporting = false;
    public string $exportClassId = 'all';
    public string $exportFormula = '(c + m + p) / t * 100';
    public string $selectedTemplate = '(c + m + p) / t * 100';
    public bool $isCustomFormula = false;

    // Biến cho các thao tác mảng
    public bool $isConfirmingArchive = false;

    // Trạng thái hiển thị form thêm sinh viên thủ công
    public bool $isAdding = false;

    // Họ tên sinh viên khi thêm mới
    public string $newName = '';

    // Email sinh viên khi thêm mới
    public string $newEmail = '';

    // ID lớp học mà sinh viên sẽ được thêm vào
    public string $newClassId = '';

    // ID sinh viên đang được chọn để lưu trữ (chờ xác nhận)
    public ?int $archivingMemberId = null;

    // Trạng thái hiển thị modal import Excel/CSV
    public bool $isImporting = false;

    // ID lớp học mà danh sách sinh viên sẽ được import vào
    public string $importClassId = '';

    // Đối tượng file Excel hoặc CSV được upload lên
    public $importFile;

    // Mảng lưu trữ các lỗi xuất hiện trong quá trình import
    public array $importErrors = [];

    // Số lượng sinh viên đã được import thành công
    public int $importSuccess = 0;

    public bool $showBackButton = false;

    // Các thuộc tính phục vụ theo dõi tiến trình import dạng chunk qua Cache/Polling
    public ?string $importToken = null;
    public bool $isImportingStatus = false;
    public int $importTotalRows = 0;
    public int $importProcessedRows = 0;
    public int $importQuietTicks = 0;

    public function checkImportProgress(): void
    {
        if (!$this->importToken) {
            return;
        }

        $progress = \Illuminate\Support\Facades\Cache::get("import_progress_{$this->importToken}");
        if ($progress) {
            $this->importTotalRows = $progress['total_rows'];
            
            if ($progress['processed_rows'] === $this->importProcessedRows) {
                $this->importQuietTicks++;
            } else {
                $this->importProcessedRows = $progress['processed_rows'];
                $this->importQuietTicks = 0;
            }

            if ($progress['status'] === 'completed') {
                $this->finalizeImport();
                return;
            }

            // Nếu sau 3 giây (6 lần poll 500ms) không thấy tiến trình chạy (do Queue Worker không chạy)
            if ($this->importQuietTicks >= 6) {
                // Tự động chuyển sang xử lý đồng bộ để tránh bị treo
                $this->finalizeImport();
            }
        }
    }

    protected function finalizeImport(): void
    {
        $count = $this->importSuccess;
        $this->closeImport();
        $message = "Đã nhập thành công {$count} sinh viên vào lớp.";
        session()->flash('success', $message);
        $this->dispatch('toast', message: $message, type: 'success');
        $this->reset(['importToken', 'isImportingStatus', 'importTotalRows', 'importProcessedRows', 'importQuietTicks']);
    }

    public function mount(): void
    {
        if (empty($this->classFilter) || $this->classFilter === 'all') {
            $latestClass = CourseClass::where('owner_user_id', auth()->id())->latest()->first();
            if ($latestClass) {
                $this->classFilter = (string) $latestClass->id;
            }
        }

        $this->showBackButton = request()->has('class_id');
        
        if ($this->action === 'import') {
            $this->openImport();
        } elseif ($this->action === 'export') {
            $this->openExport();
        }
    }

    public function updatedSearch(): void
    {
        // Khi search không cần resetPage nữa vì không còn dùng phân trang
    }

    public function updatedClassFilter(): void
    {
        // Khi đổi class filter không cần resetPage
    }

    public function updatedSelectedTemplate($value): void
    {
        if (!empty($value)) {
            $this->exportFormula = $value;
        }
    }

    public function setStatusFilter(string $status): void
    {
        if (in_array($status, ['active', 'archived', 'pending'], true)) {
            $this->statusFilter = $status;
        }
    }

    public function approveRequest(int $requestId): void
    {
        $request = ClassJoinRequest::whereHas('courseClass', function ($q) {
            $q->where('owner_user_id', auth()->id());
        })->findOrFail($requestId);

        // Yêu cầu vào lớp gắn với tài khoản; tìm thành viên theo user_id.
        $member = ClassMember::where('class_id', $request->class_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($member) {
            $member->update(['status' => ClassMember::STATUS_ACTIVE, 'status_changed_at' => now()]);
        } else {
            $maxStudents = app(SubscriptionService::class)->maxStudentsPerClass(auth()->user());
            $currentCount = ClassMember::where('class_id', $request->class_id)
                ->where('status', ClassMember::STATUS_ACTIVE)
                ->count();

            if ($currentCount >= $maxStudents) {
                $this->dispatch('toast', message: "Lớp đã đạt giới hạn {$maxStudents} sinh viên. Không thể duyệt thêm.", type: 'error');
                return;
            }

            $member = ClassMember::create([
                'class_id' => $request->class_id,
                'user_id' => $request->user_id,
                'status' => ClassMember::STATUS_ACTIVE,
            ]);

            // Khởi tạo hồ sơ danh tính từ tài khoản (chưa có MSSV khai báo).
            $member->syncProfile([
                'full_name' => $request->user?->name,
                'email' => $request->user?->email,
            ]);
        }

        $request->update(['status' => ClassJoinRequest::STATUS_APPROVED]);
        $this->dispatch('toast', message: 'Đã duyệt yêu cầu tham gia lớp của sinh viên.', type: 'success');
    }

    public function rejectRequest(int $requestId): void
    {
        $request = ClassJoinRequest::whereHas('courseClass', function ($q) {
            $q->where('owner_user_id', auth()->id());
        })->findOrFail($requestId);

        $request->update(['status' => ClassJoinRequest::STATUS_REJECTED]);
        $this->dispatch('toast', message: 'Đã từ chối yêu cầu tham gia lớp của sinh viên.', type: 'success');
    }

    public function openEdit(int $memberId): void
    {
        $member = $this->ownedMember($memberId);

        $this->editingMemberId = $member->id;
        $this->editingName = (string) $member->full_name;
        $this->editingEmail = $member->email ?? '';
        $this->editingStatus = $member->status === ClassMember::STATUS_ACTIVE ? 'active' : 'dropped';
    }

    public function closeEdit(): void
    {
        $this->reset(['editingMemberId', 'editingName', 'editingEmail']);
        $this->editingStatus = 'active';
        $this->resetValidation();
    }

    public function openAdd(): void
    {
        $this->isAdding = true;
        $this->newClassId = $this->classFilter;
        $this->newEmail = '';
    }

    public function closeAdd(): void
    {
        $this->isAdding = false;
        $this->reset(['newName', 'newClassId', 'newEmail']);
        $this->resetValidation();
    }

    public function addMember(): void
    {
        $validated = $this->validate([
            'newClassId' => ['required', 'exists:classes,id'],
            'newName' => ['required', 'string', 'max:255'],
            'newEmail' => ['nullable', 'email', 'max:255'],
        ], [
            'newClassId.required' => 'Vui lòng chọn lớp học.',
            'newName.required' => 'Vui lòng nhập họ tên.',
            'newEmail.email' => 'Email không đúng định dạng.',
        ]);

        // Ensure the class belongs to the lecturer
        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($validated['newClassId']);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $courseClass->id)
            ->whereHas('profile', fn (Builder $q) => $q->where('email', strtolower($validated['newEmail'] ?? '')))
            ->exists();

        if ($validated['newEmail'] && $duplicateExists) {
            $this->addError('newEmail', 'Email sinh viên đã tồn tại trong lớp này.');

            return;
        }

        // Kiểm tra gói: giới hạn số sinh viên mỗi lớp.
        $maxStudents = app(SubscriptionService::class)->maxStudentsPerClass(auth()->user());
        $currentCount = ClassMember::query()
            ->where('class_id', $courseClass->id)
            ->where('status', ClassMember::STATUS_ACTIVE)
            ->count();

        if ($currentCount >= $maxStudents) {
            $this->addError('newName', "Lớp đã đạt giới hạn {$maxStudents} sinh viên của gói hiện tại. Vui lòng nâng cấp gói để thêm sinh viên.");

            return;
        }

        $user = null;
        if ($validated['newEmail']) {
            $user = \App\Models\User::where('email', $validated['newEmail'])->first();
        }

        $member = ClassMember::create([
            'class_id' => $courseClass->id,
            'user_id' => $user ? $user->id : null,
            'status' => ClassMember::STATUS_ACTIVE,
        ]);

        $member->syncProfile([
            'full_name' => $validated['newName'],
            'email' => $validated['newEmail'] ?: null,
        ]);

        $this->closeAdd();
        session()->flash('success', 'Sinh viên đã được thêm vào lớp thành công.');
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->importClassId = $this->classFilter;
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->reset(['importFile', 'importErrors', 'importSuccess', 'importClassId']);
    }

    public function downloadFullTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateExport(), 'Danh_sach_sinh_vien_mau_day_du.xlsx');
    }

    public function downloadBasicTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateExport(), 'Danh_sach_sinh_vien_mau_co_ban.xlsx');
    }

    public function processImport(): void
    {
        $this->validate([
            'importClassId' => ['required', 'exists:classes,id'],
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importClassId.required' => 'Vui lòng chọn lớp học.',
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.extensions' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($this->importClassId);

        $this->importToken = \Illuminate\Support\Str::uuid()->toString();
        $this->isImportingStatus = true;
        $this->importTotalRows = 0;
        $this->importProcessedRows = 0;
        $this->importQuietTicks = 0;

        $import = new StudentsImport($courseClass->id, $this->importToken);

        $extension = $this->importFile->getClientOriginalExtension();
        $readerType = match (strtolower($extension)) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        try {
            Excel::import($import, $this->importFile->getRealPath(), null, $readerType);

            $this->importSuccess = $import->successCount;
            $this->importErrors = $import->errors;

            if (empty($this->importErrors)) {
                // Do not close import yet. We will poll progress.
            } else {
                $this->isImportingStatus = false;
                $this->importToken = null;
            }
        } catch (\Exception $e) {
            $this->isImportingStatus = false;
            $this->importToken = null;
            $this->addError('importFile', 'Có lỗi khi đọc file: '.$e->getMessage());
        }
    }

    public function saveMember(): void
    {
        $member = $this->ownedMember((int) $this->editingMemberId);

        $validated = $this->validate([
            'editingName' => ['required', 'string', 'max:255'],
            'editingEmail' => ['nullable', 'email', 'max:255'],
            'editingStatus' => ['required', 'in:active,dropped'],
        ], [
            'editingEmail.email' => 'Email không đúng định dạng.',
        ]);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $member->class_id)
            ->whereHas('profile', fn (Builder $q) => $q->where('email', strtolower($validated['editingEmail'] ?? '')))
            ->whereKeyNot($member->id)
            ->exists();

        if ($validated['editingEmail'] && $duplicateExists) {
            $this->addError('editingEmail', 'Email sinh viên đã tồn tại trong lớp này.');

            return;
        }

        $user = null;
        if ($validated['editingEmail']) {
            $user = \App\Models\User::where('email', $validated['editingEmail'])->first();
        }

        $newStatus = $validated['editingStatus'] === 'dropped'
            ? ClassMember::STATUS_REMOVED
            : ClassMember::STATUS_ACTIVE;

        $member->update([
            'user_id' => $user ? $user->id : ($member->email !== $validated['editingEmail'] ? null : $member->user_id),
            'status' => $newStatus,
            'status_changed_at' => $newStatus !== ClassMember::STATUS_ACTIVE ? now() : null,
        ]);

        $member->syncProfile([
            'full_name' => $validated['editingName'],
            'email' => $validated['editingEmail'] ?: null,
        ]);

        // Nếu chuyển sang trạng thái "thôi học", tự động đưa vào mục lưu trữ
        if ($validated['editingStatus'] === 'dropped') {
            $member->delete();
        }

        $this->closeEdit();
        session()->flash('success', 'Thông tin sinh viên đã được cập nhật.');
    }

    public function confirmArchive(int $memberId): void
    {
        $this->archivingMemberId = $memberId;
    }

    public function closeArchiveConfirm(): void
    {
        $this->archivingMemberId = null;
    }

    public function archiveMember(?int $memberId = null): void
    {
        if ($memberId !== null) {
            $this->archivingMemberId = $memberId;
        }

        if (! $this->archivingMemberId) {
            return;
        }

        $member = $this->ownedMember($this->archivingMemberId);
        $member->update(['status' => ClassMember::STATUS_REMOVED, 'status_changed_at' => now()]);
        $member->delete();

        $this->closeArchiveConfirm();
        session()->flash('success', 'Sinh viên đã được chuyển vào lưu trữ.');
    }

    public function restoreMember(int $memberId): void
    {
        $member = $this->ownedMember($memberId, true);
        $member->restore();
        $member->update(['status' => ClassMember::STATUS_ACTIVE, 'status_changed_at' => null]);

        session()->flash('success', 'Sinh viên đã được khôi phục vào lớp.');
    }

    public function openExport()
    {
        // Chỉ mở bảng xuất khi gói bật tính năng xuất Excel; FREE bị chặn ngay từ đây.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');
            $this->redirect(route('upgrade'), navigate: true);

            return;
        }

        $this->exportClassId = $this->classFilter;
        $this->exportFormula = '(c + m + p) / t * 100';
        $this->selectedTemplate = '(c + m + p) / t * 100';
        $this->isExporting = true;
    }

    public function closeExport()
    {
        $this->isExporting = false;
    }

    public function exportExcel()
    {
        // Kiểm tra gói: xuất Excel là tính năng từ gói Pro trở lên.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            $this->isExporting = false;
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirectRoute('upgrade', navigate: true);
        }

        $formulaToUse = $this->selectedTemplate;

        $fileName = 'danh_sach_sinh_vien_' . date('Ymd_His') . '.xlsx';
        $this->isExporting = false;

        return Excel::download(
            new StudentsExport(auth()->id(), $this->exportClassId, $this->statusFilter, $this->search, $formulaToUse),
            $fileName
        );
    }

    private function ownedMember(int $memberId, bool $withTrashed = false): ClassMember
    {
        $query = ClassMember::query()
            ->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()));

        return $query->findOrFail($memberId);
    }

    public function render(): View
    {
        $studentService = app(LectureManageStudentService::class); // Gọi service xử lý thống kê chuyên cần sinh viên.

        $classes = CourseClass::query()
            ->where('owner_user_id', auth()->id()) // Chỉ lấy các lớp do giảng viên hiện tại quản lý.
            ->orderBy('name') // Sắp xếp lớp theo tên để dropdown dễ nhìn.
            ->get(['id', 'name', 'join_key', 'class_code']); // Chỉ lấy cột cần dùng cho bộ lọc lớp.

        if ($this->statusFilter === 'pending') {
            $members = ClassJoinRequest::query()
                ->with(['courseClass:id,name,join_key,class_code', 'user:id,name,email,avatar'])
                ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
                ->where('status', ClassJoinRequest::STATUS_PENDING)
                ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_id', $this->classFilter))
                ->when($this->search !== '', function (Builder $query): void {
                    $query->whereHas('user', function (Builder $q): void {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                })
                ->orderByDesc('created_at')
                ->get();
        } else {
            $members = ClassMember::query()
                ->with(['courseClass:id,name,join_key,class_code', 'user:id,name,email,avatar', 'profile', 'attendanceSummary'])
                ->leftJoin('class_member_profiles', 'class_member_profiles.class_member_id', '=', 'class_members.id')
                ->select('class_members.*')
                ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
                ->when(
                    $this->statusFilter === 'archived',
                    fn (Builder $query) => $query->onlyTrashed(),
                    fn (Builder $query) => $query->where('class_members.status', ClassMember::STATUS_ACTIVE),
                )
                ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_members.class_id', $this->classFilter))
                ->when($this->search !== '', function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query->where('class_member_profiles.full_name', 'like', '%'.$this->search.'%')
                            ->orWhere('class_member_profiles.student_code', 'like', '%'.$this->search.'%')
                            ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', '%'.$this->search.'%')->orWhere('name', 'like', '%'.$this->search.'%'));
                    });
                })
                ->orderBy('class_member_profiles.full_name')
                ->get();
        }

        $attendanceStats = [];
        if ($this->statusFilter !== 'pending') {
            $attendanceStats = $studentService->getStudentsAttendanceStats( // Tính chuyên cần cho từng sinh viên đang hiển thị trên trang hiện tại.
                $members->pluck('id') // Lấy id sinh viên
            );
        }

        $attendanceOverview = $studentService->getTotalAttendanceStats( // Tính tổng chuyên cần theo toàn bộ bộ lọc hiện tại.
            auth()->id(), // Giới hạn dữ liệu theo giảng viên đang đăng nhập.
            $this->classFilter !== 'all' ? $this->classFilter : null // Nếu chọn một lớp thì chỉ thống kê lớp đó.
        );

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        return view('livewire.lecturer.students.index', compact('classes', 'members', 'attendanceStats', 'attendanceOverview', 'canExportExcel')) // Truyền dữ liệu lớp, sinh viên và chuyên cần sang Blade.
            ->layout('layouts.user', ['title' => 'Quản lý sinh viên']);
    }
}
