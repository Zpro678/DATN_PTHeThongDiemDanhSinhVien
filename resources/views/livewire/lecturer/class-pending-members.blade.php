<div wire:poll.3s>
    <div class="space-y-8 px-6 py-6 pb-24 sm:px-10 lg:px-16 font-sans text-slate-800 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-6 md:flex-row md:items-end px-2">
        <div class="space-y-1">
            <p class="text-sm font-semibold tracking-widest text-blue-500 uppercase">Duyệt học viên</p>
            <h1 class="text-3xl font-black tracking-tight text-slate-900 flex items-center gap-3">
                {{ $courseClass->join_key }} 
                <span class="text-slate-300 font-light">|</span> 
                {{ $courseClass->name }}
            </h1>
        </div>
    </section>



    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 px-2">
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                <x-user.icon name="search" :size="18" />
            </div>
            <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-11 pr-4 py-3 border border-slate-200 rounded-2xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-shadow font-medium" placeholder="Tìm kiếm theo tên hoặc email...">
        </div>
        
        @if($pendingMembers->count() > 0)
        <button x-data @click="$dispatch('open-modal', 'confirm-approve-all')" class="inline-flex items-center gap-2 rounded-2xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
            <x-user.icon name="check-square" :size="18" />
            Duyệt tất cả ({{ $pendingMembers->count() }})
        </button>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden min-h-[calc(100vh-320px)] flex flex-col">
        @if($pendingMembers->isEmpty())
            <div class="flex flex-1 flex-col items-center justify-center py-20 text-center bg-slate-50/30">
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-user.icon name="users" :size="32" />
                </div>
                <h3 class="text-base font-bold text-slate-700">Chưa có yêu cầu nào</h3>
                <p class="mt-1 text-sm text-slate-500">Hiện tại không có học viên nào đang chờ duyệt vào lớp.</p>
            </div>
        @else
            <div class="overflow-x-auto flex-1 bg-white {{ $pendingMembers->count() > 30 ? 'max-h-[700px] overflow-y-auto relative' : '' }}">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-white border-b border-slate-100 {{ $pendingMembers->count() > 30 ? 'sticky top-0 z-10 shadow-sm' : '' }}">
                        <tr>
                            <th scope="col" class="px-6 py-5 font-black uppercase tracking-widest text-slate-700 text-[13px] {{ $pendingMembers->count() > 30 ? 'bg-white' : '' }}">STT</th>
                            <th scope="col" class="px-6 py-5 font-black uppercase tracking-widest text-slate-700 text-[13px] {{ $pendingMembers->count() > 30 ? 'bg-white' : '' }}">Sinh viên</th>
                            <th scope="col" class="px-6 py-5 font-black uppercase tracking-widest text-slate-700 text-[13px] {{ $pendingMembers->count() > 30 ? 'bg-white' : '' }}">Email</th>
                            <th scope="col" class="px-6 py-5 font-black uppercase tracking-widest text-slate-700 text-[13px] text-center {{ $pendingMembers->count() > 30 ? 'bg-white' : '' }}">Thời gian xin vào</th>
                            <th scope="col" class="px-6 py-5 font-black uppercase tracking-widest text-slate-700 text-[13px] text-right {{ $pendingMembers->count() > 30 ? 'bg-white' : '' }}">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($pendingMembers as $index => $member)
                            <tr class="transition-colors hover:bg-slate-50 group/row">
                                <td class="px-6 py-4 font-bold text-slate-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($member->user && $member->user->avatar)
                                            <img src="{{ $member->user->avatar_url }}" alt="{{ $member->user->name }}" class="h-10 w-10 shrink-0 rounded-2xl object-cover border border-slate-200">
                                        @else
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl font-bold bg-blue-100 text-blue-700 border border-blue-200">
                                                {{ mb_substr($member->user?->name ?? 'H', 0, 1) }}
                                            </div>
                                        @endif
                                        <div class="flex flex-col">
                                            <span class="font-black text-slate-900">{{ $member->user?->name ?? 'Học viên' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($member->user)
                                        <div class="text-[13px] font-semibold text-slate-700">{{ $member->user->email }}</div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">
                                            Chưa liên kết
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-medium text-slate-500">
                                    {{ $member->created_at->format('H:i d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="approve({{ $member->id }})" class="inline-flex items-center justify-center rounded-xl bg-emerald-50 p-2 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 transition-colors" title="Duyệt">
                                            <x-user.icon name="check" :size="18" stroke-width="2.5" />
                                        </button>
                                        <button wire:click="confirmReject({{ $member->id }}, '{{ addslashes($member->user?->name ?? 'Học viên') }}')" class="inline-flex items-center justify-center rounded-xl bg-rose-50 p-2 text-rose-600 hover:bg-rose-100 hover:text-rose-700 transition-colors" title="Từ chối">
                                            <x-user.icon name="x" :size="18" stroke-width="2.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    </div>

    <!-- Modal Xóa -->
    <x-modal name="confirm-reject" maxWidth="sm" focusable>
        <div class="p-6 relative">
            <button x-on:click="$dispatch('close')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-500 transition-colors bg-slate-50 hover:bg-slate-100 rounded-full p-1.5">
                <x-user.icon name="x" :size="18" stroke-width="2.5" />
            </button>

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <x-user.icon name="alert-triangle" :size="20" stroke-width="2" />
                </div>
                <h2 class="text-lg font-bold text-slate-900">
                    Từ chối học viên
                </h2>
            </div>

            <p class="mt-2 text-sm text-slate-600">
                Bạn có chắc chắn muốn từ chối yêu cầu tham gia của sinh viên <span class="font-bold text-slate-900">{{ $rejectingName }}</span> không? Hành động này không thể hoàn tác.
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Hủy bỏ
                </x-secondary-button>

                <x-danger-button wire:click="reject({{ $rejectingId }})">
                    Xác nhận từ chối
                </x-danger-button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Duyệt tất cả -->
    <x-modal name="confirm-approve-all" maxWidth="sm" focusable>
        <div class="p-6 relative">
            <button x-on:click="$dispatch('close')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-500 transition-colors bg-slate-50 hover:bg-slate-100 rounded-full p-1.5">
                <x-user.icon name="x" :size="18" stroke-width="2.5" />
            </button>

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <x-user.icon name="check-circle" :size="20" stroke-width="2" />
                </div>
                <h2 class="text-lg font-bold text-slate-900">
                    Duyệt tất cả học viên
                </h2>
            </div>

            <p class="mt-2 text-sm text-slate-600">
                Bạn có chắc chắn muốn <span class="font-bold text-slate-900">duyệt tất cả {{ $pendingMembers->count() }} yêu cầu</span> tham gia lớp học không?
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Hủy bỏ
                </x-secondary-button>

                <x-primary-button wire:click="approveAll" class="!bg-emerald-600 hover:!bg-emerald-700">
                    Duyệt tất cả
                </x-primary-button>
            </div>
        </div>
    </x-modal>
</div>
