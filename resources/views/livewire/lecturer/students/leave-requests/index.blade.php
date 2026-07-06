@php
    $tabs = [
        ['label' => 'Chờ duyệt', 'route' => 'lecturer.leave-requests.index', 'status' => 'pending', 'icon' => 'clock'],
        ['label' => 'Đã duyệt', 'route' => 'lecturer.leave-requests.approved', 'status' => 'approved', 'icon' => 'check-circle'],
        ['label' => 'Đã từ chối', 'route' => 'lecturer.leave-requests.rejected', 'status' => 'rejected', 'icon' => 'x'],
    ];
@endphp

<div class="w-full space-y-6 px-6 py-6 pb-24 sm:px-10 lg:px-16">

    <div class="grid gap-3 lg:grid-cols-[1fr_200px_260px]">
        <label class="relative"><x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm học viên, mã học viên hoặc lý do..." class="w-full rounded-xl border-slate-200 bg-white shadow-sm py-2.5 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary/20"></label>
            
            <div x-data="{ 
                    open: false,
                    options: [
                        { value: 'pending', label: 'Chờ duyệt', route: '{{ route('lecturer.leave-requests.index') }}' },
                        { value: 'approved', label: 'Đã duyệt', route: '{{ route('lecturer.leave-requests.approved') }}' },
                        { value: 'rejected', label: 'Đã từ chối', route: '{{ route('lecturer.leave-requests.rejected') }}' }
                    ],
                    get selectedLabel() {
                        let selected = this.options.find(opt => opt.value === '{{ $status }}');
                        return selected ? selected.label : 'Chờ duyệt';
                    },
                    select(value) {
                        let selected = this.options.find(opt => opt.value === value);
                        if (selected && value !== '{{ $status }}') {
                            Livewire.navigate(selected.route);
                        }
                        this.open = false;
                    }
                }" 
                class="relative min-w-[200px] shrink-0">
                <button @click="open = !open" type="button" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:border-slate-300 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <span x-text="selectedLabel" class="truncate"></span>
                    <x-user.icon name="chevron-down" :size="16" class="ml-2 shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full z-[100] mt-1.5 max-h-64 w-full overflow-y-auto rounded-xl border border-slate-100 bg-white p-1.5 shadow-xl"
                     style="display: none;">
                    
                    <template x-for="option in options" :key="option.value">
                        <button @click="select(option.value)" type="button" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors hover:bg-slate-50 hover:text-primary" :class="option.value === '{{ $status }}' ? 'bg-primary/5 text-primary' : 'text-slate-600'">
                            <span class="truncate" x-text="option.label"></span>
                            <x-user.icon name="check" :size="14" class="ml-auto shrink-0" x-show="option.value === '{{ $status }}'" />
                        </button>
                    </template>
                </div>
            </div>
            <div x-data="{ 
                    open: false,
                    options: [
                        { value: 'all', label: 'Tất cả lớp học' },
                        @foreach($classes as $class)
                            { value: '{{ $class->id }}', label: '{{ $class->join_key }} - {{ $class->name }}' },
                        @endforeach
                    ],
                    get selectedLabel() {
                        let selected = this.options.find(opt => opt.value == $wire.classFilter);
                        return selected ? selected.label : 'Tất cả lớp học';
                    },
                    select(value) {
                        $wire.set('classFilter', value);
                        this.open = false;
                    }
                }" 
                class="relative min-w-[260px]">
                <button @click="open = !open" type="button" class="flex w-full items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:border-slate-300 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <span x-text="selectedLabel" class="truncate">Tất cả lớp học</span>
                    <x-user.icon name="chevron-down" :size="16" class="ml-2 shrink-0 text-slate-400 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div x-show="open" 
                     @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full z-[100] mt-1.5 max-h-64 w-[320px] overflow-y-auto rounded-xl border border-slate-100 bg-white p-1.5 shadow-xl sm:w-full"
                     style="display: none;">
                    
                    <button @click="select('all')" type="button" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors hover:bg-slate-50 hover:text-primary" :class="$wire.classFilter === 'all' ? 'bg-primary/5 text-primary' : 'text-slate-600'">
                        Tất cả lớp học
                        <x-user.icon name="check" :size="14" class="ml-auto shrink-0" x-show="$wire.classFilter === 'all'" />
                    </button>
                    
                    @foreach($classes as $class)
                        <button @click="select('{{ $class->id }}')" type="button" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors hover:bg-slate-50 hover:text-primary" :class="$wire.classFilter == '{{ $class->id }}' ? 'bg-primary/5 text-primary' : 'text-slate-600'">
                            <span class="truncate">{{ $class->join_key }} - {{ $class->name }}</span>
                            <x-user.icon name="check" :size="14" class="ml-auto shrink-0" x-show="$wire.classFilter == '{{ $class->id }}'" />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col" style="min-height: 500px;">
        <div class="overflow-x-auto flex-1">
            <table class="w-full min-w-[1050px] text-left">
                <thead class="bg-slate-50 text-sm font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-4">Học viên</th><th class="px-4 py-4">Lớp học</th><th class="px-4 py-4">Buổi xin nghỉ</th><th class="px-4 py-4">Lý do</th><th class="px-4 py-4">Trạng thái</th><th class="px-6 py-4 text-right whitespace-nowrap">Thao tác</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leaveRequests as $request)
                        <tr class="transition-colors hover:bg-slate-50/70 cursor-pointer" onclick="if(!event.target.closest('a, button')) window.location.href='{{ route('lecturer.leave-requests.show', $request) }}'">
                            <td class="px-6 py-4"><div class="flex items-center gap-3">
                                @if($request->classMember->user && $request->classMember->user->avatar)
                                    <img src="{{ asset('storage/' . $request->classMember->user->avatar) }}" alt="{{ $request->classMember->full_name }}" class="h-10 w-10 shrink-0 rounded-full object-cover">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">{{ mb_strtoupper(mb_substr($request->classMember->full_name, 0, 1)) }}</span>
                                @endif
                                <span><a href="{{ route('lecturer.students.show', $request->classMember) }}" class="block text-sm font-bold text-slate-900 hover:text-primary">{{ $request->classMember->full_name }}</a><span class="text-xs text-slate-500">{{ $request->classMember->student_code }}</span></span></div></td>
                            <td class="px-4 py-4"><span class="block text-sm font-semibold text-slate-700">{{ $request->classMember->courseClass->name }}</span><span class="text-xs text-slate-500">{{ $request->classMember->courseClass->join_key }}</span></td>
                            <td class="px-4 py-4"><span class="block text-sm font-semibold text-slate-700">{{ $request->classMeeting->name }}</span><span class="block mt-0.5 text-xs text-slate-400">Ngày gửi: {{ $request->created_at?->format('d/m/Y H:i') }}</span></td>
                            <td class="max-w-[280px] px-4 py-4"><p class="truncate text-sm text-slate-600" title="{{ $request->reason }}">{{ $request->reason }}</p>@if(!empty($request->proof_image))<div class="mt-1 flex flex-wrap gap-3">@foreach($request->proof_image as $img)<a href="{{ asset('storage/'.$img) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline"><x-user.icon name="image" :size="14" />Ảnh {{ $loop->iteration }}</a>@endforeach</div>@endif</td>
                            <td class="px-4 py-4"><span @class(['inline-flex rounded-full px-3 py-1.5 text-xs font-bold whitespace-nowrap', 'bg-amber-50 text-amber-700' => $request->status === 'pending', 'bg-emerald-50 text-emerald-700' => $request->status === 'approved', 'bg-red-50 text-red-700' => $request->status === 'rejected'])>{{ $request->status === 'pending' ? 'Chờ duyệt' : ($request->status === 'approved' ? 'Đã duyệt' : 'Đã từ chối') }}</span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    @if($request->status === 'pending')
                                        <button type="button" wire:click.stop="openApprove({{ $request->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg text-emerald-600 transition-colors hover:bg-emerald-50 hover:text-emerald-700" title="Duyệt đơn">
                                            <x-user.icon name="check" :size="18" />
                                        </button>
                                        <button type="button" wire:click.stop="openReject({{ $request->id }})" class="flex h-8 w-8 items-center justify-center rounded-lg text-red-600 transition-colors hover:bg-red-50 hover:text-red-700" title="Từ chối đơn">
                                            <x-user.icon name="x" :size="18" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-sm text-slate-500">Không có đơn xin nghỉ ở trạng thái này.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leaveRequests->hasPages())<div class="border-t border-slate-100 px-6 py-4">{{ $leaveRequests->links() }}</div>@endif
    </section>

    @if ($rejectingRequestId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <form wire:submit="reject" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold text-slate-900">Từ chối đơn xin nghỉ</h3><button type="button" wire:click="closeReject" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button></div>
                    <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Lý do từ chối</span><textarea wire:model="rejectedReason" rows="4" class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500/20" placeholder="Nhập lý do để học viên biết..."></textarea>@error('rejectedReason')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeReject" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Xác nhận từ chối</button></div>
                </form>
            </div>
        </template>
    @endif

    @if ($approvingRequestId)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                            <x-user.icon name="check-circle" :size="20" class="text-emerald-600" />
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Xác nhận duyệt</h3>
                    </div>
                    <p class="text-sm text-slate-600">Bạn có chắc chắn muốn duyệt đơn xin nghỉ phép này không?</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeApprove"
                            class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">Hủy</button>
                        <button type="button" wire:click="confirmApprove"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition-colors">Duyệt đơn</button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
