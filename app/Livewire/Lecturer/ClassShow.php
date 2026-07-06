<?php

namespace App\Livewire\Lecturer;

use App\Imports\StudentsImport;
use App\Models\CourseClass;
use App\Models\LeaveRequest;
use App\Services\LectureManageStudentService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassShow extends Component
{
    use WithFileUploads, WithPagination;

    // Đối tượng chứa thông tin chi tiết của lớp học hiện tại
    public CourseClass $class;

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

    // Quick Start Modal State
    public bool $showQuickStart = false;
    public string $quickStartType = 'manual';
    public string $quickClassId = '';
    public string $quickMeetingId = '';
    public string $newMeetingName = '';
    public string $meetingEndTime = '';
    public string $sessionName = '';
    public int $durationMinutes = 15;
    public int $gpsRadius = 100;
    public int $qrRefreshRate = 10;
    public bool $gpsEnabled = true;
    public ?float $gpsLatitude = null;
    public ?float $gpsLongitude = null;

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
            $courseClass->owner_user_id === auth()->id(),
            403,
            'Bạn không có quyền xem lớp học này.'
        );

        $this->class = $courseClass->load(['sessions' => function ($q) {
            $q->orderByDesc('date')->orderByDesc('created_at');
        }]);

        $this->studentsCount   = $courseClass->members()->where('status', \App\Models\ClassMember::STATUS_ACTIVE)->count();
        $this->sessionsCount = $courseClass->sessions()->count();
        $this->sessionsCompleted = $courseClass->sessions()->whereIn('status', ['closed', 'active'])->count();
        $this->pendingLeaveRequests = LeaveRequest::whereHas('classSession', function ($q) use ($courseClass) {
            $q->where('class_id', $courseClass->id);
        })->where('status', 'pending')->count();
        $this->pendingMembersCount = $courseClass->joinRequests()
            ->whereIn('status', [\App\Models\ClassJoinRequest::STATUS_PENDING, 'pending'])
            ->count();
        
        if (request()->has('openImport')) {
            $this->openImport();
            session()->flash('status', 'Vui lòng import danh sách lớp trước khi điểm danh.');
        }

        if (request()->has('importToken')) {
            $this->importToken = request()->query('importToken');
            $this->isImportingStatus = true;
        }
    }
    
    public function updatedQuickMeetingId()
    {
        if ($this->quickMeetingId) {
            $meeting = \App\Models\ClassMeeting::find($this->quickMeetingId);
            if ($meeting) {
                $count = $meeting->sessions()->count();
                $this->sessionName = 'Phiên ' . ($count + 1);
            }
        } else {
            $this->sessionName = 'Phiên 1';
        }
    }

    public function checkBeforeAttendance(string $type): void
    {
        if ($this->studentsCount === 0) {
            $this->showNoStudentsPopup = true;
            return;
        }

        $this->quickStartType = $type;
        $this->showQuickStart = true;
        
        $this->quickClassId = (string) $this->class->id;
        $this->quickMeetingId = '';
        $this->sessionName = 'Phiên 1';
        $this->durationMinutes = 15;
        $this->gpsRadius = 100;
        $this->qrRefreshRate = 10;
        $this->gpsEnabled = true;
        $this->gpsLatitude = null;
        $this->gpsLongitude = null;
        $this->meetingEndTime = now()->addMinutes(120)->format('H:i');
    }

    public function createTodayMeeting()
    {
        $date = now()->toDateString();
        $startTime = now()->format('H:i');
        $endTime = $this->meetingEndTime ?: now()->addMinutes(120)->format('H:i');

        $meetingCount = $this->class->meetings()->count() + 1;
        $meetingName = 'Buổi ' . $meetingCount;

        $meeting = \App\Models\ClassMeeting::create([
            'class_id' => $this->class->id,
            'user_Created' => auth()->id(),
            'name' => $meetingName,
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'active',
        ]);

        $this->quickMeetingId = (string) $meeting->id;
        $this->sessionName = 'Phiên 1';
    }

    public function startQuick()
    {
        $rules = [
            'sessionName' => 'required|string|max:255',
            'meetingEndTime' => 'required|date_format:H:i',
        ];
        $messages = [
            'sessionName.required' => 'Vui lòng nhập tên phiên.',
            'meetingEndTime.required' => 'Vui lòng chọn thời gian kết thúc.',
        ];

        if ($this->quickMeetingId === 'NEW') {
            $rules['newMeetingName'] = 'required|string|max:255';
            $messages['newMeetingName.required'] = 'Vui lòng nhập tên buổi học mới.';
        } else {
            $rules['quickMeetingId'] = 'required';
            $messages['quickMeetingId.required'] = 'Vui lòng chọn buổi học.';
        }

        if ($this->quickStartType === 'qr') {
            $rules['durationMinutes'] = 'required|integer|min:1';
            $rules['qrRefreshRate'] = 'required|integer|min:5';
            $rules['gpsRadius'] = 'required|integer|min:5';
            
            if ($this->gpsEnabled) {
                $rules['gpsLatitude'] = 'required|numeric';
                $rules['gpsLongitude'] = 'required|numeric';
                $messages['gpsLatitude.required'] = 'Vui lòng cấp quyền truy cập vị trí GPS để chống gian lận.';
            }
        }

        $this->validate($rules, $messages);
        
        if ($this->quickMeetingId === 'NEW') {
            $date = now()->toDateString();
            $startTime = now()->format('H:i');
            $endTime = $this->meetingEndTime ?: now()->addMinutes(120)->format('H:i');
            
            $meeting = \App\Models\ClassMeeting::create([
                'class_id' => $this->class->id,
                'user_Created' => auth()->id(),
                'name' => $this->newMeetingName,
                'date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'active',
            ]);
            
            $this->quickMeetingId = (string) $meeting->id;
        }

        $meeting = \App\Models\ClassMeeting::query()
            ->where('class_id', $this->class->id)
            ->findOrFail($this->quickMeetingId);

        if (! $meeting->canAddSession()) {
            $this->addError('quickMeetingId', 'Buổi điểm danh đã kết thúc, không thể thêm phiên mới.');
            return;
        }

        $meeting->update([
            'status' => 'active',
            'end_time' => $this->meetingEndTime ?: $meeting->end_time,
        ]);

        if ($this->quickStartType === 'manual') {
            $session = $meeting->createSession('active', [
                'name' => $this->sessionName,
            ]);
            $this->redirectRoute('lecturer.attendance.manual.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
        } else {
            $session = $meeting->createSession('active', [
                'name' => $this->sessionName,
                'qr_token' => Str::upper(Str::random(24)),
                'token_expires_at' => now()->addMinutes($this->durationMinutes),
                'qr_refresh_rate' => $this->qrRefreshRate,
                'gps_latitude' => $this->gpsEnabled ? $this->gpsLatitude : null,
                'gps_longitude' => $this->gpsEnabled ? $this->gpsLongitude : null,
                'gps_radius' => $this->gpsEnabled ? $this->gpsRadius : null,
            ]);

            app(\App\Services\NotificationService::class)->attendanceSessionCreated((int) auth()->id(), $session, isQr: true);
            $this->redirectRoute('lecturer.attendance.qr.session', ['ma_user' => auth()->id(), 'session' => $session->id], navigate: true);
        }
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

    #[Computed]
    public function classMeetings()
    {
        return \App\Models\ClassMeeting::where('class_id', $this->class->id)
            ->whereDate('date', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->filter(fn ($meeting) => !$meeting->isExpired());
    }

    #[Computed]
    public function activeClasses()
    {
        return collect([$this->class]);
    }

    public function render()
    {
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

        return view('livewire.lecturer.class-show', [
            'recentSessions' => $this->recentSessions,
            'students'       => $students,
            'statsMap'       => $statsMap,
        ])->layout('layouts.user', ['title' => $this->class->name]);
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
