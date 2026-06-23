@php
    $tabs = [
        ['label' => 'Chờ duyệt', 'route' => 'lecturer.leave-requests.index', 'status' => 'pending', 'icon' => 'clock'],
        ['label' => 'Đã duyệt', 'route' => 'lecturer.leave-requests.approved', 'status' => 'approved', 'icon' => 'check-circle'],
        ['label' => 'Đã từ chối', 'route' => 'lecturer.leave-requests.rejected', 'status' => 'rejected', 'icon' => 'x'],
    ];
@endphp

<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900"><x-user.icon name="file-text" class="text-primary" />Đơn xin nghỉ phép</h1>
            <p class="mt-2 text-sm text-slate-500">Theo dõi và xử lý đơn xin nghỉ của học viên trong các lớp bạn quản lý.</p>
        </div>
        <a href="{{ route('lecturer.students.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"><x-user.icon name="users" :size="18" />Quản lý học viên</a>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <nav class="grid grid-cols-3 border-b border-slate-200">
            @foreach ($tabs as $tab)
                <a href="{{ route($tab['route']) }}" @class(['relative flex items-center justify-center gap-2 px-3 py-4 text-sm font-bold transition-colors', 'bg-blue-50/50 text-primary' => $status === $tab['status'], 'text-slate-500 hover:bg-slate-50 hover:text-slate-800' => $status !== $tab['status']])>
                    <x-user.icon :name="$tab['icon']" :size="18" />
                    <span>{{ $tab['label'] }}</span>
                    @if ($status === $tab['status'])<span class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary"></span>@endif
                </a>
            @endforeach
        </nav>

        <div class="grid gap-3 border-b border-slate-100 p-4 lg:grid-cols-[1fr_300px]">
            <label class="relative"><x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" /><input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm học viên, mã học viên hoặc lý do..." class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary/20"></label>
            <select wire:model.live="classFilter" class="rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-primary focus:ring-primary/20"><option value="all">Tất cả lớp học</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>@endforeach</select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500"><tr><th class="px-6 py-4">Học viên</th><th class="px-4 py-4">Lớp học</th><th class="px-4 py-4">Buổi xin nghỉ</th><th class="px-4 py-4">Lý do</th><th class="px-4 py-4">Ngày gửi</th><th class="px-6 py-4 text-right">Thao tác</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($leaveRequests as $request)
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">{{ mb_strtoupper(mb_substr($request->classMember->full_name, 0, 1)) }}</span><span><a href="{{ route('lecturer.students.show', $request->classMember) }}" class="block text-sm font-bold text-slate-900 hover:text-primary">{{ $request->classMember->full_name }}</a><span class="text-xs text-slate-500">{{ $request->classMember->student_code }}</span></span></div></td>
                            <td class="px-4 py-4"><span class="block text-sm font-semibold text-slate-700">{{ $request->classMember->courseClass->name }}</span><span class="text-xs text-slate-500">{{ $request->classMember->courseClass->code }}</span></td>
                            <td class="px-4 py-4"><span class="block text-sm font-semibold text-slate-700">{{ $request->classSession->name }}</span><span class="text-xs text-slate-500">{{ $request->classSession->date->format('d/m/Y') }}</span></td>
                            <td class="max-w-[280px] px-4 py-4"><p class="line-clamp-2 text-sm text-slate-600">{{ $request->reason }}</p>@if(!empty($request->proof_image))<div class="mt-1 flex flex-wrap gap-3">@foreach($request->proof_image as $img)<a href="{{ asset('storage/'.$img) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline"><x-user.icon name="image" :size="14" />Ảnh {{ $loop->iteration }}</a>@endforeach</div>@endif</td>
                            <td class="px-4 py-4 text-sm text-slate-500">{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4"><div class="flex justify-end gap-2"><a href="{{ route('lecturer.leave-requests.show', $request) }}" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-primary" title="Xem chi tiết"><x-user.icon name="eye" :size="18" /></a>@if($request->status === 'pending')<button type="button" wire:click="approve({{ $request->id }})" wire:confirm="Duyệt đơn xin nghỉ này?" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">Duyệt</button><button type="button" wire:click="openReject({{ $request->id }})" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 hover:bg-red-50">Từ chối</button>@else<span @class(['rounded-full px-3 py-1.5 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $request->status === 'approved', 'bg-red-50 text-red-700' => $request->status === 'rejected'])>{{ $request->status === 'approved' ? 'Đã duyệt' : 'Đã từ chối' }}</span>@endif</div></td>
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
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <form wire:submit="reject" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold text-slate-900">Từ chối đơn xin nghỉ</h3><button type="button" wire:click="closeReject" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button></div>
                <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Lý do từ chối</span><textarea wire:model="rejectedReason" rows="4" class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500/20" placeholder="Nhập lý do để học viên biết..."></textarea>@error('rejectedReason')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeReject" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Xác nhận từ chối</button></div>
            </form>
        </div>
    @endif
</div>
