@php
    // $stats được truyền từ StudentShow::render() — lấy từ LectureManageStudentService
    // (cùng nguồn với trang danh sách học viên, query live từ attendance_records).

    // Số tiết học viên có mặt đúng giờ.
    $presentLessons = (int) ($stats['present_lessons'] ?? 0);

    // Số tiết học viên đi muộn.
    $lateLessons = (int) ($stats['late_lessons'] ?? 0);

    // Số tiết học viên vắng không phép.
    $absentLessons = (int) ($stats['absent_lessons'] ?? 0);

    // Số tiết học viên vắng có phép.
    $excusedLessons = (int) ($stats['excused_lessons'] ?? 0);

    // Tổng tiết kế hoạch cả khóa học (mẫu số tính %).
    $plannedLessons = (int) ($stats['planned_lessons'] ?? 0);

    // Số tiết vắng hiệu dụng (vắng + muộn quy đổi).
    $effectiveAbsent = (int) ($stats['effective_absent_lessons'] ?? $absentLessons);

    // % chuyên cần đã được tính qua AttendanceCalculator::percentOfPlanned trong service.
    $rate = (int) ($stats['attendance_percent'] ?? 100);

    // Ngưỡng vắng tối đa cho phép (20% tổng tiết kế hoạch).
    $allowedAbsent = (int) ($stats['allowed_absent_lessons'] ?? 0);

    // Cấm thi: vắng > 20% hoặc chuyên cần < 80%.
    $isBanned = (bool) ($stats['is_banned'] ?? false);

    // Cảnh báo: chuyên cần 80–84%.
    $isWarning = (bool) ($stats['is_warning'] ?? false);
@endphp

<div class="mx-auto max-w-[1200px] space-y-6 p-4 pb-24 sm:p-8">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">Chi tiết học viên</h1><p class="mt-1 text-sm text-slate-500">Hồ sơ và lịch sử chuyên cần trong lớp học.</p></div>
        <a href="{{ route('lecturer.students.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"><x-user.icon name="users" :size="18" />Danh sách học viên</a>
    </div>

    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-[#003184] p-7 text-white shadow-lg shadow-primary/20">
        <div class="flex flex-col gap-6 md:flex-row md:items-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-3xl font-extrabold ring-1 ring-white/20">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</div>
            <div class="flex-1"><h2 class="text-2xl font-extrabold">{{ $member->full_name }}</h2><p class="mt-2 text-sm text-blue-100">{{ $member->student_code }} · {{ $member->user?->email ?? 'Chưa liên kết tài khoản' }}</p><p class="mt-1 text-sm font-semibold text-white">{{ $member->courseClass->code }} - {{ $member->courseClass->name }}</p></div>
            <span class="self-start rounded-full bg-white/15 px-4 py-2 text-xs font-bold uppercase ring-1 ring-white/20">{{ $member->trashed() ? 'Lưu trữ' : 'Đang học' }}</span>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([['Có mặt', $presentLessons, 'text-emerald-600'], ['Đi muộn', $lateLessons, 'text-amber-600'], ['Vắng', $absentLessons, 'text-red-600'], ['Có phép', $excusedLessons, 'text-blue-600']] as [$label, $value, $color])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold {{ $color }}">{{ $value }}</p></div>
        @endforeach
        <div class="rounded-2xl border bg-white p-5 shadow-sm {{ $isBanned ? 'border-red-200' : ($isWarning ? 'border-amber-200' : 'border-slate-200') }}">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Chuyên cần</p>
            <p class="mt-2 text-3xl font-extrabold {{ $isBanned ? 'text-red-600' : ($isWarning ? 'text-amber-600' : 'text-primary') }}">{{ $rate }}%</p>
            @if ($isBanned)
                <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-red-500">Nguy cơ cấm thi</p>
            @elseif ($isWarning)
                <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-amber-500">Cảnh báo chuyên cần</p>
            @endif
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5"><h3 class="font-bold text-slate-900">Lịch sử điểm danh</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-slate-50 text-xs font-bold uppercase text-slate-500"><tr><th class="px-6 py-4">Buổi học</th><th class="px-4 py-4">Ngày</th><th class="px-4 py-4">Giờ điểm danh</th><th class="px-4 py-4">Khoảng cách</th><th class="px-6 py-4 text-right">Trạng thái</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($records as $record)
                        @php $statusLabels = ['present' => 'Có mặt', 'late' => 'Đi muộn', 'absent' => 'Vắng', 'excused' => 'Có phép', 'pending' => 'Chờ xác nhận', 'invalid' => 'Không hợp lệ']; @endphp
                        <tr><td class="px-6 py-4 text-sm font-bold text-slate-800">{{ $record->classSession?->name }}</td><td class="px-4 py-4 text-sm text-slate-600">{{ $record->classSession?->date?->format('d/m/Y') }}</td><td class="px-4 py-4 text-sm text-slate-600">{{ $record->check_in_time?->format('H:i:s') ?? '—' }}</td><td class="px-4 py-4 text-sm text-slate-600">{{ $record->distance_meters ? $record->distance_meters.' m' : '—' }}</td><td class="px-6 py-4 text-right"><span @class(['rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $record->status === 'present', 'bg-amber-50 text-amber-700' => $record->status === 'late', 'bg-red-50 text-red-700' => in_array($record->status, ['absent', 'invalid'], true), 'bg-blue-50 text-blue-700' => $record->status === 'excused', 'bg-slate-100 text-slate-600' => $record->status === 'pending'])>{{ $statusLabels[$record->status] ?? $record->status }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-14 text-center text-sm text-slate-500">Chưa có lịch sử điểm danh.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($records->hasPages())<div class="border-t border-slate-100 px-6 py-4">{{ $records->links() }}</div>@endif
    </section>
</div>
