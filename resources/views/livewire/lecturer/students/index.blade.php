<div class="mx-auto max-w-[1400px] space-y-6 p-4 pb-24 sm:p-8">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h1 class="flex items-center gap-3 text-2xl font-extrabold uppercase tracking-tight text-slate-900">
                <x-user.icon name="users" class="text-primary" />
                Sinh viên
            </h1>
            <p class="mt-2 text-sm text-slate-500">Quản lý danh sách sinh viên trong các lớp bạn đang phụ trách.</p>
        </div>
        <a href="{{ route('lecturer.leave-requests.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-5 py-2.5 text-sm font-bold text-primary transition-colors hover:bg-primary/10">
            <x-user.icon name="file-text" :size="18" />
            Đơn xin nghỉ
        </a>
    </section>

    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Tổng sinh viên', 'value' => $attendanceOverview['total_students'], 'color' => 'text-primary'],
            ['label' => 'Có mặt', 'value' => $attendanceOverview['present_lessons'], 'color' => 'text-emerald-600'],
            ['label' => 'Muộn', 'value' => $attendanceOverview['late_lessons'], 'color' => 'text-amber-600'],
            ['label' => 'Vắng', 'value' => $attendanceOverview['absent_lessons'], 'color' => 'text-red-600'],
            ['label' => 'Chuyên cần tổng', 'value' => $attendanceOverview['attendance_percent'].'%', 'color' => 'text-primary'],
        ] as $overviewItem)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $overviewItem['label'] }}</p>
                <p class="mt-2 text-3xl font-extrabold {{ $overviewItem['color'] }}">{{ $overviewItem['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-[1fr_260px_auto]">
        <label class="relative">
            <x-user.icon name="search" :size="18" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Tìm theo tên, mã sinh viên hoặc email..." class="w-full rounded-xl border-slate-200 py-2.5 pl-11 pr-4 text-sm focus:border-primary focus:ring-primary/20">
        </label>
        <select wire:model.live="classFilter" class="rounded-xl border-slate-200 text-sm font-semibold text-slate-700 focus:border-primary focus:ring-primary/20">
            <option value="all">Tất cả lớp học</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->code }} - {{ $class->name }}</option>
            @endforeach
        </select>
        <div class="flex rounded-xl bg-slate-100 p-1">
            <button type="button" wire:click="setStatusFilter('active')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'active', 'text-slate-500' => $statusFilter !== 'active'])>Đang học</button>
            <button type="button" wire:click="setStatusFilter('archived')" @class(['rounded-lg px-4 py-2 text-xs font-bold transition-colors', 'bg-white text-primary shadow-sm' => $statusFilter === 'archived', 'text-slate-500' => $statusFilter !== 'archived'])>Lưu trữ</button>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left">
                <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Sinh viên</th>
                        <th class="px-4 py-4">Lớp học</th>
                        <th class="px-4 py-4 text-center">Có mặt</th>
                        <th class="px-4 py-4 text-center">Muộn</th>
                        <th class="px-4 py-4 text-center">Vắng</th>
                        <th class="px-4 py-4 text-center">Chuyên cần</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($members as $member)
                        @php
                            $stats = $attendanceStats[$member->id] ?? [
                                'present_lessons' => 0,
                                'late_lessons' => 0,
                                'absent_lessons' => 0,
                                'attendance_percent' => 0,
                            ];
                            $rate = (float) $stats['attendance_percent'];
                        @endphp
                        <tr class="transition-colors hover:bg-slate-50/70">
                            <td class="px-6 py-4">
                                <a href="{{ route('lecturer.students.show', $member) }}" class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">{{ mb_strtoupper(mb_substr($member->full_name, 0, 1)) }}</span>
                                    <span>
                                        <span class="block text-sm font-bold text-slate-900">{{ $member->full_name }}</span>
                                        <span class="block text-xs text-slate-500">{{ $member->student_code }} · {{ $member->user?->email ?? 'Chưa liên kết tài khoản' }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="px-4 py-4">
                                <span class="block text-sm font-semibold text-slate-700">{{ $member->courseClass->name }}</span>
                                <span class="text-xs text-slate-500">{{ $member->courseClass->code }}</span>
                            </td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-emerald-600">{{ $stats['present_lessons'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-amber-600">{{ $stats['late_lessons'] }}</td>
                            <td class="px-4 py-4 text-center text-sm font-bold text-red-600">{{ $stats['absent_lessons'] }}</td>
                            <td class="px-4 py-4 text-center">
                                <span @class(['inline-flex rounded-full px-3 py-1 text-xs font-bold', 'bg-emerald-50 text-emerald-700' => $rate >= 80, 'bg-amber-50 text-amber-700' => $rate >= 60 && $rate < 80, 'bg-red-50 text-red-700' => $rate < 60])>{{ $rate }}%</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('lecturer.students.show', $member) }}" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-primary/10 hover:text-primary" title="Xem chi tiết"><x-user.icon name="eye" :size="18" /></a>
                                    @if ($statusFilter === 'active')
                                        <button type="button" wire:click="openEdit({{ $member->id }})" class="rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-800" title="Sửa"><x-user.icon name="edit" :size="18" /></button>
                                        <button type="button" wire:click="archiveMember({{ $member->id }})" wire:confirm="Chuyển sinh viên này vào lưu trữ?" class="rounded-lg p-2 text-red-500 transition-colors hover:bg-red-50" title="Lưu trữ"><x-user.icon name="x" :size="18" /></button>
                                    @else
                                        <button type="button" wire:click="restoreMember({{ $member->id }})" class="rounded-lg px-3 py-2 text-xs font-bold text-primary transition-colors hover:bg-primary/10">Khôi phục</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-14 text-center text-sm text-slate-500">Không tìm thấy sinh viên phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($members->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $members->links() }}</div>
        @endif
    </section>

    @if ($editingMemberId)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <form wire:submit="saveMember" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Sửa thông tin sinh viên</h3>
                    <button type="button" wire:click="closeEdit" class="rounded-full p-2 text-slate-400 hover:bg-slate-100"><x-user.icon name="x" :size="18" /></button>
                </div>
                <div class="space-y-4">
                    <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Họ và tên</span><input wire:model="editingName" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20">@error('editingName')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Mã sinh viên</span><input wire:model="editingStudentCode" class="w-full rounded-xl border-slate-200 uppercase focus:border-primary focus:ring-primary/20">@error('editingStudentCode')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="block space-y-2"><span class="text-sm font-semibold text-slate-700">Trạng thái</span><select wire:model="editingStatus" class="w-full rounded-xl border-slate-200 focus:border-primary focus:ring-primary/20"><option value="active">Đang học</option><option value="dropped">Đã thôi học</option></select></label>
                </div>
                <div class="mt-6 flex justify-end gap-3"><button type="button" wire:click="closeEdit" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100">Hủy</button><button type="submit" class="rounded-xl bg-primary px-5 py-2.5 text-sm font-bold text-white">Lưu thay đổi</button></div>
            </form>
        </div>
    @endif
</div>
