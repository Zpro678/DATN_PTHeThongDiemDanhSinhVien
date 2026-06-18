<div x-data="{ showImportModal: false }" class="mx-auto max-w-7xl p-6 lg:p-8">
    <x-slot name="title">
        {{ $class->name }}
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-on-surface">Lớp học: {{ $class->name }}</h1>
            <p class="mt-1 text-sm text-on-surface-variant">Mã lớp: {{ $class->code }} • Học kỳ: {{ $class->semester }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-surface-container px-4 py-2 text-sm font-medium text-on-surface hover:bg-surface-container-high transition-colors" onclick="navigator.clipboard.writeText('{{ url('/student/join-class?code=' . $class->code) }}'); alert('Đã chép link tham gia lớp!');">
                <x-user.icon name="share" :size="16" />
                Chia sẻ
            </button>
            <a href="{{ route('lecturer.classes.settings', $class->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-surface-container px-4 py-2 text-sm font-medium text-on-surface hover:bg-surface-container-high transition-colors">
                <x-user.icon name="settings" :size="16" />
                Cài đặt lớp
            </a>
            <a href="{{ route('managed-classes') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary hover:bg-primary/90 transition-colors">
                <x-user.icon name="arrow-left" :size="16" />
                Trở về danh sách
            </a>
        </div>
    </div>

    <div class="flex flex-col gap-6 lg:flex-row">
        <!-- Sidebar -->
        <div class="flex flex-col gap-6 lg:w-1/3 xl:w-1/4">
            <!-- Mã tham gia -->
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-on-surface">Mã tham gia lớp</h3>
                    <div class="flex gap-1">
                        <button type="button" class="rounded-full p-2 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors" title="Đổi mã mới">
                            <x-user.icon name="refresh-cw" :size="18" />
                        </button>
                        <button type="button" class="rounded-full p-2 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors" title="Sao chép mã" onclick="navigator.clipboard.writeText('{{ $class->code }}'); alert('Đã copy mã lớp!');">
                            <x-user.icon name="copy" :size="18" />
                        </button>
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <div class="rounded-2xl bg-primary/10 px-2 py-4 font-mono text-xl sm:text-2xl font-black tracking-wide text-primary whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $class->code }}">{{ $class->code }}</div>
                </div>
                <p class="mt-4 text-center text-xs text-on-surface-variant">Gửi mã này cho sinh viên để tham gia lớp học.</p>
            </div>
            
            <!-- Thông tin lớp -->
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20">
                <h3 class="mb-4 font-bold text-on-surface">Thông tin lớp học</h3>
                <div class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Tên lớp</span>
                        <span class="font-semibold text-on-surface text-right max-w-[60%]">{{ $class->name }}</span>
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
            <!-- Import Sinh Viên -->
            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:flex-row sm:items-center sm:justify-between">
                 <div class="flex items-center gap-4">
                     <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary">
                         <x-user.icon name="users" :size="24" />
                     </div>
                     <div>
                         <h3 class="font-bold text-on-surface">Danh sách sinh viên</h3>
                         <p class="text-sm text-on-surface-variant">Quản lý và thêm sinh viên vào lớp học.</p>
                     </div>
                 </div>
                 <div class="flex shrink-0 gap-2">
                     <button type="button" @click="showImportModal = true" class="inline-flex items-center gap-2 rounded-lg bg-surface-container px-4 py-2 text-sm font-medium text-on-surface transition-colors hover:bg-surface-container-high">
                         <x-user.icon name="upload" :size="16" />
                         Import danh sách
                     </button>
                     <a href="{{ route('lecturer.students.index', ['class_id' => $class->id]) }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-on-primary transition-colors hover:bg-primary/90">
                         Xem chi tiết
                     </a>
                 </div>
            </div>
            
            <!-- Tổng quan -->
            <div class="flex-1 rounded-3xl bg-white p-12 text-center shadow-sm ring-1 ring-outline-variant/20">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-surface-container-low">
                    <x-user.icon name="layout" :size="32" class="text-on-surface-variant" />
                </div>
                <h3 class="text-xl font-bold text-on-surface">Tổng quan lớp học</h3>
                <p class="mx-auto mt-2 max-w-md text-on-surface-variant">Nơi hiển thị các thông báo, lịch sử điểm danh gần đây và tình hình hoạt động chung của lớp học.</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions / Stats -->
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Điểm danh QR -->
        <a href="{{ route('lecturer.attendance.qr.create', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-primary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                <x-user.icon name="qr-code" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Điểm danh QR</span>
        </a>

        <!-- Điểm danh Thủ công -->
        <a href="{{ route('lecturer.attendance.manual.create', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-tertiary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-tertiary/10 text-tertiary transition-colors group-hover:bg-tertiary group-hover:text-white">
                <x-user.icon name="check-square" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Điểm danh Thủ công</span>
        </a>

        <!-- Đơn xin nghỉ -->
        <a href="{{ route('lecturer.leave-requests.index', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-[#F59E0B]/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F59E0B]/10 text-[#F59E0B] transition-colors group-hover:bg-[#F59E0B] group-hover:text-white">
                <x-user.icon name="file-text" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Đơn xin nghỉ</span>
        </a>

        <!-- Thống kê -->
        <a href="{{ route('lecturer.class.statistics', ['class_id' => $class->id]) }}" class="group flex flex-col items-center justify-center gap-3 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-lg hover:ring-secondary/20">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                <x-user.icon name="bar-chart" :size="24" />
            </div>
            <span class="font-bold text-on-surface">Thống kê lớp học</span>
        </a>
    </div>

    <!-- Import Modal -->
    <template x-teleport="body">
        <div x-show="showImportModal" style="display: none;" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div x-show="showImportModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity"></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showImportModal" @click.away="showImportModal = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative overflow-hidden rounded-3xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                        <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                            <button type="button" @click="showImportModal = false" class="rounded-full p-2 text-on-surface-variant hover:bg-surface-container hover:text-on-surface focus:outline-none focus:ring-2 focus:ring-primary">
                                <span class="sr-only">Đóng</span>
                                <x-user.icon name="x" :size="20" />
                            </button>
                        </div>
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                                <x-user.icon name="upload" :size="20" class="text-primary" />
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-xl font-bold leading-6 text-on-surface" id="modal-title">Import danh sách</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-on-surface-variant">
                                        Tải lên tệp Excel hoặc CSV. <a href="#" class="font-bold text-primary hover:underline">Mẫu file</a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-5 w-full">
                            <label for="file-upload" class="relative flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-outline-variant/50 bg-surface-container-lowest py-6 hover:bg-surface-container-low transition-colors">
                                <x-user.icon name="file-text" :size="28" class="mb-2 text-on-surface-variant" />
                                <span class="text-sm font-medium text-on-surface">Nhấn chọn file</span>
                                <input id="file-upload" name="file-upload" type="file" class="sr-only" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            </label>
                        </div>

                        <div class="mt-6 sm:mt-8 sm:flex sm:flex-row-reverse">
                            <button type="button" class="inline-flex w-full justify-center rounded-full bg-primary px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:ml-3 sm:w-auto transition-colors">
                                Import
                            </button>
                            <button type="button" @click="showImportModal = false" class="mt-3 inline-flex w-full justify-center rounded-full bg-white px-6 py-2.5 text-sm font-bold text-on-surface shadow-sm ring-1 ring-inset ring-outline-variant hover:bg-surface-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 sm:mt-0 sm:w-auto transition-colors">
                                Hủy bỏ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    </div>
</div>
