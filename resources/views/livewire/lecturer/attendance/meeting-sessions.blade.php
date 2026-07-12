<div class="w-full px-6 pt-6 sm:px-10 lg:px-16 sm:pt-8 min-h-screen space-y-6 pb-24"
    x-data x-init="window.listenRealtime && window.listenRealtime(@js($this->realtimeChannel()), () => $wire.$refresh(), 300)">

    <section class="relative flex flex-col justify-between gap-6 overflow-hidden rounded-3xl border border-indigo-100 bg-gradient-to-br from-indigo-50/80 via-white to-white p-6 shadow-sm md:flex-row md:items-center sm:p-8">
        <div class="absolute -right-12 -top-12 h-48 w-48 rounded-full bg-indigo-200/40 blur-3xl"></div>
        <div class="relative">
            <h1 class="flex items-center gap-3 text-3xl font-extrabold tracking-tight text-slate-900">
                <div class="grid h-12 w-12 shrink-0 place-items-center rounded-2xl bg-indigo-100 text-indigo-600 ring-1 ring-inset ring-indigo-200/50">
                    <x-user.icon name="calendar-check" :size="24" />
                </div>
                {{ $meeting->name }}
            </h1>
            <p class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 font-bold text-slate-700 ring-1 ring-inset ring-slate-200">{{ $meeting->courseClass->join_key }}</span>
                <span>·</span>
                <span class="font-medium">{{ $meeting->courseClass->name }}</span>
                <span>·</span>
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                    <x-user.icon name="calendar" :size="14" class="text-slate-400" />
                    {{ $meeting->date->format('d/m/Y') }}
                </span>
                @if($meeting->start_time)
                <span>·</span>
                <span class="inline-flex items-center gap-1.5 font-medium text-slate-600">
                    <x-user.icon name="clock" :size="14" class="text-slate-400" />
                    {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}
                </span>
                @endif
            </p>
        </div>
        <div class="relative flex flex-wrap items-center justify-end gap-3">
            @if($meeting->canAddSession())
                <button type="button" wire:click="addManualSession" class="group inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-xl hover:shadow-emerald-600/40">
                    <x-user.icon name="check-square" :size="18" class="transition-transform group-hover:scale-110" />Thêm thủ công
                </button>
                <button type="button" wire:click="$dispatch('open-quick-attendance-modal', { type: 'qr', meetingId: {{ $meeting->id }} })" class="group inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/30 transition-all duration-300 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-600/40">
                    <x-user.icon name="qr-code" :size="18" class="transition-transform group-hover:scale-110" />Thêm QR
                </button>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                    <x-user.icon name="lock" :size="15" />Buổi đã kết thúc
                </span>
            @endif
            <a href="{{ route('lecturer.attendance.meeting.summary', ['ma_user' => auth()->id(), 'meeting' => $meeting->id]) }}" class="group inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-900/20 transition-all duration-300 hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-900/30">
                <x-user.icon name="clipboard-check" :size="18" class="transition-transform group-hover:scale-110" />Tổng kết
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-extrabold text-slate-900">Danh sách phiên ({{ $sessions->count() }})</h2>
            <p class="mt-1 text-sm text-slate-500">Mỗi phiên là một lần điểm danh của buổi học này. Bấm "Xem chi tiết" để xem danh sách sinh viên.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[940px] text-left">
                <thead class="bg-slate-50/80 text-[13px] font-extrabold uppercase tracking-wider text-slate-900 whitespace-nowrap backdrop-blur-sm">
                    <tr>
                        <th class="px-6 py-4 text-left">Phiên</th>
                        <th class="px-4 py-4 text-center">Phương thức</th>
                        <th class="px-4 py-4 text-center">Trạng thái</th>
                        <th class="px-4 py-4 text-center">Có mặt</th>
                        <th class="px-4 py-4 text-center">Đi muộn</th>
                        <th class="px-4 py-4 text-center">Vắng</th>
                        <th class="px-4 py-4 text-center">Có phép</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $index => $session)
                        <tr class="transition-colors hover:bg-blue-50/30">
                            <td class="px-6 py-4 text-left">
                                <span class="block text-sm font-bold text-slate-900">Phiên {{ $sessions->count() - $index }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Tạo lúc {{ $session->created_at->format('H:i d/m/Y') }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $session->qr_token ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-purple-50 text-purple-700 ring-purple-600/20' }}">{{ $session->qr_token ? 'Điểm danh QR' : 'Thủ công' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold ring-1 ring-inset {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-sky-50 text-sky-700 ring-sky-600/20' }}">{{ $session->status === 'active' ? 'Đang mở' : 'Đã chốt' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex min-w-[36px] items-center justify-center rounded-lg bg-emerald-50 px-2.5 py-1.5 text-[13px] font-black text-emerald-700 ring-1 ring-inset ring-emerald-600/20">{{ $session->present_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex min-w-[36px] items-center justify-center rounded-lg bg-amber-50 px-2.5 py-1.5 text-[13px] font-black text-amber-700 ring-1 ring-inset ring-amber-600/20">{{ $session->late_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex min-w-[36px] items-center justify-center rounded-lg bg-rose-50 px-2.5 py-1.5 text-[13px] font-black text-rose-700 ring-1 ring-inset ring-rose-600/20">{{ $session->absent_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex min-w-[36px] items-center justify-center rounded-lg bg-sky-50 px-2.5 py-1.5 text-[13px] font-black text-sky-700 ring-1 ring-inset ring-sky-600/20">{{ $session->excused_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session) }}" class="whitespace-nowrap rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all hover:bg-slate-50 hover:text-slate-900">Xem chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center text-sm text-slate-500">Buổi này chưa có phiên điểm danh nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <livewire:lecturer.attendance.quick-attendance-modal />

</div>
