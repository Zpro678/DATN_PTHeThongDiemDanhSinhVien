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
use Maatwebsite\Excel\Facades\Excel;

class StudentIndex extends Component
{
    use WithFileUploads, WithPagination;

    // Từ khóa tìm kiếm sinh viên
    public string $search = '';

    // Bộ lọc theo ID lớp học ('all' là tất cả các lớp)
    #[Url(as: 'class_id')]
    public string $classFilter = 'all';

    // Tham số hành động từ URL (ví dụ: 'import' để tự động mở modal)
    #[Url(as: 'action')]
    public string $action = '';

    // Bộ lọc trạng thái sinh viên (đang học hoặc đã lưu trữ)
    public string $statusFilter = 'active';

    // ID của thành viên lớp đang được chỉnh sửa
    public ?int $editingMemberId = null;

    // Họ tên của sinh viên đang được chỉnh sửa
    public string $editingName = '';

    // Mã số sinh viên đang được chỉnh sửa
    public string $editingStudentCode = '';

    // Email của sinh viên đang được chỉnh sửa
    public string $editingEmail = '';

    // Trạng thái của sinh viên đang được chỉnh sửa
    public string $editingStatus = 'active';

    // Thao tác với Modal Export
    public bool $isExporting = false;
    public string $exportClassId = 'all';
    public string $exportFormula = '(c + m) / t * 100';
    public string $selectedTemplate = '(c + m) / t * 100';
    public bool $isCustomFormula = false;

    // Biến cho các thao tác mảng
    public bool $isConfirmingArchive = false;

    // Trạng thái hiển thị form thêm sinh viên thủ công
    public bool $isAdding = false;

    // Họ tên sinh viên khi thêm mới
    public string $newName = '';

    // Mã số sinh viên khi thêm mới
    public string $newStudentCode = '';

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
        $this->closeImport();
        session()->flash('success', "Đã nhập thành công {$this->importSuccess} sinh viên vào lớp.");
        $this->reset(['importToken', 'isImportingStatus', 'importTotalRows', 'importProcessedRows', 'importQuietTicks']);
    }

    public function mount(): void
    {
        $this->showBackButton = request()->has('class_id');
        
        if ($this->action === 'import') {
            $this->openImport();
        } elseif ($this->action === 'export') {
            $this->openExport();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedTemplate($value): void
    {
        if (!empty($value)) {
            $this->exportFormula = $value;
        }
    }

    public function setStatusFilter(string $status): void
    {
        if (in_array($status, ['active', 'archived'], true)) {
            $this->statusFilter = $status;
            $this->resetPage();
        }
    }

    public function openEdit(int $memberId): void
    {
        $member = $this->ownedMember($memberId);

        $this->editingMemberId = $member->id;
        $this->editingName = $member->full_name;
        $this->editingStudentCode = $member->student_code;
        $this->editingEmail = $member->email ?? '';
        $this->editingStatus = $member->status;
    }

    public function closeEdit(): void
    {
        $this->reset(['editingMemberId', 'editingName', 'editingStudentCode', 'editingEmail']);
        $this->editingStatus = 'active';
        $this->resetValidation();
    }

    public function openAdd(): void
    {
        $this->isAdding = true;
        $this->newClassId = $this->classFilter !== 'all' ? $this->classFilter : '';
        $this->newEmail = '';
    }

    public function closeAdd(): void
    {
        $this->isAdding = false;
        $this->reset(['newName', 'newStudentCode', 'newClassId', 'newEmail']);
        $this->resetValidation();
    }

    public function addMember(): void
    {
        $validated = $this->validate([
            'newClassId' => ['required', 'exists:classes,id'],
            'newName' => ['required', 'string', 'max:255'],
            'newStudentCode' => ['required', 'string', 'max:50'],
            'newEmail' => ['nullable', 'email', 'max:255'],
        ], [
            'newClassId.required' => 'Vui lòng chọn lớp học.',
            'newName.required' => 'Vui lòng nhập họ tên.',
            'newStudentCode.required' => 'Vui lòng nhập mã sinh viên.',
            'newEmail.email' => 'Email không đúng định dạng.',
        ]);

        // Ensure the class belongs to the lecturer
        $courseClass = CourseClass::where('owner_user_id', auth()->id())->findOrFail($validated['newClassId']);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $courseClass->id)
            ->where('student_code', $validated['newStudentCode'])
            ->exists();

        if ($duplicateExists) {
            $this->addError('newStudentCode', 'Mã sinh viên đã tồn tại trong lớp này.');

            return;
        }

        // Kiểm tra gói: giới hạn số sinh viên mỗi lớp.
        $maxStudents = app(SubscriptionService::class)->maxStudentsPerClass(auth()->user());
        $currentCount = ClassMember::query()
            ->where('class_id', $courseClass->id)
            ->where('status', 'active')
            ->count();

        if ($currentCount >= $maxStudents) {
            $this->addError('newStudentCode', "Lớp đã đạt giới hạn {$maxStudents} sinh viên của gói hiện tại. Vui lòng nâng cấp gói để thêm sinh viên.");

            return;
        }

        $user = null;
        if ($validated['newEmail']) {
            $user = \App\Models\User::where('email', $validated['newEmail'])->first();
        }

        ClassMember::create([
            'class_id' => $courseClass->id,
            'full_name' => $validated['newName'],
            'email' => $validated['newEmail'] ?: null,
            'student_code' => strtoupper($validated['newStudentCode']),
            'user_id' => $user ? $user->id : null,
            'status' => 'active',
        ]);

        $this->closeAdd();
        session()->flash('success', 'Sinh viên đã được thêm vào lớp thành công.');
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->importClassId = $this->classFilter !== 'all' ? $this->classFilter : '';
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->reset(['importFile', 'importErrors', 'importSuccess', 'importClassId']);
    }

    public function downloadFullTemplate()
    {
        $csvContent = "Mã học viên,Họ và tên,Email,22/06,23/06\nHV001,Nguyễn Văn A,nva@email.com,c,m\nHV002,Trần Thị B,ttb@email.com,v,c";

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF".$csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_hoc_vien_mau_day_du.csv');
    }

    public function downloadBasicTemplate()
    {
        $csvContent = "Mã học viên,Họ và tên,Email\nHV001,Nguyễn Văn A,nva@email.com\nHV002,Trần Thị B,ttb@email.com";

        return response()->streamDownload(function () use ($csvContent) {
            echo "\xEF\xBB\xBF".$csvContent; // UTF-8 BOM cho Excel
        }, 'Danh_sach_hoc_vien_mau_co_ban.csv');
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
                if ($this->syncAttendance && $this->importClassId) {
                    $sessions = \App\Models\ClassSession::where('class_id', $this->importClassId)->get();
                    $members = \App\Models\ClassMember::where('class_id', $this->importClassId)->where('status', 'active')->get();
                    $recordsToInsert = [];
                    $now = now();
                    foreach ($sessions as $session) {
                        foreach ($members as $member) {
                            $recordsToInsert[] = [
                                'class_session_id' => $session->id,
                                'class_member_id' => $member->id,
                                'status' => 'pending',
                                'is_verified' => $member->user_id !== null,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                    if (!empty($recordsToInsert)) {
                        \App\Models\AttendanceRecord::insertOrIgnore($recordsToInsert);
                    }
                }

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
            'editingStudentCode' => ['required', 'string', 'max:50'],
            'editingEmail' => ['nullable', 'email', 'max:255'],
            'editingStatus' => ['required', 'in:active,dropped'],
        ], [
            'editingEmail.email' => 'Email không đúng định dạng.',
        ]);

        $duplicateExists = ClassMember::query()
            ->where('class_id', $member->class_id)
            ->where('student_code', $validated['editingStudentCode'])
            ->whereKeyNot($member->id)
            ->exists();

        if ($duplicateExists) {
            $this->addError('editingStudentCode', 'Mã sinh viên đã tồn tại trong lớp này.');

            return;
        }

        $user = null;
        if ($validated['editingEmail']) {
            $user = \App\Models\User::where('email', $validated['editingEmail'])->first();
        }

        $member->update([
            'full_name' => $validated['editingName'],
            'student_code' => strtoupper($validated['editingStudentCode']),
            'email' => $validated['editingEmail'] ?: null,
            'user_id' => $user ? $user->id : ($member->email !== $validated['editingEmail'] ? null : $member->user_id),
            'status' => $validated['editingStatus'],
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

    public function archiveMember(): void
    {
        if (! $this->archivingMemberId) {
            return;
        }

        $member = $this->ownedMember($this->archivingMemberId);
        $member->update(['status' => 'dropped']);
        $member->delete();

        $this->closeArchiveConfirm();
        session()->flash('success', 'Sinh viên đã được chuyển vào lưu trữ.');
    }

    public function restoreMember(int $memberId): void
    {
        $member = $this->ownedMember($memberId, true);
        $member->restore();
        $member->update(['status' => 'active']);

        session()->flash('success', 'Sinh viên đã được khôi phục vào lớp.');
    }

    public function openExport()
    {
        $this->exportClassId = $this->classFilter;
        $this->exportFormula = '(c + m) / t * 100';
        $this->selectedTemplate = '(c + m) / t * 100';
        $this->isCustomFormula = false;
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

        $formulaToUse = $this->isCustomFormula ? $this->exportFormula : $this->selectedTemplate;

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
            ->get(['id', 'name', 'code']); // Chỉ lấy cột cần dùng cho bộ lọc lớp.

        $members = ClassMember::query()
            ->with(['courseClass:id,name,code', 'user:id,email,avatar', 'attendanceSummary'])
            ->whereHas('courseClass', fn (Builder $query) => $query->where('owner_user_id', auth()->id()))
            ->when(
                $this->statusFilter === 'archived',
                fn (Builder $query) => $query->onlyTrashed(),
                fn (Builder $query) => $query->where('status', 'active'),
            )
            ->when($this->classFilter !== 'all', fn (Builder $query) => $query->where('class_id', $this->classFilter))
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->where('full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('student_code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('full_name')
            ->paginate(12);

        $attendanceStats = $studentService->getStudentsAttendanceStats( // Tính chuyên cần cho từng sinh viên đang hiển thị trên trang hiện tại.
            $members->getCollection()->pluck('id') // Lấy id sinh viên trong page hiện tại sau khi phân trang.
        );

        $attendanceOverview = $studentService->getTotalAttendanceStats( // Tính tổng chuyên cần theo toàn bộ bộ lọc hiện tại.
            auth()->id(), // Giới hạn dữ liệu theo giảng viên đang đăng nhập.
            $this->classFilter !== 'all' ? (int) $this->classFilter : null // Nếu chọn một lớp thì chỉ thống kê lớp đó.
        );

        $canExportExcel = app(SubscriptionService::class)->canExportExcel(auth()->user()); // Quyền xuất Excel theo gói (Pro trở lên).

        return view('livewire.lecturer.students.index', compact('classes', 'members', 'attendanceStats', 'attendanceOverview', 'canExportExcel')) // Truyền dữ liệu lớp, sinh viên và chuyên cần sang Blade.
            ->layout('layouts.user', ['title' => 'Quản lý sinh viên']);
    }
}
