<div class="mx-auto max-w-[1300px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 md:flex-row md:items-center px-1">
        <h1 class="flex items-center gap-3 text-2xl font-extrabold tracking-tight text-slate-900">
            <x-user.icon name="calendar-check" class="text-blue-600" :size="28" />
            {{ $courseClass->code }} - {{ $courseClass->name }} ({{ number_format($sessions->total()) }})
        </h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('lecturer.classes.show', $courseClass->id) }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 py-2.5 text-[15px] font-medium text-slate-700 transition-colors hover:bg-slate-200">
                <x-user.icon name="arrow-left" :size="18" />
                Trở về
            </a>
        </div>
    </section>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <section class="mt-0">
        <h2 class="mb-2 text-sm font-bold uppercase tracking-wider text-slate-500">Lịch sử điểm danh</h2>
        <div class="flex flex-col gap-2">
            @forelse($sessions as $session)
                @php
                    $href = $session->qr_token ? route('lecturer.attendance.qr.session', $session) : route('lecturer.attendance.manual.session', $session);
                @endphp
                <div class="group relative flex flex-col gap-2 rounded-[14px] border border-slate-200 bg-white p-2 sm:p-2.5 shadow-sm transition hover:border-blue-300 hover:shadow-md hover:bg-slate-50 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ $href }}" class="absolute inset-0 z-0 rounded-[14px]"></a>
                    
                    <div class="relative z-10 flex items-center gap-3 pointer-events-none">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] {{ $session->qr_token ? 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white' : 'bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white' }} transition-colors">
                            <x-user.icon name="{{ $session->qr_token ? 'qr-code' : 'check-square' }}" :size="20" />
                        </div>
                        <div>
                            <h3 class="text-[15px] font-bold text-slate-900 group-hover:text-blue-700 transition-colors leading-tight">{{ $session->name }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 sm:gap-2 text-[13px] text-slate-500">
                                <span class="flex items-center gap-1"><x-user.icon name="calendar" :size="13" /> {{ $session->date->format('d/m/Y') }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span class="flex items-center gap-1"><x-user.icon name="clock" :size="13" /> {{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span class="inline-flex items-center rounded-md bg-slate-100 px-1.5 py-0.5 text-[11px] font-bold text-slate-600">{{ $session->qr_token ? 'Điểm danh QR' : 'Thủ công' }}</span>
                                <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-bold {{ $session->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-600' }}">{{ $session->status === 'active' ? 'Đang mở' : 'Đã chốt' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="relative z-10 flex flex-col gap-1.5 sm:gap-2 sm:items-end">
                        <div class="flex items-center gap-4 pointer-events-none">
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 leading-none">Có mặt</span>
                                <span class="mt-1 text-[16px] font-black text-emerald-600 leading-none">{{ $session->present_count }}</span>
                            </div>
                            <div class="h-5 w-px bg-slate-200"></div>
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 leading-none">Vắng</span>
                                <span class="mt-1 text-[16px] font-black text-rose-600 leading-none">{{ $session->absent_count }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-0.5 pointer-events-auto">
                            <a href="{{ $href }}" class="rounded-[8px] bg-blue-50 px-3 py-1 text-[12px] font-bold text-blue-700 shadow-sm transition-colors hover:bg-blue-100">Mở phiên</a>
                            @if($session->status !== 'closed')
                                <button type="button" wire:click="closeSession({{ $session->id }})" wire:confirm="Chốt phiên điểm danh này?" class="rounded-[8px] bg-amber-50 px-3 py-1 text-[12px] font-bold text-amber-700 shadow-sm transition-colors hover:bg-amber-100">Chốt</button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <x-user.icon name="inbox" :size="32" />
                    </div>
                    <p class="text-slate-500">Chưa có phiên điểm danh nào cho lớp học này.</p>
                </div>
            @endforelse
        </div>
        @if($sessions->hasPages() || $sessions->total() > 5)
            <div class="mt-6">
                {{ $sessions->links() }}
            </div>
        @endif
    </section>
</div>
