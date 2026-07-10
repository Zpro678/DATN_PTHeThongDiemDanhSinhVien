<?php

namespace App\Livewire\Lecturer;

use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Services\LectureManageStudentService;
use App\Services\SubscriptionService;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ClassShow extends Component
{
    use WithFileUploads, WithPagination;

    // Đối tượng chứa thông tin chi tiết của lớp học hiện tại
    public CourseClass $class;

    // Bộ lọc danh sách học viên qua URL (?filter=warning|banned). Rỗng = xem tất cả.
    // Dùng để thông báo "sắp vượt ngưỡng vắng" dẫn thẳng tới nhóm SV liên quan.
    #[Url]
    public string $filter = '';

    // Từ khóa tìm kiếm học viên theo tên / MSSV / email.
    public string $search = '';

    public function getRecentSessionsProperty()
    {
        return $this->class->sessions()
            ->withCount([
                'attendanceRecords as present_count' => function ($query) {
                    $query->where('status', 'present');
                },
                'attendanceRecords as absent_count' => function ($query) {
                    $query->where('status', 'absent');
                },
                'attendanceRecords as late_count' => function ($query) {
                    $query->where('status', 'late');
                },
                'attendanceRecords as excused_count' => function ($query) {
                    $query->where('status', 'excused');
                }
            ])
            ->latest('date')
            ->latest('start_time')
            ->take(5)
            ->get();
    }

    // Thống kê hiển thị trên trang
    public int $studentsCount = 0;
    
    // Tổng số buổi học dự kiến của lớp
    public int $sessionsCount = 0;

    // Tổng số buổi học đã hoàn thành hoặc đang diễn ra
    public int $sessionsCompleted = 0;

    // Số lượng đơn xin phép nghỉ đang chờ duyệt của lớp này
    public int $pendingLeaveRequests = 0;

    // Số lượng sinh viên đang chờ duyệt vào lớp
    public int $pendingMembersCount = 0;

    // Import state
    // Trạng thái hiển thị modal import sinh viên
    public bool $isImporting = false;

    // Đối tượng file Excel/CSV được chọn để import
    public $importFile;

    // Mảng lưu trữ các lỗi phát sinh trong quá trình import
    public array $importErrors = [];

    // Số lượng sinh viên đã được import thành công vào lớp
    public int $importSuccess = 0;

    public bool $showNoStudentsPopup = false;

    public bool $isEditingCode = false;
    public string $newClassCode = '';

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
        // Cập nhật lại số sinh viên và số buổi
        $this->studentsCount = $this->class->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count();
        $this->sessionsCount = $this->class->sessions()->count();
        $this->sessionsCompleted = $this->class->sessions()->whereIn('status', ['closed', 'active'])->count();
        
        $dbErrors = \App\Models\ImportError::where('import_token', $this->importToken)->orderBy('row_index')->get();
        if ($dbErrors->count() > 0) {
            $this->importErrors = $dbErrors->map(function ($error) {
                return ($error->row_index ? "Dòng {$error->row_index}: " : "") . $error->error_message;
            })->toArray();
        }

        if ($dbErrors->count() === 0) {
            $this->isImportingStatus = false;
            $message = "Đã xử lý xong file dữ liệu sinh viên thành công.";
            $this->dispatch('toast', message: $message, type: 'success');
        } else {
            // Có cảnh báo/lỗi thì mở lại modal để người dùng đọc
            $this->isImporting = true;
            $this->isImportingStatus = false;
            $message = "Đã xử lý xong dữ liệu, nhưng có một số dòng bị lỗi. Vui lòng xem chi tiết.";
            $this->dispatch('toast', message: $message, type: 'warning');
        }

        $this->reset(['importToken', 'importTotalRows', 'importProcessedRows', 'importQuietTicks']);
    }

    public function openImportFromPopup(): void
    {
        $this->showNoStudentsPopup = false;
        $this->openImport();
    }

    public function mount(CourseClass $courseClass): void
    {
        // Kiểm tra quyền — chỉ chủ lớp mới được xem
        abort_unless(
            $courseClass->isManagedBy(auth()->id()),
            403,
            'Bạn không có quyền xem lớp học này.'
        );

        $this->class = $courseClass->load(['sessions' => function ($q) {
            $q->orderByDesc('date')->orderByDesc('created_at');
        }]);

        $this->loadStats();

        if (request()->has('openImport')) {
            $this->openImport();
            session()->flash('status', 'Vui lòng import danh sách lớp trước khi điểm danh.');
        }

        if (request()->has('importToken')) {
            $this->importToken = request()->query('importToken');
            $this->isImportingStatus = true;
        }
    }
    
    /**
     * Tính lại các số liệu ở thẻ đầu trang (sĩ số, số buổi, chờ duyệt...).
     * Tách riêng để dùng chung cho mount() và làm mới realtime refreshClass().
     */
    private function loadStats(): void
    {
        $this->studentsCount = $this->class->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count();
        $this->sessionsCount = $this->class->sessions()->count();
        $this->sessionsCompleted = $this->class->sessions()->whereIn('status', ['closed', 'active'])->count();
        $this->pendingLeaveRequests = LeaveRequest::whereHas('classMeeting', function ($q) {
            $q->where('class_id', $this->class->id);
        })->where('status', 'pending')->count();
        $this->pendingMembersCount = $this->class->joinRequests()
            ->whereIn('status', [\App\Models\ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->count();
    }

    /**
     * Tên kênh socket.io mà trang chi tiết lớp lắng nghe để cập nhật realtime.
     *
     * StudentJoinedClass broadcast qua Redis (kèm prefix), server.cjs relay sang
     * socket.io; client (Alpine x-init) nghe kênh này rồi gọi $wire.refreshClass().
     */
    public function realtimeChannel(): string
    {
        return (string) config('database.redis.options.prefix') . 'class.' . $this->class->id;
    }

    public function checkBeforeAttendance(string $type): void
    {
        if ($this->studentsCount === 0) {
            $this->showNoStudentsPopup = true;
            return;
        }

        $this->dispatch('open-quick-attendance-modal', type: $type, classId: (string) $this->class->id);
    }

    public function toggleEditCode(): void
    {
        $this->isEditingCode = !$this->isEditingCode;
        if ($this->isEditingCode) {
            $this->newClassCode = $this->class->join_key;
        } else {
            $this->resetValidation('newClassCode');
        }
    }

    public function updateClassCode(): void
    {
        $this->validate([
            'newClassCode' => 'required|string|max:50|unique:classes,join_key,' . $this->class->id,
        ], [
            'newClassCode.required' => 'Mã lớp không được để trống.',
            'newClassCode.unique' => 'Mã lớp này đã tồn tại trong hệ thống.',
            'newClassCode.max' => 'Mã lớp không được vượt quá 50 ký tự.',
        ]);

        $this->class->update([
            'join_key' => $this->newClassCode,
        ]);

        $this->isEditingCode = false;
        session()->flash('success', 'Đã cập nhật mã lớp thành công.');
    }

    public function generateRandomCode(): void
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (CourseClass::where('join_key', $code)->where('id', '!=', $this->class->id)->exists());

        $this->newClassCode = $code;
    }

    public function render()
    {
        // Tính lại số liệu mỗi lần render để mọi lần làm mới (socket realtime, thao tác
        // Livewire) đều phản ánh đúng DB — sửa lỗi số liệu chỉ tính ở mount() nên đứng yên.
        $this->loadStats();

        $students = $this->class->members()
            ->with(['user', 'profile'])
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->get()
            ->sortBy(function ($m) {
                $parts = explode(' ', trim((string) $m->full_name));
                return end($parts);
            })
            ->values();

        // Dùng service để tính chuyên cần đồng nhất với trang danh sách học viên.
        $memberIds = $students->pluck('id')->all();
        $statsMap  = $memberIds
            ? app(LectureManageStudentService::class)->getStudentsAttendanceStats($memberIds)
            : [];

        // Đếm số lượng từng nhóm trên TOÀN lớp (không phụ thuộc search) để hiển thị badge.
        $warningTotal = $students->filter(fn ($m) => (bool) ($statsMap[$m->id]['is_warning'] ?? false))->count();
        $bannedTotal  = $students->filter(fn ($m) => (bool) ($statsMap[$m->id]['is_banned'] ?? false))->count();

        // Tìm kiếm theo tên / MSSV / email.
        $search = trim(mb_strtolower($this->search));
        if ($search !== '') {
            $students = $students->filter(function ($m) use ($search) {
                $email = $m->email ?? ($m->user->email ?? '');
                $haystack = mb_strtolower(trim(
                    ($m->full_name ?? '').' '.($m->student_code ?? '').' '.$email
                ));

                return str_contains($haystack, $search);
            })->values();
        }

        // Lọc theo trạng thái chuyên cần (từ thông báo cảnh báo hoặc nút lọc trên trang).
        // warning = sắp vượt ngưỡng vắng (tô vàng); banned = đã vượt/nguy cơ cấm thi.
        $activeFilter = in_array($this->filter, ['warning', 'banned'], true) ? $this->filter : '';
        if ($activeFilter !== '') {
            $flag = $activeFilter === 'warning' ? 'is_warning' : 'is_banned';
            $students = $students
                ->filter(fn ($m) => (bool) ($statsMap[$m->id][$flag] ?? false))
                ->values();
        }

        // Link tham gia lớp + mã QR để học viên quét (dùng chung trong modal Chia sẻ).
        $shareUrl = url('/student/join-class?code=' . $this->class->join_key);

        return view('livewire.lecturer.class-show', [
            'recentSessions' => $this->recentSessions,
            'students'       => $students,
            'statsMap'       => $statsMap,
            'activeFilter'   => $activeFilter,
            'warningTotal'   => $warningTotal,
            'bannedTotal'    => $bannedTotal,
            'canExportExcel' => app(\App\Services\SubscriptionService::class)->canExportExcel(auth()->user()),
            'shareUrl'       => $shareUrl,
            'shareQr'        => $this->shareQrSvg($shareUrl),
        ])->layout('layouts.user', ['title' => $this->class->name]);
    }

    /**
     * Tạo mã QR (SVG) cho đường dẫn tham gia lớp; trả null nếu không tạo được.
     */
    private function shareQrSvg(string $url): ?string
    {
        if (! class_exists(QrCode::class)) {
            return null;
        }

        try {
            return (string) QrCode::format('svg')
                ->size(220)
                ->margin(1)
                ->errorCorrection('M')
                ->generate($url);
        } catch (\Throwable) {
            return null;
        }
    }

    public function clearFilter(): void
    {
        $this->filter = '';
        $this->search = '';
    }

    /**
     * Tải trực tiếp file Excel danh sách học viên của CHÍNH lớp đang xem
     * (theo từ khóa tìm kiếm hiện tại). Không điều hướng sang trang khác.
     */
    public function exportExcel()
    {
        // Gate theo gói: chỉ gói bật tính năng xuất Excel mới tải được.
        if (! app(SubscriptionService::class)->canExportExcel(auth()->user())) {
            session()->flash('upgrade_required', 'Xuất báo cáo Excel là tính năng của gói Pro trở lên. Vui lòng nâng cấp để sử dụng.');

            return $this->redirect(route('upgrade'), navigate: true);
        }

        $fileName = 'danh_sach_sinh_vien_' . Str::slug($this->class->name) . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(
            new StudentsExport(
                (int) auth()->id(),
                (string) $this->class->id,   // chỉ lớp hiện tại
                'all',
                $this->search,               // theo tìm kiếm đang gõ
                '(c + m + p) / t * 100',
            ),
            $fileName
        );
    }

    /**
     * Tìm học viên đang hoạt động trong lớp này kèm thống kê chuyên cần hiện thời.
     *
     * @return array{0: ?\App\Models\ClassMember, 1: array<string, mixed>}
     */
    private function resolveMemberForAction(int $memberId): array
    {
        $member = $this->class->members()
            ->where('status', \App\Models\ClassMember::STATUS_ACTIVE)
            ->find($memberId);

        if (! $member) {
            $this->dispatch('toast', message: 'Không tìm thấy sinh viên trong lớp.', type: 'error');

            return [null, []];
        }

        $stats = app(LectureManageStudentService::class)->getStudentsAttendanceStats([$memberId]);

        return [$member, $stats[$memberId] ?? []];
    }

    /**
     * Chủ lớp gửi cảnh báo chuyên cần cho một sinh viên đang ở mức "sắp vượt ngưỡng".
     */
    public function sendAttendanceWarning(int $memberId): void
    {
        [$member, $stats] = $this->resolveMemberForAction($memberId);

        if (! $member) {
            return;
        }

        if (empty($stats['is_warning'])) {
            $this->dispatch('toast', message: 'Sinh viên không còn ở mức cảnh báo.', type: 'error');

            return;
        }

        if (! $member->user_id) {
            $this->dispatch('toast', message: 'Sinh viên chưa liên kết tài khoản nên không thể nhận thông báo.', type: 'error');

            return;
        }

        $sent = app(\App\Services\NotificationService::class)->sendManualAbsenceWarning(
            (int) $member->user_id,
            $this->class,
            (int) ($stats['attendance_percent'] ?? 0),
        );

        $this->dispatch(
            'toast',
            message: $sent ? 'Đã gửi cảnh báo chuyên cần cho sinh viên.' : 'Sinh viên đã có cảnh báo chưa đọc.',
            type: $sent ? 'success' : 'info',
        );
    }

    /**
     * Chủ lớp gửi thông báo cấm thi cho một sinh viên đã vượt ngưỡng vắng cho phép.
     */
    public function sendExamBan(int $memberId): void
    {
        [$member, $stats] = $this->resolveMemberForAction($memberId);

        if (! $member) {
            return;
        }

        if (empty($stats['is_banned'])) {
            $this->dispatch('toast', message: 'Sinh viên chưa vượt ngưỡng nên không thể cấm thi.', type: 'error');

            return;
        }

        if (! $member->user_id) {
            $this->dispatch('toast', message: 'Sinh viên chưa liên kết tài khoản nên không thể nhận thông báo.', type: 'error');

            return;
        }

        $sent = app(\App\Services\NotificationService::class)->sendExamBanNotice(
            (int) $member->user_id,
            $this->class,
            (int) ($stats['attendance_percent'] ?? 0),
        );

        $this->dispatch(
            'toast',
            message: $sent ? 'Đã gửi thông báo cấm thi cho sinh viên.' : 'Sinh viên đã có thông báo cấm thi chưa đọc.',
            type: $sent ? 'success' : 'info',
        );
    }

    public function openImport(): void
    {
        $this->isImporting = true;
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function closeImport(): void
    {
        $this->isImporting = false;
        $this->reset(['importFile', 'importErrors', 'importSuccess']);
    }

    public function downloadFullTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateExport(), 'Danh_sach_hoc_vien_mau_day_du.xlsx');
    }

    public function downloadBasicTemplate()
    {
        return Excel::download(new \App\Exports\ImportTemplateExport(), 'Danh_sach_hoc_vien_mau_co_ban.xlsx');
    }

    public function processImport(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'extensions:xlsx,xls,csv', 'max:5120'], // Max 5MB
        ], [
            'importFile.required' => 'Vui lòng chọn file Excel hoặc CSV.',
            'importFile.extensions' => 'Định dạng file không hỗ trợ. Vui lòng dùng .xlsx, .xls, .csv',
        ]);

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

        try {
            // Bước 1: Phân tích header trước (đọc 10 dòng đầu)
            $headingImport = new \App\Imports\HeadingRowImport($this->class->id);
            Excel::import($headingImport, $this->importFile->getRealPath(), null, $readerType);

            if (!empty($headingImport->errors)) {
                $this->importErrors = $headingImport->errors;
                $this->isImportingStatus = false;
                return;
            }

            $meetingHeaders = array_unique($headingImport->meetingHeaders);

            // Bước 2: Tạo Bus::batch và StartImportJob
            $batch = \Illuminate\Support\Facades\Bus::batch([
                new \App\Jobs\StartImportJob(
                    $this->importFile->getRealPath(),
                    $this->class->id,
                    $headingImport->dateHeaders,
                    $headingImport->meetingHeaders,
                    $headingImport->emailColIndex,
                    $headingImport->nameColIndex,
                    $headingImport->codeColIndex,
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
            ->name('Import Students')
            ->dispatch();

            $this->importToken = $batch->id;
            
            // Đóng popup để người dùng rảnh tay, hiện thanh tiến trình chạy ngầm
            $this->isImporting = false;
        } catch (\Exception $e) {
            $this->isImportingStatus = false;
            $this->importToken = null;
            $this->addError('importFile', 'Có lỗi khi đọc file: '.$e->getMessage());
        }
    }
}
