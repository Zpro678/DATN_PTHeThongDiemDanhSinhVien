@php
    $statusMeta = [
        'pending' => ['label' => 'Chờ duyệt', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/60', 'dot' => 'bg-amber-500'],
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
            <h1 class="text-2xl font-extrabold uppercase tracking-tight text-slate-900">Lịch sử đơn xin nghỉ phép ({{ $requests->total() }})</h1>
        </div>
        <a href="{{ route('student.leave-requests.create') }}" wire:navigate class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-white transition-all hover:shadow-lg hover:bg-primary/90 active:scale-95 md:w-auto shrink-0">
            <x-user.icon name="plus" />
            Tạo đơn mới
        </a>
    </section>

    <section class="rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm w-full">
        <div class="overflow-x-auto rounded-[2rem] min-h-[260px]">
            <table class="w-full min-w-[900px] border-collapse text-left">
                <thead class="bg-surface-container-lowest text-sm font-bold uppercase tracking-wider text-on-surface-variant">
                    <tr>
                        <th class="pl-6 pr-3 py-4 w-28 whitespace-nowrap">Ngày gửi</th>
                        <th class="px-4 py-4 whitespace-nowrap">Lớp học</th>
                        <th class="px-4 py-4 whitespace-nowrap">Buổi xin nghỉ</th>
                        <th class="px-4 py-4 whitespace-nowrap">Trạng thái</th>
                        <th class="px-4 py-4 min-w-[200px]">Lý do</th>
                        <th class="pr-6 pl-4 py-4 text-center whitespace-nowrap w-24">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/10">
                    @forelse ($requests as $request)
                        @php($meta = $statusMeta[$request->status] ?? $statusMeta['pending'])
                        <tr class="transition-all duration-200 hover:bg-surface-container-lowest/80 hover:shadow-sm group">
                            <td class="whitespace-nowrap pl-6 pr-3 py-4 w-28">
                                <p class="text-[14.5px] font-bold text-on-surface">{{ $request->created_at?->format('d/m/Y') ?? '--/--/----' }}</p>
                                <p class="text-[12.5px] font-medium text-on-surface-variant mt-0.5">{{ $request->created_at?->format('H:i') ?? '' }}</p>
                            </td>
                            <td class="px-4 py-4 min-w-[150px]">
                                <p class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ $request->classSession->courseClass->name ?? '' }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-xs font-medium text-on-surface-variant">
                                    <span>{{ $request->classSession->courseClass->code ?? '' }}</span>
                                </p>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="text-sm font-bold text-on-surface group-hover:text-primary transition-colors">
                                        {{ $request->classSession->name ?? 'Không xác định' }}
                                    </span>
                                    <span class="flex items-center gap-1 text-xs font-medium text-on-surface-variant">
                                        <x-user.icon name="calendar" :size="12" />
                                        {{ $request->classSession->date?->format('d/m/Y') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider whitespace-nowrap {{ $meta['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $meta['dot'] }}"></span>
                                    {{ $meta['label'] }}
                                </span>
                                @if($request->status === 'rejected' && $request->rejected_reason)
                                    <div class="mt-1.5 text-[11px] text-rose-600 font-medium whitespace-normal max-w-[200px]">Lý do: {{ $request->rejected_reason }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 min-w-[200px] max-w-[250px] text-on-surface-variant">
                                <p class="text-[13px] truncate" title="{{ $request->reason }}">{{ $request->reason }}</p>
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
                            <td class="pr-6 pl-4 py-4 text-center whitespace-nowrap w-24">
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
                                         class="absolute right-1/2 translate-x-1/2 sm:translate-x-0 sm:right-0 z-50 w-36 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none {{ ($loop->remaining <= 2 && $loop->count >= 4) ? 'bottom-full mb-2 origin-bottom-right sm:origin-bottom-right' : 'mt-1 origin-top-right sm:origin-top-right' }}" style="display: none;" x-cloak>
                                        <div class="py-1">
                                            <a href="{{ route('student.leave-requests.show', $request) }}" wire:navigate class="group flex items-center px-3 py-2 text-[13px] font-semibold text-gray-700 hover:bg-primary/10 hover:text-primary transition-colors">
                                                <x-user.icon name="eye" :size="14" class="mr-2 text-gray-400 group-hover:text-primary" />
                                                Xem chi tiết
                                            </a>
                                            @if($request->status === 'pending')
                                            <a href="{{ route('student.leave-requests.edit', $request) }}" wire:navigate class="group flex items-center px-3 py-2 text-[13px] font-semibold text-gray-700 hover:bg-primary/10 hover:text-primary transition-colors">
                                                <x-user.icon name="edit" :size="14" class="mr-2 text-gray-400 group-hover:text-primary" />
                                                Chỉnh sửa
                                            </a>
                                            <button type="button" wire:click="confirmDelete({{ $request->id }})" class="group flex w-full items-center px-3 py-2 text-[13px] font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
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
        @if($requests->hasPages())
            <div class="border-t border-outline-variant/10 px-6 py-4">
                {{ $requests->links() }}
            </div>
        @endif
    </section>

    <template x-teleport="body">
        <x-modal name="confirm-request-deletion" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="bg-rose-100 rounded-full p-3">
                        <x-user.icon name="alert-triangle" :size="24" class="text-rose-600" />
                    </div>
                </div>
                
                <h2 class="text-lg font-bold text-slate-900 text-center mb-2">
                    Xóa đơn xin nghỉ phép?
                </h2>

                <p class="text-sm text-slate-500 text-center mb-6">
                    Bạn có chắc chắn muốn xóa đơn xin phép này không? Sau khi xóa, bạn sẽ không thể khôi phục lại.
                </p>

                <div class="flex items-center justify-center gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="px-4 py-2 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        Hủy bỏ
                    </button>

                    <button type="button" wire:click="deleteRequest" class="px-4 py-2 bg-rose-600 border border-transparent rounded-xl text-sm font-bold text-white hover:bg-rose-700 transition-colors flex items-center">
                        <div wire:loading wire:target="deleteRequest" class="mr-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        Xác nhận xóa
                    </button>
                </div>
            </div>
        </x-modal>
    </template>
</div>
