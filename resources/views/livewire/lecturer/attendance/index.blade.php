<div class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div><h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900"><x-user.icon name="calendar-check" class="text-primary" />Quản lý điểm danh</h1><p class="mt-2 text-sm text-slate-500">Tạo và theo dõi các buổi điểm danh của lớp học.</p></div>
        <a href="{{ route('lecturer.attendance.create') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-sm hover:shadow-lg"><x-user.icon name="calendar-plus" :size="18" />Tạo buổi điểm danh</a>
    </section>

    @if(session('status'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif

    <section class="grid gap-5 md:grid-cols-2">
        <a href="{{ route('lecturer.attendance.manual.create') }}" class="group overflow-hidden rounded-2xl border-2 border-amber-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-xl"><div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><x-user.icon name="check-square" /></div><h2 class="text-xl font-extrabold text-slate-900">Điểm danh thủ công</h2><p class="mt-2 text-sm text-slate-500">Tạo phiên và đánh dấu có mặt, muộn, vắng, có phép trực tiếp trên danh sách sinh viên.</p></a>
        <a href="{{ route('lecturer.attendance.qr.create') }}" class="group overflow-hidden rounded-2xl border-2 border-blue-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-400 hover:shadow-xl"><div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700"><x-user.icon name="qr-code" /></div><h2 class="text-xl font-extrabold text-slate-900">Điểm danh QR</h2><p class="mt-2 text-sm text-slate-500">Sinh mã QR/token, cấu hình thời hạn và theo dõi sinh viên check-in theo thời gian thực.</p></a>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-xl font-extrabold text-slate-900">Phiên điểm danh gần đây ({{ number_format($sessions->total()) }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-slate-50 text-sm font-extrabold uppercase tracking-wider text-slate-800 whitespace-nowrap">
                    <tr>
                        <th class="w-[25%] px-6 py-4 text-left">Buổi học</th>
                        <th class="w-[30%] pl-10 pr-4 py-4 text-left">Lớp</th>
                        <th class="w-[10%] px-4 py-4 text-center">Ngày</th>
                        <th class="w-[10%] px-4 py-4 text-center">Đã ghi nhận</th>
                        <th class="w-[10%] px-4 py-4 text-center">Vắng</th>
                        <th class="w-[15%] px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sessions as $session)
                        <tr class="transition-colors hover:bg-blue-50/30 group">
                            <td class="px-6 py-4 text-left">
                                <div class="flex items-center gap-3">
                                    <span class="block text-sm font-bold text-slate-900 group-hover:text-blue-700 transition-colors">{{ $session->name }}</span>
                                    <span class="text-[11px] font-medium text-slate-400">{{ $session->created_at->format('H:i') }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">{{ $session->qr_token ? 'Điểm danh QR' : 'Thủ công' }}</span>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10' }}">{{ $session->status === 'active' ? 'Đang mở' : 'Đã chốt' }}</span>
                                </div>
                            </td>
                            <td class="pl-10 pr-4 py-4 text-sm text-slate-600 text-left">
                                <span class="font-bold text-slate-700">{{ $session->courseClass->code }}</span>
                                <span class="block text-xs text-slate-500 mt-0.5">{{ $session->courseClass->name }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-slate-700">{{ $session->date->format('d/m/Y') }}</span>
                                <span class="block text-xs font-medium text-slate-500 mt-0.5">
                                    {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-emerald-50 px-2 text-sm font-black text-emerald-600 ring-1 ring-inset ring-emerald-600/20">{{ $session->present_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-rose-50 px-2 text-sm font-black text-rose-600 ring-1 ring-inset ring-rose-600/20">{{ $session->absent_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session) }}" class="whitespace-nowrap rounded-xl bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700 shadow-sm ring-1 ring-inset ring-blue-100 transition-all hover:bg-blue-100 hover:shadow hover:ring-blue-200">Mở phiên</a>
                                    @if($session->status !== 'closed')
                                        <button type="button" wire:click="closeSession({{ $session->id }})" wire:confirm="Chốt phiên điểm danh này?" class="whitespace-nowrap rounded-xl bg-amber-50 px-4 py-2 text-xs font-bold text-amber-700 shadow-sm ring-1 ring-inset ring-amber-100 transition-all hover:bg-amber-100 hover:shadow hover:ring-amber-200">Chốt</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Chưa có phiên điểm danh nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())<div class="border-t border-slate-100 px-6 py-4">{{ $sessions->links() }}</div>@endif
    </section>
</div>
