<div class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">

    <section class="flex flex-col justify-between gap-4 rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
                <x-user.icon name="calendar-check" class="text-primary" />{{ $meeting->name }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                <span class="font-bold text-slate-700">{{ $meeting->courseClass->join_key }}</span> · {{ $meeting->courseClass->name }} ·
                {{ $meeting->date->format('d/m/Y') }}@if($meeting->start_time) · {{ \Carbon\Carbon::parse($meeting->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($meeting->end_time)->format('H:i') }}@endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($meeting->canAddSession())
                <button type="button" wire:click="addManualSession" class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <x-user.icon name="check-square" :size="18" />Thêm phiên thủ công
                </button>
                <a href="{{ route('lecturer.attendance.qr.create') }}?meeting={{ $meeting->id }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <x-user.icon name="qr-code" :size="18" />Thêm phiên QR
                </a>
            @else
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700 ring-1 ring-inset ring-sky-600/20">
                    <x-user.icon name="lock" :size="15" />Buổi đã kết thúc
                </span>
            @endif
            <a href="{{ route('lecturer.attendance.meeting.summary', ['ma_user' => auth()->id(), 'meeting' => $meeting->id]) }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                <x-user.icon name="clipboard-check" :size="18" />Tổng kết buổi
            </a>
        </div>
    </section>

    @if(session('status'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
    @if(session('error'))<div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">{{ session('error') }}</div>@endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-extrabold text-slate-900">Danh sách phiên điểm danh ({{ $sessions->count() }})</h2>
            <p class="mt-1 text-sm text-slate-500">Mỗi phiên là một lần điểm danh của buổi học này. Bấm "Xem chi tiết" để xem danh sách sinh viên.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[940px] text-left">
                <thead class="bg-slate-50 text-sm font-extrabold uppercase tracking-wider text-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="px-6 py-4 text-left">Phiên</th>
                        <th class="px-4 py-4 text-center">Phương thức</th>
                        <th class="px-4 py-4 text-center">Trạng thái</th>
                        <th class="px-4 py-4 text-center">Có mặt</th>
                        <th class="px-4 py-4 text-center">Đi muộn</th>
                        <th class="px-4 py-4 text-center">Vắng</th>
                        <th class="px-4 py-4 text-center">Có phép</th>
                        <th class="px-4 py-4 text-center">Chưa ĐD</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $index => $session)
                        <tr class="transition-colors hover:bg-blue-50/30">
                            <td class="px-6 py-4 text-left">
                                <span class="block text-sm font-bold text-slate-900">Lần {{ $index + 1 }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">Tạo lúc {{ $session->created_at->format('H:i d/m/Y') }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $session->qr_token ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 'bg-purple-50 text-purple-700 ring-purple-600/20' }}">{{ $session->qr_token ? 'Điểm danh QR' : 'Thủ công' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-sky-50 text-sky-700 ring-sky-600/20' }}">{{ $session->status === 'active' ? 'Đang mở' : 'Đã chốt' }}</span>
                            </td>
                            <td class="px-4 py-4 text-center"><span class="text-sm font-black text-emerald-600">{{ $session->present_count }}</span></td>
                            <td class="px-4 py-4 text-center"><span class="text-sm font-black text-amber-600">{{ $session->late_count }}</span></td>
                            <td class="px-4 py-4 text-center"><span class="text-sm font-black text-rose-600">{{ $session->absent_count }}</span></td>
                            <td class="px-4 py-4 text-center"><span class="text-sm font-black text-sky-600">{{ $session->excused_count }}</span></td>
                            <td class="px-4 py-4 text-center"><span class="text-sm font-black text-slate-400">{{ $session->pending_count }}</span></td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session) }}" class="whitespace-nowrap rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all hover:bg-slate-50 hover:text-slate-900">Xem chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-14 text-center text-sm text-slate-500">Buổi này chưa có phiên điểm danh nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-4 flex justify-start">
        <a href="{{ route('lecturer.attendance.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition hover:bg-slate-50 hover:text-slate-900">
            <x-user.icon name="arrow-left" :size="18" />Quay lại
        </a>
    </div>
</div>
