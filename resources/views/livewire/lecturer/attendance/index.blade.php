<div x-data="{ 
    showTypePopup: false, 
    cloneSessionId: null, 
    cloneClassId: null, 
    cloneDate: null,
    cloneClassName: '',
    cloneSessionName: '',
    cloneStartLesson: 1,
    cloneEndLesson: 3,
    cloneFormattedDate: ''
}" class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div><h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900"><x-user.icon name="calendar-check" class="text-primary" />Quản lý điểm danh</h1><p class="mt-2 text-sm text-slate-500">Tạo và theo dõi các buổi điểm danh của lớp học.</p></div>
        <a href="{{ route('lecturer.attendance.create') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-sm hover:shadow-lg"><x-user.icon name="calendar-plus" :size="18" />Tạo buổi điểm danh</a>
    </section>

    @if(session('status'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif

    <section class="grid gap-5 md:grid-cols-2">
        <a href="{{ route('lecturer.attendance.create') }}" class="group overflow-hidden rounded-2xl border-2 border-amber-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-amber-400 hover:shadow-xl"><div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"><x-user.icon name="check-square" /></div><h2 class="text-xl font-extrabold text-slate-900">Điểm danh thủ công</h2><p class="mt-2 text-sm text-slate-500">Tạo phiên và đánh dấu có mặt, muộn, vắng, có phép trực tiếp trên danh sách học viên.</p></a>
        <a href="{{ route('lecturer.attendance.qr.create') }}" class="group overflow-hidden rounded-2xl border-2 border-blue-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-400 hover:shadow-xl"><div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700"><x-user.icon name="qr-code" /></div><h2 class="text-xl font-extrabold text-slate-900">Điểm danh QR</h2><p class="mt-2 text-sm text-slate-500">Sinh mã QR/token, cấu hình thời hạn và theo dõi học viên check-in theo thời gian thực.</p></a>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-xl font-extrabold text-slate-900">Buổi điểm danh gần đây ({{ number_format($sessions->total()) }})</h2>
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
                                <span class="block text-sm font-bold text-slate-900 group-hover:text-blue-700 transition-colors">
                                    {{ $session->name }}
                                </span>
                                @if($session->start_lesson && $session->end_lesson)
                                    <span class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500">
                                        <x-user.icon name="clock" :size="13" />
                                        Tiết {{ $session->start_lesson }} - {{ $session->end_lesson }} ({{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }})
                                    </span>
                                @endif
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $session->qr_token ? 'bg-indigo-50 text-indigo-700 ring-indigo-600/20' : 'bg-purple-50 text-purple-700 ring-purple-600/20' }}">{{ $session->qr_token ? 'Điểm danh QR' : 'Thủ công' }}</span>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-sky-50 text-sky-700 ring-sky-600/20' }}">{{ $session->status === 'active' ? 'Đang mở' : 'Đã chốt' }}</span>
                                </div>
                            </td>
                            <td class="pl-10 pr-4 py-4 text-sm text-slate-600 text-left">
                                <span class="font-bold text-slate-700">{{ $session->courseClass->code }}</span>
                                <span class="block text-xs text-slate-500 mt-0.5">{{ $session->courseClass->name }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="block text-sm font-bold text-slate-700">{{ $session->date->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-emerald-50 px-2 text-sm font-black text-emerald-600 ring-1 ring-inset ring-emerald-600/20">{{ $session->present_count }}</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-rose-50 px-2 text-sm font-black text-rose-600 ring-1 ring-inset ring-rose-600/20">{{ $session->absent_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($session->status === 'active')
                                            <a href="{{ $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session) }}" class="whitespace-nowrap rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm ring-1 ring-inset ring-blue-600 transition-all hover:bg-blue-700 hover:shadow">Tiếp tục điểm danh</a>
                                        @else
                                            <a href="{{ route('lecturer.attendance.group.detail', ['courseClass' => $session->class_id, 'groupKey' => $session->date->format('Y-m-d') . '_' . $session->start_time . '_' . $session->end_time]) }}" class="whitespace-nowrap rounded-xl bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 transition-all hover:bg-slate-50 hover:text-slate-900">Xem phiên</a>
                                            <button type="button" @click="showTypePopup = true; cloneSessionId = {{ $session->id }}; cloneClassId = {{ $session->class_id }}; cloneDate = '{{ $session->date->format('Y-m-d') }}'; cloneClassName = '{{ addslashes($session->courseClass->code . ' - ' . $session->courseClass->name) }}'; cloneSessionName = '{{ addslashes($session->name) }}'; cloneStartLesson = {{ $session->start_lesson ?? 1 }}; cloneEndLesson = {{ $session->end_lesson ?? 3 }}; cloneFormattedDate = '{{ $session->date->format('d/m/Y') }}';" class="whitespace-nowrap rounded-xl bg-amber-100 px-4 py-2 text-xs font-bold text-amber-700 shadow-sm ring-1 ring-inset ring-amber-200 transition-all hover:bg-amber-200 hover:text-amber-800 hover:shadow">Thêm phiên</button>
                                        @endif
                                    </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Chưa có buổi điểm danh nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sessions->hasPages())<div class="border-t border-slate-100 px-6 py-4">{{ $sessions->links() }}</div>@endif
    </section>

    <!-- Create Session Modal -->
    <div x-cloak x-show="showTypePopup" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="showTypePopup" x-transition.opacity.duration.200ms class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" @click="showTypePopup = false" aria-label="Đóng"></div>
        <div x-show="showTypePopup" x-transition.scale.origin.center.duration.200ms class="relative w-full max-w-[380px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <button type="button" @click="showTypePopup = false" class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <x-user.icon name="x" :size="20" />
            </button>
            <div class="p-5 pb-2 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                    <x-user.icon name="plus-circle" :size="28" />
                </div>
                <h3 class="mb-3 text-lg font-extrabold text-slate-900">Tạo phiên điểm danh mới</h3>
                <div class="text-sm text-slate-700">
                    <div class="flex flex-col gap-2.5 text-left bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm">
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-2.5">
                            <span class="font-medium text-slate-500">Lớp:</span>
                            <span class="font-bold text-slate-900 text-right max-w-[65%] truncate" :title="cloneClassName" x-text="cloneClassName"></span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-2.5">
                            <span class="font-medium text-slate-500">Buổi:</span>
                            <span class="font-bold text-slate-900" x-text="cloneSessionName"></span>
                        </div>
                        <div class="flex justify-between items-center border-b border-slate-200/60 pb-2.5">
                            <span class="font-medium text-slate-500">Tiết:</span>
                            <span class="font-bold text-slate-900" x-text="cloneStartLesson + ' đến ' + cloneEndLesson"></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-slate-500">Ngày:</span>
                            <span class="font-bold text-slate-900" x-text="cloneFormattedDate"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-3 px-5 pb-5 pt-3 sm:flex-row">
                <button type="button" @click="$wire.cloneAndStartManual(cloneSessionId)" class="inline-flex justify-center items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 sm:flex-1">
                    <x-user.icon name="check-square" :size="18" class="text-emerald-600" />
                    Thủ công
                </button>
                <a :href="'{{ route('lecturer.attendance.qr.create') }}?class_id=' + cloneClassId + '&date=' + cloneDate + '&clone_session=' + cloneSessionId" class="inline-flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700 sm:flex-1">
                    <x-user.icon name="qr-code" :size="18" />
                    QR/Link
                </a>
            </div>
        </div>
    </div>
</div>
