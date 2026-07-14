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

        $batch = \Illuminate\Support\Facades\Bus::findBatch($this->importToken);
        if ($batch) {
            $this->importTotalRows = $batch->totalJobs;
            
            if ($batch->processedJobs() === $this->importProcessedRows) {
                $this->importQuietTicks++;
            } else {
                $this->importProcessedRows = $batch->processedJobs();
                $this->importQuietTicks = 0;
            }

            if ($batch->finished()) {
                $this->finalizeImport();
                return;
            }
        }
    }

    protected function finalizeImport(): void
    {
        // Số SV nhập thành công (các chunk job đếm dồn vào cache theo token).
        $this->importSuccess = (int) cache()->pull('import_success_' . $this->importToken, 0);

        // Lấy tất cả lỗi nếu có
        $dbErrors = \App\Models\ImportError::where('import_token', $this->importToken)->orderBy('row_index')->get();
        if ($dbErrors->count() > 0) {
            $this->importErrors = $dbErrors->map(function ($error) {
                return ($error->row_index ? "Dòng {$error->row_index}: " : "") . $error->error_message;
            })->toArray();
        }

        // Số SV bị bỏ qua do vượt giới hạn SV/lớp của gói (chunk job đếm vào cache theo token).
        $skipped   = (int) cache()->pull('import_skipped_' . $this->importToken, 0);
        $skipLimit = (int) cache()->pull('import_skipped_limit_' . $this->importToken, 0);
        if ($skipped > 0) {
            array_unshift(
                $this->importErrors,
                "{$skipped} sinh viên KHÔNG được thêm vào lớp vì đã đạt giới hạn {$skipLimit} sinh viên/lớp của gói hiện tại. Vui lòng nâng cấp gói để thêm nhiều hơn."
            );
            $importedClass = \App\Models\CourseClass::find($this->importClassId);
            if ($importedClass) {
                app(\App\Services\NotificationService::class)
                    ->notifyImportStudentLimitReached((int) auth()->id(), $importedClass, $skipped, $skipLimit);
            }
        }

        $message = "Đã nhập {$this->importSuccess} học viên thành công." . ($dbErrors->count() > 0 ? " Tuy nhiên có một số dòng bị lỗi." : "");
        if ($dbErrors->count() === 0 && $skipped === 0) {
            $this->closeImport();
            $this->isImportingStatus = false;
            $this->dispatch('toast', message: $message, type: 'success');
        } else {
            // Có lỗi hoặc bị cắt bớt do giới hạn: giữ modal mở để chủ lớp đọc chi tiết.
            $this->isImporting = true;
            $this->isImportingStatus = false;
            $message = $skipped > 0
                ? "Đã nhập {$this->importSuccess} học viên; {$skipped} SV bị bỏ qua do vượt giới hạn của gói."
                : "Đã nhập {$this->importSuccess} học viên, nhưng có một số dòng bị lỗi. Vui lòng xem chi tiết.";
            $this->dispatch('toast', message: $message, type: 'warning');
        }

        session()->flash('success', $message);
        $this->reset(['importToken', 'importTotalRows', 'importProcessedRows', 'importQuietTicks']);
    }

    public function mount(): void
    {
        if (empty($this->classFilter) || $this->classFilter === 'all') {
            $latestClass = CourseClass::managedBy(auth()->id())->latest()->first();
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
            $q->managedBy(auth()->id());
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
            $q->managedBy(auth()->id());
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
        $courseClass = CourseClass::managedBy(auth()->id())->findOrFail($validated['newClassId']);

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
        // Chỉ 1 sheet: import chỉ đọc sheet đầu tiên, nên template cũng phải đơn sheet
        // để tránh lệch cột giữa các sheet.
        return Excel::download(new \App\Exports\ImportTemplateFullSheet(), 'Danh_sach_sinh_vien_mau_day_du.xlsx');
    }

    public function downloadBasicTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateBasicSheet(), 'Danh_sach_sinh_vien_mau_co_ban.xlsx');
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

        $courseClass = CourseClass::managedBy(auth()->id())->findOrFail($this->importClassId);

        $this->isImportingStatus = true;
        $this->importTotalRows = 0;
        $this->importProcessedRows = 0;
        $this->importQuietTicks = 0;

        $extension = $this->importFile->getClientOriginalExtension();
        $readerType = match (strtolower($extension)) {
            'csv' => \Maatwebsite\Excel\Excel::CSV,
            'xls' => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        // Khoá: mỗi tài khoản chỉ 1 lượt import chạy tại một thời điểm.
        $importUserId = (int) auth()->id();
        if (! \App\Support\ImportLock::acquire($importUserId)) {
            $this->isImportingStatus = false;
            $this->addError('importFile', 'Bạn đang có một lượt import đang chạy. Vui lòng đợi hoàn tất rồi thử lại.');
            return;
        }

        try {
            // Lưu file vào ổ đĩa cố định trước khi đẩy vào hàng đợi (worker async chạy sau,
            // file tạm Livewire sẽ không còn) — xem thêm CreateClass::save.
            $storedRelativePath = $this->importFile->storeAs(
                'imports',
                \Illuminate\Support\Str::uuid()->toString().'.'.strtolower($extension),
                'local'
            );
            $importAbsolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedRelativePath);

            // Bước 1: Phân tích header trước (đọc 10 dòng đầu)
            $headingImport = new \App\Imports\HeadingRowImport($courseClass->id);
            Excel::import($headingImport, $importAbsolutePath, null, $readerType);

            if (!empty($headingImport->errors)) {
                $this->importErrors = $headingImport->errors;
                $this->isImportingStatus = false;
                \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
                \App\Support\ImportLock::release($importUserId);
                return;
            }

            $meetingHeaders = array_unique($headingImport->meetingHeaders);

            // Bước 2: Tạo Bus::batch và StartImportJob
            $batch = \Illuminate\Support\Facades\Bus::batch([
                new \App\Jobs\StartImportJob(
                    $importAbsolutePath,
                    $courseClass->id,
                    $headingImport->dateHeaders,
                    $headingImport->meetingHeaders,
                    $headingImport->emailColIndex,
                    $headingImport->nameColIndex,
                    $headingImport->headerRowNumber,
                    auth()->id(),
                    $readerType,
                    null // Sẽ được cập nhật sau
                )
            ])
            ->then(function (\Illuminate\Bus\Batch $batch) use ($meetingHeaders) {
                // Đồng bộ chuyên cần khi batch hoàn thành
                foreach ($meetingHeaders as $meetingId) {
                    $meeting = \App\Models\ClassMeeting::with(['courseClass', 'sessions'])->find($meetingId);
                    if ($meeting) {
                        \App\Services\AttendanceCalculator::syncSummaries($meeting);
                    }
                }
            })
            ->finally(function () use ($storedRelativePath, $importUserId) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
                \App\Support\ImportLock::release($importUserId);
            })
            ->name('Import Students')
            ->dispatch();

            $this->importToken = $batch->id;
        } catch (\Exception $e) {
            if (isset($storedRelativePath)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($storedRelativePath);
            }
            \App\Support\ImportLock::release($importUserId);
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
        event(new \App\Events\ClassDataUpdated((string) $member->class_id));
        $this->dispatch('toast', message: 'Sinh viên đã được chuyển vào lưu trữ.', type: 'success');
    }

    public function restoreMember(int $memberId): void
    {
        $member = $this->ownedMember($memberId, true);
        $member->restore();
        $member->update(['status' => ClassMember::STATUS_ACTIVE, 'status_changed_at' => null]);

        event(new \App\Events\ClassDataUpdated((string) $member->class_id));
        $this->dispatch('toast', message: 'Sinh viên đã được khôi phục vào lớp.', type: 'success');
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

        try {
            return Excel::download(
                new StudentsExport(auth()->id(), $this->exportClassId, $this->statusFilter, $this->search, $formulaToUse),
                $fileName
            );
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Đã xảy ra lỗi trong quá trình tạo file. Vui lòng thử lại sau hoặc liên hệ Admin.', type: 'error');
            return null;
        }
    }

    /** @return array<int, string> Kênh realtime "class.{id}" của mọi lớp giảng viên đang quản lý. */
    public function realtimeChannels(): array
    {
        $prefix = (string) config('database.redis.options.prefix');

        return CourseClass::query()
            ->managedBy(auth()->id())
            ->pluck('id')
            ->map(fn ($id) => $prefix . 'class.' . $id)
            ->all();
    }

    private function ownedMember(int $memberId, bool $withTrashed = false): ClassMember
    {
        $query = ClassMember::query()
            ->when($withTrashed, fn (Builder $query) => $query->withTrashed())
            ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()));

        return $query->findOrFail($memberId);
    }

    public function render(): View
    {
        $studentService = app(LectureManageStudentService::class); // Gọi service xử lý thống kê chuyên cần sinh viên.

        $classes = CourseClass::query()
            ->managedBy(auth()->id()) // Chỉ lấy các lớp mà giảng viên hiện tại được quản lý (chủ chính hoặc đồng chủ).
            ->orderBy('name') // Sắp xếp lớp theo tên để dropdown dễ nhìn.
            ->get(['id', 'name', 'join_key', 'class_code']); // Chỉ lấy cột cần dùng cho bộ lọc lớp.

        if ($this->statusFilter === 'pending') {
            $members = ClassJoinRequest::query()
                ->with(['courseClass:id,name,join_key,class_code', 'user:id,name,email,avatar'])
                ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()))
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
                ->whereHas('courseClass', fn (Builder $query) => $query->managedBy(auth()->id()))
                ->when(
                    $this->statusFilter === 'archived',
                    fn (Builder $query) => $query->onlyTrashed(),
                    fn (Builder $query) => $query->where('class_members.status', ClassMember::STATUS_ACTIVE),
                )
                ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_members.class_id', $this->classFilter))
                ->when($this->search !== '', function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query->where('class_member_profiles.full_name', 'like', '%'.$this->search.'%')
                            ->orWhere('class_member_profiles.email', 'like', '%'.$this->search.'%')
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
