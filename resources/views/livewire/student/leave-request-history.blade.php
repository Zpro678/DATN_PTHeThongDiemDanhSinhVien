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

    <section class="rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm w-full">
        <div class="overflow-x-auto rounded-[2rem]">
            <table class="w-full min-w-[1080px] border-collapse text-left">
                <thead class="bg-surface-container-lowest text-[13px] font-bold uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="pl-8 pr-6 py-4">Ngày gửi</th>
                        <th class="px-6 py-4">Lớp học</th>
                        <th class="px-6 py-4 whitespace-nowrap">Buổi xin nghỉ</th>
                        <th class="px-6 py-4 whitespace-nowrap">Trạng thái</th>
                        <th class="px-6 py-4 min-w-[250px]">Lý do</th>
                        <th class="pr-8 pl-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse ($requests as $request)
                        @php($meta = $statusMeta[$request->status] ?? $statusMeta['pending'])
                        <tr class="transition-all duration-200 hover:bg-surface-container-lowest/80 hover:shadow-sm group">
                            <td class="whitespace-nowrap pl-8 pr-6 py-4 font-medium text-sm text-on-surface">
                                {{ $request->created_at?->format('d/m/Y H:i') ?? '--/--/----' }}
                            </td>
                            <td class="px-6 py-4 min-w-[200px]">
                                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ $request->classSession->courseClass->name ?? '' }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-on-surface-variant">
                                    <span>{{ $request->classSession->courseClass->code ?? '' }}</span>
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="rounded-lg border border-outline-variant/20 bg-surface-container-lowest px-2.5 py-1 text-xs font-bold tracking-wider text-on-surface-variant">
                                    {{ $request->classSession->name ?? 'Không xác định' }}
                                </span>
                                <span class="ml-1.5 text-[11px] text-on-surface-variant">{{ $request->classSession->date?->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider whitespace-nowrap {{ $meta['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                    {{ $meta['label'] }}
                                </span>
                                @if($request->status === 'rejected' && $request->rejected_reason)
                                    <div class="mt-1.5 text-[11px] text-rose-600 font-medium whitespace-normal max-w-[200px]">Lý do: {{ $request->rejected_reason }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 min-w-[250px] text-on-surface-variant">
                                <p class="text-[13px]" title="{{ $request->reason }}">{{ $request->reason }}</p>
                                @if(!empty($request->proof_image))
                                    <div class="mt-1.5 flex flex-wrap gap-1.5">
                                        @foreach($request->proof_image as $img)
                                            <a href="{{ asset('storage/'.$img) }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-primary hover:underline bg-primary/10 px-1.5 py-0.5 rounded">
                                                <x-user.icon name="image" :size="10" />Ảnh {{ $loop->iteration }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="pr-8 pl-6 py-4 text-center">
                                <div x-data="{ open: false }" class="relative inline-block text-left" @click.away="open = false">
                                    <button @click="open = !open" type="button" class="flex items-center justify-center h-8 w-8 rounded-full text-on-surface-variant hover:bg-surface-container-high transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-1 mx-auto">
                                        <x-user.icon name="more-vertical" :size="18" />
                                    </button>

                                    <div x-show="open" 
                                         x-transition:enter="transition ease-out duration-100" 
                                         x-transition:enter-start="transform opacity-0 scale-95" 
                                         x-transition:enter-end="transform opacity-100 scale-100" 
                                         x-transition:leave="transition ease-in duration-75" 
                                         x-transition:leave-start="transform opacity-100 scale-100" 
                                         x-transition:leave-end="transform opacity-0 scale-95" 
                                         class="absolute right-1/2 translate-x-1/2 sm:translate-x-0 sm:right-0 z-50 mt-1 w-36 origin-top-right sm:origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;" x-cloak>
                                        <div class="py-1">
                                            <a href="#" class="group flex items-center px-3 py-2 text-[13px] font-semibold text-gray-700 hover:bg-primary/10 hover:text-primary transition-colors">
                                                <x-user.icon name="eye" :size="14" class="mr-2 text-gray-400 group-hover:text-primary" />
                                                Xem chi tiết
                                            </a>
                                            @if($request->status === 'pending')
                                            <a href="#" class="group flex items-center px-3 py-2 text-[13px] font-semibold text-gray-700 hover:bg-primary/10 hover:text-primary transition-colors">
                                                <x-user.icon name="edit-2" :size="14" class="mr-2 text-gray-400 group-hover:text-primary" />
                                                Chỉnh sửa
                                            </a>
                                            <button type="button" wire:click="deleteRequest({{ $request->id }})" wire:confirm="Bạn có chắc chắn muốn xóa đơn xin phép này không?" class="group flex w-full items-center px-3 py-2 text-[13px] font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                                <x-user.icon name="trash-2" :size="14" class="mr-2 text-rose-500" />
                                                Xóa đơn
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <div class="flex flex-col items-center justify-center text-on-surface-variant">
                                    <div class="mb-4 rounded-full bg-surface-container p-4 text-outline">
                                        <x-user.icon name="file-text" :size="32" />
                                    </div>
                                    <p class="text-base font-bold text-on-surface">Chưa có đơn xin nghỉ phép nào</p>
                                    <p class="mt-1 text-sm">Bạn chưa từng gửi đơn xin nghỉ phép nào lên hệ thống.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
