<div class="mx-auto max-w-7xl p-6 lg:p-8">
    <x-slot name="title">
        Thông tin: {{ $class->name }}
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Lớp học: {{ $class->name }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Mã lớp: {{ $class->code }} • Học kỳ: {{ $class->semester }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('joined-classes') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary hover:bg-primary/90 transition-colors">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về lớp học
            </a>
        </div>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row items-stretch">
        <!-- Sidebar -->
        <div class="flex flex-col gap-6 lg:w-1/3 xl:w-1/4">
            
            <!-- Thông tin lớp -->
            <div class="flex h-full flex-col rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <h3 class="mb-4 font-bold text-on-surface">Chi tiết lớp học</h3>
                <div class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Tên lớp</span>
                        <span class="font-semibold text-on-surface text-right max-w-[60%]">{{ $class->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Giảng viên</span>
                        <span class="font-semibold text-on-surface">{{ $class->owner->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Học kỳ</span>
                        <span class="font-semibold text-on-surface">{{ $class->semester }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Mã học phần</span>
                        <span class="font-semibold text-on-surface">{{ $class->subject_code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Số buổi học</span>
                        <span class="font-semibold text-on-surface">{{ $class->total_sessions }} buổi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col gap-6">
            <!-- Tổng quan -->
            <div class="flex h-full flex-col items-center justify-center rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-outline-variant/20">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-secondary/10">
                    <x-user.icon name="info" :size="32" class="text-secondary" />
                </div>
                <h3 class="text-xl font-bold text-on-surface">Không gian học tập</h3>
                <p class="mx-auto mt-2 max-w-md text-on-surface-variant">Thông tin chi tiết về các hoạt động của lớp học này đang được cập nhật.</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions / Stats -->
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Điểm danh -->
        <a href="{{ route('student.classes.join') }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-secondary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                <x-user.icon name="qr-code" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Vào lớp (Điểm danh)</span>
        </a>

        <!-- Lịch sử -->
        <a href="{{ route('student.attendance.history', ['classFilter' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-primary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="history" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Lịch sử điểm danh</span>
        </a>

        <!-- Đơn xin nghỉ -->
        <a href="{{ route('student.leave-requests.create') }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-[#F59E0B]/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F59E0B]/10 text-[#F59E0B] transition-colors group-hover:bg-[#F59E0B] group-hover:text-white">
                <x-user.icon name="file-text" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Đơn xin nghỉ</span>
        </a>
    </div>
</div>
