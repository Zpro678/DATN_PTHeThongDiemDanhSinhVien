@php
    $statusMeta = [
        'present' => ['label' => 'Có mặt', 'short' => 'Có mặt', 'card' => 'border-emerald-500', 'iconBg' => 'bg-emerald-50 text-emerald-600', 'button' => 'bg-emerald-600 text-white', 'soft' => 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100', 'row' => 'bg-emerald-50/60'],
        'late' => ['label' => 'Đi trễ', 'short' => 'Đi trễ', 'card' => 'border-amber-500', 'iconBg' => 'bg-amber-50 text-amber-600', 'button' => 'bg-amber-500 text-white', 'soft' => 'bg-amber-50 text-amber-700 hover:bg-amber-100', 'row' => 'bg-amber-50/70'],
        'excused' => ['label' => 'Vắng phép', 'short' => 'Vắng phép', 'card' => 'border-blue-500', 'iconBg' => 'bg-blue-50 text-blue-600', 'button' => 'bg-blue-600 text-white', 'soft' => 'bg-blue-50 text-blue-700 hover:bg-blue-100', 'row' => 'bg-blue-50/60'],
        'absent' => ['label' => 'Vắng KP', 'short' => 'Vắng KP', 'card' => 'border-rose-500', 'iconBg' => 'bg-rose-50 text-rose-600', 'button' => 'bg-rose-600 text-white', 'soft' => 'bg-rose-50 text-rose-700 hover:bg-rose-100', 'row' => 'bg-rose-50/70'],
        'pending' => ['label' => 'Chưa ĐD', 'short' => 'Chưa ĐD', 'card' => 'border-slate-400', 'iconBg' => 'bg-slate-100 text-slate-500', 'button' => 'bg-slate-900 text-white', 'soft' => 'bg-slate-100 text-slate-600 hover:bg-slate-200', 'row' => 'bg-white'],
    ];

    $isClosed = $session->status === 'closed';
@endphp

<div class="mx-auto max-w-[1400px] p-4 pb-24 sm:p-8" x-data="{ modalOpen: false, confirmChecked: false }">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <div class="mb-3 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Điểm danh thủ công</h1>
                <span @class([
                    'inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-bold',
                    'border-slate-200 bg-slate-100 text-slate-600' => $isClosed,
                    'border-emerald-200 bg-emerald-50 text-emerald-600' => ! $isClosed,
                ])>
                    <span @class([
                        'h-2 w-2 rounded-full',
                        'bg-slate-400' => $isClosed,
                        'animate-pulse bg-emerald-500' => ! $isClosed,
                    ])></span>
                    {{ $isClosed ? 'Đã chốt sổ' : 'Đang điểm danh' }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-500">
                <div class="flex items-center gap-2">
                    <x-user.icon name="users" :size="20" class="text-blue-600" />
                    <span class="font-bold text-slate-900">{{ $session->courseClass->code }} - {{ $session->courseClass->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-user.icon name="clock" :size="20" />
                    <span class="font-medium">
                        {{ $session->name }}
                        • {{ $session->date->format('d/m/Y') }}
                        @if ($session->start_time || $session->end_time)
                            • {{ \Illuminate\Support\Str::of((string) $session->start_time)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of((string) $session->end_time)->substr(0, 5) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('lecturer.attendance.manual.create') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                Thiết lập lại
            </a>
            <button
                type="button"
                wire:click="markAllPresent"
                @disabled($isClosed)
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-500/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <x-user.icon name="check-circle-2" :size="16" />
                Đánh dấu tất cả có mặt
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['key' => 'present', 'icon' => 'check-circle-2'],
            ['key' => 'absent', 'icon' => 'x-circle'],
            ['key' => 'late', 'icon' => 'clock'],
            ['key' => 'excused', 'icon' => 'clipboard-check'],
            ['key' => 'pending', 'icon' => 'more-horizontal'],
        ] as $card)
            <button
                type="button"
                wire:click="setStatusFilter('{{ $card['key'] }}')"
                @class([
                    'rounded-2xl border-l-4 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md',
                    $statusMeta[$card['key']]['card'],
                    'ring-2 ring-primary/30' => $statusFilter === $card['key'],
                ])
            >
                <div class="mb-2 flex items-center gap-3">
                    <div class="{{ $statusMeta[$card['key']]['iconBg'] }} flex h-10 w-10 items-center justify-center rounded-full">
                        <x-user.icon :name="$card['icon']" :size="20" />
                    </div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{{ $statusMeta[$card['key']]['label'] }}</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-900">{{ $summary[$card['key']] }}</p>
            </button>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-200 bg-white p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Danh sách sinh viên</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Hiển thị <span class="font-bold">{{ $records->count() }}</span> / {{ $summary['total'] }} sinh viên
                </p>
            </div>

            <div class="flex flex-col gap-3 lg:min-w-[620px]">
                <form wire:submit.prevent="searchStudents" class="flex flex-col gap-2 sm:flex-row">
                    <label class="relative flex-1">
                        <x-user.icon name="search" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            wire:model="search"
                            placeholder="Tìm theo tên hoặc mã số sinh viên..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-3 text-sm font-medium outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20"
                        >
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-primary-container">
                        <x-user.icon name="search" :size="16" />
                        Tìm kiếm
                    </button>
                    @if ($search !== '' || $statusFilter !== 'all')
                        <button type="button" wire:click="clearSearch" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                            Xóa lọc
                        </button>
                    @endif
                </form>

                <div class="flex flex-wrap gap-2">
                    @foreach ([
                        'all' => 'Tất cả',
                        'pending' => 'Chưa ĐD',
                        'present' => 'Có mặt',
                        'absent' => 'Vắng KP',
                        'late' => 'Đi trễ',
                        'excused' => 'Vắng phép',
                    ] as $status => $label)
                        <button
                            type="button"
                            wire:click="setStatusFilter('{{ $status }}')"
                            @class([
                                'rounded-xl px-3 py-2 text-xs font-bold transition',
                                'bg-slate-900 text-white' => $statusFilter === $status,
                                'bg-slate-100 text-slate-600 hover:bg-slate-200' => $statusFilter !== $status,
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] border-collapse text-left">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="w-16 px-5 py-4">STT</th>
                        <th class="w-32 px-5 py-4">MSSV</th>
                        <th class="px-5 py-4">Họ tên</th>
                        <th class="px-5 py-4 text-center">Trạng thái điểm danh</th>
                        <th class="w-64 px-5 py-4">Ghi chú</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($records as $record)
                        <tr class="{{ $statusMeta[$record->status]['row'] ?? $statusMeta['pending']['row'] }} transition-colors">
                            <td class="px-5 py-4 font-bold text-slate-500">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-4 font-mono font-bold text-slate-600">{{ $record->classMember->student_code }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-extrabold text-blue-700">
                                        {{ \Illuminate\Support\Str::substr($record->classMember->full_name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-900">{{ $record->classMember->full_name }}</p>
                                        <p class="text-xs font-medium text-slate-400">{{ $statusMeta[$record->status]['label'] ?? $statusMeta['pending']['label'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap justify-center gap-3">
                                    @foreach (['present', 'absent', 'late', 'excused'] as $option)
                                        <button
                                            type="button"
                                            wire:click="updateStatus({{ $record->id }}, '{{ $option }}')"
                                            @disabled($isClosed)
                                            @class([
                                                'rounded-full px-3 py-2 text-xs font-bold transition disabled:cursor-not-allowed disabled:opacity-60',
                                                $statusMeta[$option]['button'] => $record->status === $option,
                                                $statusMeta[$option]['soft'] => $record->status !== $option,
                                            ])
                                        >
                                            {{ $statusMeta[$option]['short'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <input
                                    type="text"
                                    value="{{ $record->note }}"
                                    wire:change="updateNote({{ $record->id }}, $event.target.value)"
                                    placeholder="Thêm ghi chú..."
                                    @disabled($isClosed)
                                    class="w-full rounded-lg border border-transparent bg-transparent px-3 py-2 text-sm font-medium outline-none transition hover:border-slate-200 hover:bg-white focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:text-slate-400"
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm font-semibold text-slate-500">
                                Không tìm thấy sinh viên phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-medium text-slate-500">
                Tổng sĩ số: <span class="font-bold text-slate-900">{{ $summary['total'] }}</span>
                <span class="mx-2 text-slate-300">|</span>
                Có mặt: <span class="font-bold text-emerald-600">{{ $summary['present'] }}</span>
            </p>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                <x-user.icon name="download" :size="16" />
                Xuất Excel
            </button>
        </div>
    </div>

    <div class="sticky bottom-0 z-30 -mx-4 mt-8 border-t border-slate-200 bg-white/85 px-4 py-4 backdrop-blur-md md:-mx-8 md:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <div class="text-left sm:text-right">
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-500">Tổng hợp nhanh</span>
                <span class="text-lg font-extrabold text-slate-900">
                    {{ $summary['present'] }}/{{ $summary['total'] }} có mặt
                    ({{ $summary['present_percent'] }}%)
                </span>
            </div>
            <button
                type="button"
                @click="modalOpen = true; confirmChecked = false"
                @disabled($isClosed)
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-orange-500 px-8 py-3 text-lg font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <x-user.icon name="save" :size="20" />
                {{ $isClosed ? 'ĐÃ CHỐT SỔ' : 'LƯU & CHỐT SỔ' }}
            </button>
        </div>
    </div>

    <div x-cloak x-show="modalOpen" x-transition.opacity class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <button type="button" class="absolute inset-0 bg-slate-950/45 backdrop-blur-sm" @click="modalOpen = false" aria-label="Đóng"></button>

        <div x-show="modalOpen" x-transition.scale.origin.center.duration.150ms class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <div class="flex items-start gap-4 border-b border-slate-200 p-6">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                    <x-user.icon name="lock" :size="24" />
                </div>
                <div>
                    <h3 class="text-2xl font-extrabold text-slate-900">Xác nhận chốt sổ điểm danh?</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">Sau khi chốt sổ, giao diện sẽ khóa chỉnh sửa trong phiên hiện tại.</p>
                </div>
            </div>

            <div class="space-y-6 p-6">
                <div class="grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 sm:grid-cols-5">
                    @foreach (['present', 'absent', 'late', 'excused', 'pending'] as $status)
                        <div class="border-b border-r border-slate-200 p-4 text-center last:border-r-0 sm:border-b-0">
                            <p class="mb-1 text-xs font-bold uppercase text-slate-500">{{ $statusMeta[$status]['label'] }}</p>
                            <p class="text-2xl font-extrabold text-slate-900">{{ $summary[$status] }}</p>
                        </div>
                    @endforeach
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-transparent bg-slate-50 p-4 transition hover:border-slate-200">
                    <input type="checkbox" x-model="confirmChecked" class="h-5 w-5 rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                    <span class="select-none font-semibold text-slate-900">Tôi đã kiểm tra lại danh sách điểm danh.</span>
                </label>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 p-6 sm:flex-row">
                <button type="button" class="rounded-xl border border-slate-200 bg-white px-6 py-3 font-bold text-slate-700 transition hover:bg-slate-100 sm:flex-[0.4]" @click="modalOpen = false">
                    Quay lại kiểm tra
                </button>
                <button
                    type="button"
                    wire:click="closeSession"
                    @click="modalOpen = false"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-orange-500 px-6 py-3 font-bold text-white shadow-sm shadow-orange-500/20 transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="! confirmChecked"
                >
                    <x-user.icon name="user-check" :size="20" />
                    Chốt sổ điểm danh
                </button>
            </div>
        </div>
    </div>
</div>
