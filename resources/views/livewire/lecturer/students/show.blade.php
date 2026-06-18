@php
    $summary = $member->attendanceSummary;
    $total = ($summary?->total_present ?? 0) + ($summary?->total_late ?? 0) + ($summary?->total_absent ?? 0) + ($summary?->total_excused ?? 0);
    $rate = $total > 0 ? (int) round((($summary?->total_present ?? 0) + ($summary?->total_late ?? 0)) / $total * 100) : 0;
@endphp

<div class="mx-auto max-w-[1200px] space-y-6 p-4 pb-24 sm:p-8">
    <div class="flex items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-slate-900">Chi tiết sinh viên</h1><p class="mt-1 text-sm text-slate-500">Hồ sơ và lịch sử chuyên cần trong lớp học.</p></div>
        <a href="{{ route('lecturer.students.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"><x-user.icon name="users" :size="18" />Danh sách sinh viên</a>
    </div>

    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-primary to-[#003184] p-7 text-white shadow-lg shadow-primary/20">
        <div class="flex flex-col gap-6 md:flex-row md:items-center">
            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white/15 text-3xl font-extrabold ring-1 ring-white/20">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</div>
            <div class="flex-1"><h2 class="text-2xl font-extrabold">{{ $member->full_name }}</h2><p class="mt-2 text-sm text-blue-100">{{ $member->student_code }} · {{ $member->user?->email ?? 'Chưa liên kết tài khoản' }}</p><p class="mt-1 text-sm font-semibold text-white">{{ $member->courseClass->code }} - {{ $member->courseClass->name }}</p></div>
            <span class="self-start rounded-full bg-white/15 px-4 py-2 text-xs font-bold uppercase ring-1 ring-white/20">{{ $member->trashed() ? 'Lưu trữ' : 'Đang học' }}</span>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([['Có mặt', $summary?->total_present ?? 0, 'text-emerald-600'], ['Đi muộn', $summary?->total_late ?? 0, 'text-amber-600'], ['Vắng', $summary?->total_absent ?? 0, 'text-red-600'], ['Có phép', $summary?->total_excused ?? 0, 'text-blue-600'], ['Chuyên cần', $rate.'%', 'text-primary']] as [$label, $value, $color])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold {{ $color }}">{{ $value }}</p></div>
        @endforeach
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
