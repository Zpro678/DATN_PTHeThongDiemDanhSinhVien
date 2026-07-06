@php
    $statusMap = [
        'pending' => ['Chờ duyệt', 'bg-amber-50 text-amber-700 border-amber-200'],
        'approved' => ['Đã duyệt', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'rejected' => ['Bị từ chối', 'bg-red-50 text-red-700 border-red-200'],
    ];
    [$statusLabel, $statusClass] = $statusMap[$leaveRequest->status] ?? [$leaveRequest->status, 'bg-slate-100 text-slate-700 border-slate-200'];
@endphp

<div class="mx-auto max-w-[1400px] space-y-4 p-4 pt-4 sm:px-8 sm:pb-8 sm:pt-4 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                <x-user.icon name="file-text" :size="14" />
                Đơn xin phép
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Chi tiết đơn xin nghỉ</h1>
            <p class="mt-1 text-sm text-slate-500">Mã đơn #{{ $leaveRequest->id }}</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($leaveRequest->status === 'pending')
                <a href="{{ route('student.leave-requests.edit', $leaveRequest) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white transition-all hover:shadow-lg hover:bg-primary/90 active:scale-95">
                    <x-user.icon name="edit" :size="16" />
                    Chỉnh sửa
                </a>
            @endif
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-4 py-2.5 text-sm font-bold text-on-surface transition-all hover:bg-surface-container-low hover:shadow-sm active:scale-95">
                <x-user.icon name="arrow-left" :size="16" />
                Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 flex items-center gap-3 shadow-sm">
            <x-user.icon name="check-circle" class="text-emerald-500" />
            {{ session('success') }}
        </div>
    @endif

    <section class="grid gap-6 lg:grid-cols-[1fr_1.5fr]">
        <div class="flex h-full flex-col gap-6">
            <div class="rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
                    <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Thông tin chung</h2>
                    <span class="rounded-full border px-3 py-1 text-xs font-bold whitespace-nowrap {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <dl class="space-y-5 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase text-slate-400 mb-1">Lớp học</dt>
                        <dd class="font-bold text-slate-800">{{ $leaveRequest->classMember->courseClass->join_key }} - {{ $leaveRequest->classMember->courseClass->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-slate-400 mb-1">Buổi xin nghỉ</dt>
                        <dd class="font-bold text-slate-800 flex items-center gap-1.5">
                            <x-user.icon name="calendar" :size="16" class="text-slate-400" />
                            {{ $leaveRequest->classMeeting->name }} · {{ $leaveRequest->classMeeting->date->format('d/m/Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-slate-400 mb-1">Ngày gửi</dt>
                        <dd class="font-semibold text-slate-700">{{ $leaveRequest->created_at?->format('H:i d/m/Y') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex flex-1 flex-col rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-4">Trạng thái xét duyệt</h2>
                @if($leaveRequest->reviewer)
                    <p class="text-sm text-slate-600 mb-2">Được xử lý bởi <strong class="text-slate-900">{{ $leaveRequest->reviewer->name }}</strong> lúc {{ $leaveRequest->reviewed_at?->format('H:i d/m/Y') }}.</p>
                @else
                    <div class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 text-amber-700 text-sm border border-amber-200/50">
                        <x-user.icon name="loader" class="shrink-0" />
                        Đơn đang chờ giảng viên duyệt.
                    </div>
                @endif 

                @if($leaveRequest->rejected_reason)
                    <div class="mt-4 rounded-xl bg-red-50 p-4 text-sm text-red-700 border border-red-200/50">
                        <div class="flex items-start gap-2">
                            <x-user.icon name="alert-triangle" class="shrink-0 text-red-500" />
                            <div>
                                <strong class="block mb-1">Lý do từ chối:</strong> 
                                {{ $leaveRequest->rejected_reason }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-outline-variant/10 bg-white p-6 shadow-sm h-full flex flex-col">
                <h2 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-4">Nội dung đơn xin nghỉ</h2>
                <div class="bg-surface-container-lowest p-5 rounded-xl border border-outline-variant/20 shadow-inner flex-1">
                    <p class="whitespace-pre-line text-base leading-relaxed text-slate-700">{{ $leaveRequest->reason }}</p>
                </div>
                
                @if(!empty($leaveRequest->proof_image))
                    <div class="mt-6 pt-6">
                        <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-500">Minh chứng đính kèm ({{ count($leaveRequest->proof_image) }})</h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                            @foreach($leaveRequest->proof_image as $img)
                                <a href="{{ route('leave-requests.proof', ['leaveRequest' => $leaveRequest->id, 'filename' => basename($img)]) }}" target="_blank" class="group block aspect-square overflow-hidden rounded-xl border border-slate-200 shadow-sm relative bg-slate-50">
                                    @if(Str::endsWith(strtolower($img), '.pdf'))
                                        <div class="flex h-full w-full flex-col items-center justify-center bg-red-50 text-red-500">
                                            <x-user.icon name="file-text" :size="32" />
                                            <span class="mt-2 text-[10px] font-bold uppercase">PDF</span>
                                        </div>
                                    @else
                                        <img src="{{ route('leave-requests.proof', ['leaveRequest' => $leaveRequest->id, 'filename' => basename($img)]) }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110" alt="Minh chứng">
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                        <div class="bg-white/20 backdrop-blur p-2 rounded-full">
                                            <x-user.icon name="external-link" class="text-white" :size="20" />
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
