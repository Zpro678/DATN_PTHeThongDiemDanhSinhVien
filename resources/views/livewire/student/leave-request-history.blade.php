@php
    $statusMeta = [
        'pending' => ['label' => 'Đang chờ duyệt', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'dot' => 'bg-amber-500'],
        'approved' => ['label' => 'Đã duyệt', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60', 'dot' => 'bg-emerald-500'],
        'rejected' => ['label' => 'Bị từ chối', 'badge' => 'bg-rose-50 text-rose-700 border-rose-200/60', 'dot' => 'bg-rose-500'],
    ];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-center">
        <div>
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-primary">
                <x-user.icon name="history" :size="14" />
                Lịch sử
            </div>
            <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Lịch sử đơn xin nghỉ phép</h1>
        </div>
        <a href="{{ route('student.leave-requests.create') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-white transition-all hover:shadow-lg hover:bg-primary/90 active:scale-95 md:w-auto shrink-0">
            <x-user.icon name="plus" />
            Tạo đơn mới
        </a>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm w-full">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] border-collapse text-left">
                <thead class="bg-surface-container-lowest text-base font-bold uppercase tracking-wider text-on-surface">
                    <tr>
                        <th class="pl-[44px] pr-8 py-5">Ngày gửi</th>
                        <th class="px-8 py-5">Lớp học</th>
                        <th class="px-8 py-5">Buổi xin nghỉ</th>
                        <th class="px-8 py-5">Trạng thái</th>
                        <th class="px-8 py-5 max-w-[300px]">Lý do</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10 text-base">
                    @forelse ($requests as $request)
                        @php($meta = $statusMeta[$request->status] ?? $statusMeta['pending'])
                        <tr class="transition-all duration-200 hover:bg-surface-container-lowest/80 hover:shadow-sm group">
                            <td class="whitespace-nowrap pl-[44px] pr-8 py-5 font-medium text-[15px] text-on-surface">
                                {{ $request->created_at?->format('d/m/Y H:i') ?? '--/--/----' }}
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-base font-bold text-on-surface group-hover:text-primary transition-colors">{{ $request->classSession->courseClass->name ?? '' }}</p>
                                <p class="mt-1.5 flex items-center gap-2 text-sm font-medium text-on-surface-variant">
                                    <span>{{ $request->classSession->courseClass->code ?? '' }}</span>
                                </p>
                            </td>
                            <td class="px-8 py-5">
                                <span class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest px-3 py-1.5 text-[13px] font-bold tracking-wider text-on-surface-variant">
                                    {{ $request->classSession->name ?? 'Không xác định' }}
                                </span>
                                <span class="ml-2 text-xs text-on-surface-variant">{{ $request->classSession->date?->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center gap-1.5 rounded-xl border px-3 py-1.5 text-[13px] font-bold uppercase tracking-wider {{ $meta['badge'] }}">
                                    <span class="h-2 w-2 rounded-full {{ $meta['dot'] }}"></span>
                                    {{ $meta['label'] }}
                                </span>
                                @if($request->status === 'rejected' && $request->rejected_reason)
                                    <div class="mt-1.5 text-xs text-rose-600 font-medium">Lý do từ chối: {{ $request->rejected_reason }}</div>
                                @endif
                            </td>
                            <td class="px-8 py-5 max-w-[300px] text-on-surface-variant">
                                <p class="text-sm line-clamp-2" title="{{ $request->reason }}">{{ $request->reason }}</p>
                                @if(!empty($request->proof_image))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach($request->proof_image as $img)
                                            <a href="{{ asset('storage/'.$img) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline bg-primary/10 px-2 py-1 rounded-md">
                                                <x-user.icon name="image" :size="12" />Ảnh {{ $loop->iteration }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center text-on-surface-variant">
                                    <div class="mb-5 rounded-full bg-surface-container p-5 text-outline">
                                        <x-user.icon name="file-text" :size="40" />
                                    </div>
                                    <p class="text-lg font-bold text-on-surface">Chưa có đơn xin nghỉ phép nào</p>
                                    <p class="mt-2 text-base">Bạn chưa từng gửi đơn xin nghỉ phép nào lên hệ thống.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
