<div class="admin-card admin-card-hover flex h-full min-h-[430px] flex-col justify-between overflow-hidden rounded-2xl border p-6">
    <div class="relative z-10">
        <div class="mb-6 flex flex-col gap-4 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-500">
                    <x-user.icon name="shield-alert" :size="16" />
                </div>
                <div>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-900">
                        Cảnh báo học viên vắng nhiều
                    </h2>
                    <p class="mt-0.5 text-xs font-semibold text-slate-400">
                        Danh sách những bạn có tỷ lệ đi học thấp hơn 80%.
                    </p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="whitespace-nowrap px-4 py-3 w-[30%]">Họ và tên</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Mã lớp</th>
                        <th class="whitespace-nowrap px-4 py-3 w-[30%]">Môn học</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center">Tỷ lệ đi học</th>
                        <th class="min-w-[160px] whitespace-nowrap px-4 py-3 text-right">Đánh giá chung</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($warningStudents as $item)
                        <tr class="transition-colors hover:bg-blue-50/40">
                            <td class="px-4 py-3.5 font-extrabold text-slate-800">{{ $item['name'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-center font-bold text-slate-500">{{ $item['class'] }}</td>
                            <td class="max-w-[250px] truncate px-4 py-3.5 font-medium text-slate-600">{{ $item['subject'] }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="font-black text-xs {{ $item['attendanceRate'] < 70 ? 'text-rose-600' : 'text-amber-600' }}">
                                    {{ $item['attendanceRate'] }}%
                                </span>
                                <div class="mx-auto mt-1.5 h-1.5 w-16 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $item['attendanceRate'] < 70 ? 'bg-rose-500' : 'bg-amber-500' }}" style="width: {{ $item['attendanceRate'] }}%"></div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                <span class="rounded-lg border px-2.5 py-1 text-[10px] font-bold {{ $item['levelColor'] }}">
                                    {{ $item['level'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Không có học viên nào bị cảnh báo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="relative z-10 mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-xs font-bold text-slate-400">
        <span>Hiển thị {{ min(10, count($warningStudents)) }} / {{ $warningCount }} trường hợp</span>
        <button type="button" class="text-blue-600 hover:text-blue-700 hover:underline">
            Quản lý toàn bộ cảnh báo chuyên cần &rarr;
        </button>
    </div>
</div>
