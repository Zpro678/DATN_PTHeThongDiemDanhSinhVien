@php
    // $stats được truyền từ StudentShow::render() — lấy từ LectureManageStudentService
    // (cùng nguồn với trang danh sách học viên, query live từ attendance_records).

    // Số tiết học viên có mặt đúng giờ.
    $presentSessions = (int) ($stats['present_sessions'] ?? 0);

    // Số tiết học viên đi muộn.
    $lateSessions = (int) ($stats['late_sessions'] ?? 0);

    // Số tiết học viên vắng không phép.
    $absentSessions = (int) ($stats['absent_sessions'] ?? 0);

    // Số tiết học viên vắng có phép.
    $excusedSessions = (int) ($stats['excused_sessions'] ?? 0);

    // Tổng tiết kế hoạch cả khóa học (mẫu số tính %).
    $plannedSessions = (int) ($stats['planned_sessions'] ?? 0);

    // Số tiết vắng hiệu dụng (vắng + muộn quy đổi).
    $effectiveAbsent = (int) ($stats['effective_absent_sessions'] ?? $absentSessions);

    // % chuyên cần đã được tính qua AttendanceCalculator::percentOfPlanned trong service.
    $rate = (int) ($stats['attendance_percent'] ?? 100);

    // Ngưỡng vắng tối đa cho phép (20% tổng tiết kế hoạch).
    $allowedAbsent = (int) ($stats['allowed_absent_sessions'] ?? 0);

    // Cấm thi: vắng > 20% hoặc chuyên cần < 80%.
    $isBanned = (bool) ($stats['is_banned'] ?? false);

    // Cảnh báo: chuyên cần 80–84%.
    $isWarning = (bool) ($stats['is_warning'] ?? false);
@endphp

<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-700 p-7 text-white shadow-lg shadow-blue-600/25">
        {{-- Decorative circles --}}
        <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="pointer-events-none absolute -bottom-8 -left-8 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="relative flex flex-col gap-6 md:flex-row md:items-center">
            @if($member->user && $member->user->avatar)
                <img src="{{ asset('storage/' . $member->user->avatar) }}" alt="{{ $member->full_name }}" class="h-20 w-20 shrink-0 rounded-2xl object-cover ring-2 ring-white/25">
            @else
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-white/20 text-3xl font-extrabold backdrop-blur-sm ring-2 ring-white/25">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</div>
            @endif
            <div class="flex-1"><h2 class="text-2xl font-extrabold">{{ $member->full_name }}</h2><p class="mt-2 text-sm text-blue-100">{{ $member->student_code }} · {{ $member->user?->email ?? 'Chưa liên kết tài khoản' }}</p><p class="mt-1 text-sm font-semibold text-white/90">{{ $member->courseClass->join_key }} - {{ $member->courseClass->name }}</p></div>
            <span class="self-start rounded-full bg-white/20 px-4 py-2 text-xs font-bold uppercase ring-1 ring-white/25">{{ $member->trashed() ? 'Lưu trữ' : 'Đang học' }}</span>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([['Có mặt', $presentSessions, 'text-emerald-600'], ['Đi muộn', $lateSessions, 'text-amber-600'], ['Vắng', $absentSessions, 'text-red-600'], ['Có phép', $excusedSessions, 'text-blue-600']] as [$label, $value, $color])
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

    <div>
        <h3 class="mb-4 text-xl font-bold text-slate-900">Lịch sử điểm danh</h3>
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="bg-slate-50 text-sm font-bold uppercase text-slate-500"><tr><th class="px-6 py-4">Buổi học</th><th class="px-4 py-4">Ngày</th><th class="px-4 py-4">Giờ điểm danh</th><th class="px-4 py-4">Khoảng cách</th><th class="px-6 py-4 text-right">Trạng thái</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $row)
                            <tr class="transition-colors hover:bg-slate-50/70">
                                <td class="px-6 py-5 text-[15px] font-bold text-slate-800">{{ $row['name'] }}</td>
                                <td class="px-4 py-5 text-[15px] text-slate-600">{{ $row['date']?->format('d/m/Y') }}</td>
                                <td class="px-4 py-5 text-[15px] text-slate-600">{{ $row['check_in_time']?->format('H:i:s') ?? '—' }}</td>
                                <td class="px-4 py-5 text-[15px] text-slate-600">{{ $row['distance_meters'] ? $row['distance_meters'].' m' : '—' }}</td>
                                <td class="px-6 py-5 text-right">
                                    <span @class([
                                        'rounded-full px-3 py-1.5 text-sm font-bold',
                                        'bg-emerald-50 text-emerald-700' => $row['status'] === 'present',
                                        'bg-amber-50 text-amber-700' => $row['status'] === 'late',
                                        'bg-red-50 text-red-700' => $row['status'] === 'absent',
                                        'bg-blue-50 text-blue-700' => $row['status'] === 'excused',
                                    ])>{{ $row['label'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-14 text-center text-sm text-slate-500">Chưa có lịch sử điểm danh.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
