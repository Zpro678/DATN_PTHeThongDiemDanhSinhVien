<div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-surface-container-low text-on-surface-variant transition-colors hover:bg-surface-container hover:text-on-surface">
            <x-user.icon name="arrow-left" :size="20" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Thống kê lớp: {{ $courseClass->course_name }}</h1>
            <p class="text-sm text-on-surface-variant">{{ $courseClass->course_code }} • Học kỳ 2 2025-2026</p>
        </div>
    </div>

    <!-- Overview Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-tertiary/10 text-tertiary">
                <x-user.icon name="users" :size="28" />
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface-variant">Tổng sinh viên</p>
                <h3 class="text-3xl font-bold text-on-surface leading-tight">{{ $totalStudents }}</h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                <x-user.icon name="calendar" :size="28" />
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface-variant">Buổi đã điểm danh</p>
                <h3 class="text-3xl font-bold text-on-surface leading-tight">{{ $totalSessions }}</h3>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-outline-variant/10 transition-shadow hover:shadow-md">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                <x-user.icon name="bar-chart-2" :size="28" />
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface-variant">Chuyên cần trung bình</p>
                <h3 class="text-3xl font-bold text-on-surface leading-tight">{{ $averageAttendance }}%</h3>
            </div>
        </div>
    </div>

    <!-- Detailed Stats -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Warning Students -->
        <div class="lg:col-span-1">
            <h4 class="mb-4 flex items-center gap-2 text-[16px] font-bold text-on-surface">
                <x-user.icon name="alert-triangle" class="text-error" />
                Sinh viên cần chú ý
            </h4>
            <div class="rounded-3xl border border-outline-variant/10 bg-white p-5">
                @if(count($warningStudents) > 0)
                    <div class="space-y-4">
                        @foreach($warningStudents as $student)
                            <div class="flex items-center justify-between border-b border-outline-variant/10 pb-4 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-bold text-on-surface">{{ $student['name'] }}</p>
                                    <p class="text-xs text-on-surface-variant">{{ $student['code'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-error">Vắng {{ $student['absent'] }} buổi</p>
                                    <p class="text-xs text-on-surface-variant">Chuyên cần: {{ $student['percent'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center text-sm text-on-surface-variant">
                        Không có sinh viên nào rơi vào mức cảnh báo.
                    </div>
                @endif
            </div>
        </div>

        <!-- Attendance Chart (Mocked UI) -->
        <div class="lg:col-span-2">
            <h4 class="mb-4 flex items-center gap-2 text-[16px] font-bold text-on-surface">
                <x-user.icon name="trending-up" class="text-tertiary" />
                Tiến độ chuyên cần
            </h4>
            <div class="flex h-64 flex-col items-center justify-center rounded-3xl border border-outline-variant/10 bg-white p-6">
                <!-- A simple visual representation since we don't have a charting library handy -->
                <div class="flex w-full h-full items-end justify-around gap-2 px-4 pb-2 pt-8">
                    @php
                        $mockData = [95, 92, 100, 88, 85, 90, 94, 98];
                    @endphp
                    @foreach($mockData as $idx => $val)
                        <div class="group relative flex w-full max-w-[40px] flex-col items-center justify-end">
                            <div class="absolute -top-8 hidden rounded bg-surface-container-high px-2 py-1 text-xs font-bold text-on-surface group-hover:block">{{ $val }}%</div>
                            <div class="w-full rounded-t-md bg-primary/20 transition-all group-hover:bg-primary/40" style="height: {{ $val }}%"></div>
                            <span class="mt-2 text-xs text-on-surface-variant">B{{ $idx + 1 }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
    </div>
</div>
