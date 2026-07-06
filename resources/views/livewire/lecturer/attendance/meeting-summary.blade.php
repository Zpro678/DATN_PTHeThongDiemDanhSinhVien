@php
    $sessionBadge = [
        'present' => ['Có mặt', 'text-emerald-600'],
        'late'    => ['Đi muộn', 'text-amber-600'],
        'excused' => ['Có phép', 'text-sky-600'],
        'absent'  => ['Vắng', 'text-rose-600'],
        'invalid' => ['Lỗi', 'text-rose-600'],
        'pending' => ['—', 'text-slate-400'],
    ];
    $finalBadge = [
        'present'     => 'border-emerald-300 bg-emerald-50 text-emerald-700',
        'late'        => 'border-amber-300 bg-amber-50 text-amber-700',
        'excused'     => 'border-sky-300 bg-sky-50 text-sky-700',
        'absent'      => 'border-rose-300 bg-rose-50 text-rose-700',
    ];
@endphp

<div class="w-full px-6 pt-6 sm:px-10 lg:px-16 sm:pt-8 min-h-screen space-y-6 pb-24">

    <section class="flex flex-col justify-between gap-6 md:flex-row md:items-start">
        <div>
            <h1 class="flex items-center flex-wrap gap-3 text-3xl font-extrabold tracking-tight text-slate-900">
                Tổng kết: {{ $meeting->name }}
                
                <div x-data="{ showRules: false }" class="relative ml-1 inline-flex -mt-2">
                    <button @click="showRules = !showRules" type="button" class="group flex items-center justify-center text-blue-500 transition-all hover:text-blue-700" title="Quy tắc tổng kết">
                        <x-user.icon name="info" :size="18" class="transition-transform group-hover:scale-110" />
                    </button>
                    
                    <div x-show="showRules" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         @click.away="showRules = false"
                         style="display: none;"
                         class="absolute left-0 top-12 z-50 w-[400px] rounded-[20px] border border-slate-200 bg-white p-6 text-[15px] font-normal tracking-normal leading-relaxed text-slate-600 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)]">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-base font-bold text-slate-800">Quy tắc tổng kết tự động</h3>
                            <button @click="showRules = false" class="text-slate-400 hover:text-slate-600"><x-user.icon name="x" :size="18" /></button>
                        </div>
                        <ul class="mt-3 space-y-2">
                            <li><strong class="text-slate-700">Vắng:</strong> Nếu phiên cuối vắng mặt.</li>
                            <li><strong class="text-slate-700">Đi muộn:</strong> Nếu phiên cuối có mặt, nhưng phiên đầu vắng hoặc có ít nhất 1 phiên đi muộn.</li>
                            <li><strong class="text-slate-700">Có mặt:</strong> Nếu phiên cuối có mặt, phiên đầu có mặt và không có phiên đi muộn.</li>
                        </ul>
                    </div>
                </div>
            </h1>
            <div class="mt-4 flex flex-col gap-2">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[13px] text-slate-900 font-medium">
                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 font-bold text-slate-700">{{ $meeting->courseClass->join_key }}</span>
                    <span class="text-slate-300">•</span>
                    <span>{{ $meeting->courseClass->name }}</span>
                </div>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-[13px] text-slate-900 font-medium">
                    <span class="inline-flex items-center gap-1.5">
                        <x-user.icon name="calendar" :size="14" class="text-slate-400" />
                        {{ $meeting->date->format('d/m/Y') }}
                    </span>
                    @if($meeting->end_time)
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-user.icon name="clock" :size="14" class="text-slate-400" />
                        Kết thúc {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('lecturer.attendance.meeting.sessions', $meeting) }}" wire:navigate class="group inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 text-sm font-bold text-indigo-700 transition-all hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-900 shadow-sm">
                <x-user.icon name="history" :size="16" class="text-indigo-500 transition-transform group-hover:scale-110" />
                <span class="leading-none">Xem phiên</span>
            </a>
            @if(!$isLocked)
                <button type="button" x-data @click="$dispatch('open-modal', 'confirm-recompute')" class="group inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900">
                    <x-user.icon name="refresh-cw" :size="16" class="transition-transform group-hover:rotate-180" />
                    <span class="leading-none">Reset</span>
                </button>
            @endif
            <x-user.export-button action="exportExcel" label="Xuất file" :can="$canExportExcel" />
            @if($isLocked)
                <button type="button" wire:click="unlock" class="group inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#059669] px-5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md">
                    <x-user.icon name="edit" :size="16" class="transition-transform group-hover:scale-110" />
                    <span class="leading-none">Chỉnh sửa</span>
                </button>
            @else
                <button type="button" wire:click="save" class="group inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-[#059669] px-5 text-sm font-bold text-white shadow-sm transition-all hover:bg-emerald-700 hover:shadow-md">
                    <x-user.icon name="save" :size="16" class="transition-transform group-hover:scale-110" />
                    <span class="leading-none">Lưu tổng kết</span>
                </button>
            @endif
        </div>
    </section>


    @if(session('upgrade_required'))<div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-3 text-sm font-semibold text-amber-700">{{ session('upgrade_required') }}</div>@endif

    {{-- Thẻ thống kê --}}
    <section class="grid grid-cols-2 gap-4 md:grid-cols-5">
        <div class="rounded-[20px] border-2 border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-emerald-300 hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-500">
                <x-user.icon name="check-circle" :size="18" class="text-emerald-600" />
                <span class="text-[13px] font-black uppercase tracking-widest text-slate-500">Có mặt</span>
            </div>
            <p class="mt-3 text-[40px] leading-none font-black text-[#059669]">{{ $totals['present'] ?? 0 }}</p>
        </div>
        <div class="rounded-[20px] border-2 border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-rose-300 hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-500">
                <x-user.icon name="x-circle" :size="18" class="text-rose-600" />
                <span class="text-[13px] font-black uppercase tracking-widest text-slate-500">Vắng</span>
            </div>
            <p class="mt-3 text-[40px] leading-none font-black text-[#E11D48]">{{ $totals['absent'] ?? 0 }}</p>
        </div>
        <div class="rounded-[20px] border-2 border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-amber-300 hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-500">
                <x-user.icon name="clock" :size="18" class="text-amber-500" />
                <span class="text-[13px] font-black uppercase tracking-widest text-slate-500">Đi muộn</span>
            </div>
            <p class="mt-3 text-[40px] leading-none font-black text-[#F59E0B]">{{ $totals['late'] ?? 0 }}</p>
        </div>
        <div class="rounded-[20px] border-2 border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-blue-300 hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-500">
                <x-user.icon name="clipboard-check" :size="18" class="text-blue-600" />
                <span class="text-[13px] font-black uppercase tracking-widest text-slate-500">Có phép</span>
            </div>
            <p class="mt-3 text-[40px] leading-none font-black text-[#2563EB]">{{ $totals['excused'] ?? 0 }}</p>
        </div>
        <div class="rounded-[20px] border-2 border-slate-200 bg-white p-5 shadow-sm transition-all hover:border-slate-400 hover:shadow-md">
            <div class="flex items-center gap-2 text-slate-500">
                <x-user.icon name="minus-circle" :size="18" class="text-slate-600" />
                <span class="text-[13px] font-black uppercase tracking-widest text-slate-500">Tổng điểm trừ</span>
            </div>
            <p class="mt-3 text-[40px] leading-none font-black text-slate-800">-{{ rtrim(rtrim(number_format($totals['deduction'] ?? 0, 1), '0'), '.') }}</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" style="margin-top: 32px;">
        <div class="max-h-[800px] overflow-auto">
            <table class="w-full min-w-[900px] text-left relative">
                <thead class="sticky top-0 z-20 bg-slate-50 text-sm font-extrabold uppercase tracking-wider text-slate-800 whitespace-nowrap shadow-sm">
                    <tr>
                        <th class="pl-10 pr-6 py-4 text-left">Học viên</th>
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
                            <td class="pl-10 pr-6 py-4 text-left">
                                <span class="block text-sm font-bold text-slate-900">{{ $row['member']->full_name }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ $row['member']->student_code }}</span>
                            </td>
                            @foreach($row['session_statuses'] as $st)
                                @php($badge = $sessionBadge[$st] ?? $sessionBadge['pending'])
                                <td class="px-3 py-4 text-center">
                                    <span class="text-sm font-bold {{ $badge[1] }}">{{ $badge[0] }}</span>
                                </td>
                            @endforeach
                            <td class="px-4 py-4 text-center">
                                <select @disabled($isLocked) wire:model.live="draftStatuses.{{ $memberId }}" class="w-28 rounded-xl border-2 px-3 py-2 text-xs font-bold outline-none transition disabled:opacity-100 {{ $isLocked ? 'cursor-default' : 'cursor-pointer' }} {{ $finalBadge[$row['status']] ?? 'border-slate-200 bg-white text-slate-700' }}">
                                    <option value="present">Có mặt</option>
                                    <option value="late">Đi muộn</option>
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
                                <input type="text" wire:model.blur="draftNotes.{{ $memberId }}" @disabled($isLocked) placeholder="Ghi chú..." class="w-full min-w-[160px] rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-400 focus:bg-white disabled:opacity-100 disabled:bg-slate-50 disabled:text-slate-700" />
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

    <x-modal name="confirm-recompute" maxWidth="sm" focusable>
        <div class="p-6 relative">
            <button x-on:click="$dispatch('close')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-500 transition-colors bg-slate-50 hover:bg-slate-100 rounded-full p-1.5">
                <x-user.icon name="x" :size="18" stroke-width="2.5" />
            </button>

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <x-user.icon name="alert-triangle" :size="20" stroke-width="2" />
                </div>
                <h2 class="text-lg font-bold text-slate-900">
                    Xác nhận tính lại
                </h2>
            </div>

            <p class="mt-2 text-sm text-slate-600">
                Tính lại sẽ <span class="font-bold text-slate-900">bỏ mọi chỉnh sửa thủ công</span> và lấy lại theo dữ liệu điểm danh gốc. Bạn có chắc chắn muốn tiếp tục?
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Hủy bỏ
                </x-secondary-button>

                <x-primary-button wire:click="recompute" x-on:click="$dispatch('close')" class="!bg-rose-600 hover:!bg-rose-700">
                    Đồng ý Reset
                </x-primary-button>
            </div>
        </div>
    </x-modal>
</div>
