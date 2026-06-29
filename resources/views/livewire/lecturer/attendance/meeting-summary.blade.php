@php
    $sessionBadge = [
        'present' => ['Có mặt', 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'],
        'late'    => ['Đi muộn', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'excused' => ['Có phép', 'bg-sky-50 text-sky-700 ring-sky-600/20'],
        'absent'  => ['Vắng', 'bg-rose-50 text-rose-700 ring-rose-600/20'],
        'invalid' => ['Lỗi', 'bg-rose-50 text-rose-700 ring-rose-600/20'],
        'pending' => ['—', 'bg-slate-50 text-slate-400 ring-slate-400/20'],
    ];
    $finalBadge = [
        'present'     => 'border-emerald-300 bg-emerald-50 text-emerald-700',
        'late'        => 'border-amber-300 bg-amber-50 text-amber-700',
        'partial'     => 'border-orange-300 bg-orange-50 text-orange-700',
        'early_leave' => 'border-rose-300 bg-rose-50 text-rose-700',
        'excused'     => 'border-sky-300 bg-sky-50 text-sky-700',
        'absent'      => 'border-rose-300 bg-rose-50 text-rose-700',
    ];
@endphp

<div class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">
    <a href="{{ route('lecturer.attendance.meeting.sessions', ['ma_user' => auth()->id(), 'meeting' => $meeting->id]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-800">
        <x-user.icon name="arrow-left" :size="16" />Quay lại danh sách phiên
    </a>

    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
                <x-user.icon name="clipboard-check" class="text-primary" />Tổng kết: {{ $meeting->name }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                <span class="font-bold text-slate-700">{{ $meeting->courseClass->join_key }}</span> · {{ $meeting->courseClass->name }} ·
                {{ $meeting->date->format('d/m/Y') }}
                @if($meeting->end_time) · Kết thúc {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}@endif
                <span class="ml-2 inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $meeting->status === 'closed' ? 'bg-sky-50 text-sky-700 ring-sky-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">{{ $meeting->status === 'closed' ? 'Đã kết thúc' : 'Đang mở' }}</span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="recompute" wire:confirm="Tính lại sẽ bỏ mọi chỉnh sửa thủ công và lấy lại theo dữ liệu các phiên. Tiếp tục?" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <x-user.icon name="refresh-cw" :size="16" />Tính lại tự động
            </button>
            <button type="button" wire:click="save" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <x-user.icon name="save" :size="16" />Lưu tổng kết
            </button>
            @if($canExportExcel)
                <button type="button" wire:click="exportExcel" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    <x-user.icon name="download" :size="16" />Xuất file tổng kết
                </button>
            @else
                <a href="{{ route('upgrade') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-amber-100 px-5 py-2.5 text-sm font-bold text-amber-700 shadow-sm ring-1 ring-inset ring-amber-200 transition hover:bg-amber-200">
                    <x-user.icon name="download" :size="16" />Xuất file (Nâng cấp Pro)
                </a>
            @endif
        </div>
    </section>

    @if(session('status'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
    @if(session('upgrade_required'))<div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700">{{ session('upgrade_required') }}</div>@endif

    {{-- Thẻ thống kê --}}
    <section class="grid grid-cols-2 gap-4 md:grid-cols-5">
        <div class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Có mặt</p><p class="mt-1 text-2xl font-black text-emerald-600">{{ $totals['present'] ?? 0 }}</p></div>
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Đi muộn / giữa giờ</p><p class="mt-1 text-2xl font-black text-amber-600">{{ ($totals['late'] ?? 0) + ($totals['partial'] ?? 0) }}</p></div>
        <div class="rounded-2xl border border-rose-100 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Vắng / về sớm</p><p class="mt-1 text-2xl font-black text-rose-600">{{ ($totals['absent'] ?? 0) + ($totals['early_leave'] ?? 0) }}</p></div>
        <div class="rounded-2xl border border-sky-100 bg-white p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-400">Có phép</p><p class="mt-1 text-2xl font-black text-sky-600">{{ $totals['excused'] ?? 0 }}</p></div>
        <div class="rounded-2xl border border-slate-200 bg-slate-900 p-4 shadow-sm"><p class="text-xs font-bold uppercase text-slate-300">Tổng điểm trừ</p><p class="mt-1 text-2xl font-black text-white">-{{ rtrim(rtrim(number_format($totals['deduction'] ?? 0, 1), '0'), '.') }}</p></div>
    </section>

    <div class="rounded-2xl border border-blue-100 bg-blue-50/60 px-5 py-3 text-xs font-medium leading-relaxed text-blue-800">
        <span class="font-bold">Quy tắc tổng kết:</span> Vắng ở phiên cuối → Vắng cả buổi (−1). Có mặt phiên cuối nhưng từng vắng phiên trước → Đi muộn (−0.5). Có mặt tất cả phiên → Có mặt (0). Bạn có thể chỉnh tay từng dòng trước khi xuất file.
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-slate-50 text-sm font-extrabold uppercase tracking-wider text-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="px-6 py-4 text-left">Sinh viên</th>
                        @foreach($sessions as $index => $session)
                            <th class="px-3 py-4 text-center" title="{{ $session->name }}">Lần {{ $index + 1 }}</th>
                        @endforeach
                        <th class="px-4 py-4 text-center">Tổng kết</th>
                        <th class="px-4 py-4 text-center">Điểm trừ</th>
                        <th class="px-4 py-4 text-left">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        @php($memberId = $row['member']->id)
                        <tr class="transition-colors hover:bg-blue-50/30">
                            <td class="px-6 py-4 text-left">
                                <span class="block text-sm font-bold text-slate-900">{{ $row['member']->full_name }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $row['member']->student_code }}</span>
                            </td>
                            @foreach($row['session_statuses'] as $st)
                                @php($badge = $sessionBadge[$st] ?? $sessionBadge['pending'])
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold ring-1 ring-inset {{ $badge[1] }}">{{ $badge[0] }}</span>
                                </td>
                            @endforeach
                            <td class="px-4 py-4 text-center">
                                <select wire:model.live="draftStatuses.{{ $memberId }}" class="w-full min-w-[120px] cursor-pointer rounded-xl border-2 px-3 py-2 text-sm font-bold outline-none transition {{ $finalBadge[$row['status']] ?? 'border-slate-200 bg-white text-slate-700' }}">
                                    <option value="present">Có mặt</option>
                                    <option value="late">Đi muộn</option>
                                    <option value="partial">Vắng giữa giờ</option>
                                    <option value="early_leave">Về sớm</option>
                                    <option value="absent">Vắng</option>
                                    <option value="excused">Có phép</option>
                                </select>
                                @if($row['edited'])
                                    <span class="mt-1 block text-[10px] font-semibold text-amber-600">Đã sửa (gốc: {{ $row['auto_label'] }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-sm font-black {{ $row['deduction'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $row['deduction'] > 0 ? '-'.rtrim(rtrim(number_format($row['deduction'], 1), '0'), '.') : '0' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-left">
                                <input type="text" wire:model.blur="draftNotes.{{ $memberId }}" placeholder="Ghi chú..." class="w-full min-w-[160px] rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $sessions->count() }}" class="px-6 py-14 text-center text-sm text-slate-500">Lớp chưa có sinh viên để tổng kết.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
