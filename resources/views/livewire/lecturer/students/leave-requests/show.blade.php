@php
    $statusMap = [
        'pending' => ['Chờ duyệt', 'bg-amber-50 text-amber-700 border-amber-200'],
        'approved' => ['Đã duyệt', 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'rejected' => ['Đã từ chối', 'bg-red-50 text-red-700 border-red-200'],
    ];
    [$statusLabel, $statusClass] = $statusMap[$leaveRequest->status] ?? [$leaveRequest->status, 'bg-slate-100 text-slate-700 border-slate-200'];
@endphp

<div class="mx-auto max-w-[1100px] space-y-6 p-4 pb-24 sm:p-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div><h1 class="text-3xl font-extrabold uppercase tracking-tight text-slate-900">Chi tiết đơn xin nghỉ</h1><p class="mt-1 text-base text-slate-500">Mã đơn #{{ $leaveRequest->id }}</p></div>
        <div class="flex flex-wrap gap-2">@if($leaveRequest->status === 'pending')<button type="button" wire:click="approve" wire:confirm="Duyệt đơn xin nghỉ này?" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-base font-bold text-white hover:bg-emerald-700"><x-user.icon name="check-circle" :size="20" />Duyệt đơn</button><button type="button" wire:click="$set('showRejectForm', true)" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-base font-bold text-red-600 hover:bg-red-50"><x-user.icon name="x" :size="20" />Từ chối</button>@endif<a href="{{ route('lecturer.leave-requests.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-base font-bold text-slate-600 hover:bg-slate-50">Quay lại</a></div>
    </div>

    @if(session('status'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif

    <section class="grid gap-6 lg:grid-cols-[1fr_1.25fr]">
        <div class="flex h-full flex-col gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between"><h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Thông tin học viên</h2><span class="rounded-full border px-3 py-1 text-sm font-bold {{ $statusClass }}">{{ $statusLabel }}</span></div>
                <div class="flex items-center gap-4"><span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-2xl font-extrabold text-primary">{{ mb_strtoupper(mb_substr($leaveRequest->classMember->full_name, 0, 1)) }}</span><div><a href="{{ route('lecturer.students.show', $leaveRequest->classMember) }}" class="text-xl font-extrabold text-slate-900 hover:text-primary">{{ $leaveRequest->classMember->full_name }}</a><p class="mt-1 text-base text-slate-500">{{ $leaveRequest->classMember->student_code }} · {{ $leaveRequest->classMember->user?->email }}</p></div></div>
                <dl class="mt-5 grid gap-3 border-t border-slate-100 pt-5 text-base"><div><dt class="text-sm font-bold uppercase text-slate-400">Lớp học</dt><dd class="mt-1 font-semibold text-slate-700">{{ $leaveRequest->classMember->courseClass->code }} - {{ $leaveRequest->classMember->courseClass->name }}</dd></div><div><dt class="text-sm font-bold uppercase text-slate-400">Buổi học</dt><dd class="mt-1 font-semibold text-slate-700">{{ $leaveRequest->classSession->name }} · {{ $leaveRequest->classSession->date->format('d/m/Y') }}</dd></div></dl>
            </div>

            <div class="flex flex-1 flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-slate-900">Xét duyệt</h2>@if($leaveRequest->reviewer)<p class="text-base text-slate-600">Xử lý bởi <strong class="text-slate-900">{{ $leaveRequest->reviewer->name }}</strong> lúc {{ $leaveRequest->reviewed_at?->format('H:i d/m/Y') }}.</p>@else<p class="text-base text-slate-500">Đơn chưa được xử lý.</p>@endif @if($leaveRequest->rejected_reason)<div class="mt-4 rounded-xl bg-red-50 p-4 text-base text-red-700"><strong>Lý do từ chối:</strong> {{ $leaveRequest->rejected_reason }}</div>@endif</div>
        </div>

        <div class="flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><h2 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-slate-900">Nội dung đơn</h2><p class="whitespace-pre-line text-base leading-relaxed text-slate-700">{{ $leaveRequest->reason }}</p><p class="mt-5 text-sm text-slate-400">Gửi lúc {{ $leaveRequest->created_at?->format('H:i d/m/Y') }}</p>@if(!empty($leaveRequest->proof_image))<div class="mt-5 border-t border-slate-100 pt-5"><h3 class="mb-3 text-sm font-bold uppercase text-slate-400">Minh chứng đính kèm</h3><div class="grid grid-cols-2 gap-4 sm:grid-cols-3">@foreach($leaveRequest->proof_image as $img)<a href="{{ asset('storage/'.$img) }}" target="_blank" class="group block aspect-square overflow-hidden rounded-xl border border-slate-200 shadow-sm relative"><img src="{{ asset('storage/'.$img) }}" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110" alt="Minh chứng"><div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"><x-user.icon name="external-link" class="text-white" /></div></a>@endforeach</div></div>@endif</div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">Đơn đã duyệt gần đây ({{ $approvedLeaveRequests->total() }})</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[13px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-6 py-4">Buổi học</th>
                            <th scope="col" class="px-6 py-4">Ngày xin nghỉ</th>
                            <th scope="col" class="px-6 py-4">Lý do</th>
                            <th scope="col" class="px-6 py-4 text-right">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($approvedLeaveRequests as $record)
                            <tr class="transition-colors hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-700">
                                    {{ $record->classSession?->name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-semibold">
                                    {{ $record->classSession?->date?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="line-clamp-1 max-w-sm" title="{{ $record->reason }}">{{ $record->reason }}</span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-emerald-700">Đã duyệt</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm font-medium text-slate-500">
                                    Học viên chưa có đơn xin nghỉ nào được duyệt.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($approvedLeaveRequests->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $approvedLeaveRequests->links() }}
                </div>
            @endif
        </div>
    </section>

    @if($showRejectForm)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"><form wire:submit="reject" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl"><div class="mb-5 flex items-center justify-between"><h3 class="text-lg font-bold text-slate-900">Từ chối đơn xin nghỉ</h3><button type="button" wire:click="$set('showRejectForm', false)" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button></div><label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Lý do từ chối</span><textarea wire:model="rejectedReason" rows="4" class="w-full rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500/20"></textarea>@error('rejectedReason')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label><div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="$set('showRejectForm', false)" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white">Xác nhận từ chối</button></div></form></div>
    @endif
</div>
