<x-app-layout>
    <div class="w-full space-y-8 pb-10" x-data="{
        name: '{{ old('name', 'Công nghệ phần mềm SE2201') }}',
        code: '{{ old('code', 'SE2201') }}',
        subjectName: '{{ old('subject_name', 'Công nghệ phần mềm') }}',
        subjectCode: '{{ old('subject_code', 'SE101') }}',
        semester: '{{ old('semester', 'Học kỳ 1') }}',
        schoolYear: '{{ old('school_year', '2025 - 2026') }}',
        totalLessons: {{ old('total_lessons', 45) }},
        totalSessions: {{ old('total_sessions', 15) }},
        selectedDays: [],
        students: [],
        requireApproval: {{ old('require_approval', 0) }},
        toggleDay(day) {
            if (this.selectedDays.includes(day)) {
                this.selectedDays = this.selectedDays.filter(d => d !== day);
            } else {
                this.selectedDays.push(day);
            }
        },
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.name.endsWith('.csv')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const text = e.target.result;
                    const lines = text.split('\n');
                    let parsedStudents = [];
                    for (let i = 1; i < lines.length; i++) {
                        if (!lines[i].trim()) continue;
                        const cols = lines[i].split(',');
                        if (cols.length >= 2) {
                            parsedStudents.push({
                                code: cols[0].replace(/['&quot;]+/g, '').trim(),
                                name: cols[1].replace(/['&quot;]+/g, '').trim(),
                                email: cols[2] ? cols[2].replace(/['&quot;]+/g, '').trim() : '',
                                status: 'Hoạt động'
                            });
                        }
                    }
                    this.students = parsedStudents;
                };
                reader.readAsText(file);
            } else {
                // Mock simulation for Excel upload
                this.students = [
                    { code: '22020101', name: 'Nguyễn Văn Anh', email: 'anh.nv@gmail.com', status: 'Hoạt động' },
                    { code: '22020102', name: 'Trần Thị Bình', email: 'binh.tt@gmail.com', status: 'Hoạt động' },
                    { code: '22020103', name: 'Lê Hoàng Cường', email: 'cuong.lh@gmail.com', status: 'Hoạt động' },
                    { code: '22020104', name: 'Phạm Minh Đức', email: 'duc.pm@gmail.com', status: 'Hoạt động' }
                ];
            }
        }
    }">
        <form method="POST" action="{{ route('classes.store') }}">
            @csrf

            <!-- Hidden input calculated by prepareForValidation in request -->
            <input type="hidden" name="require_approval" :value="requireApproval ? 1 : 0">

            <div>
                <div class="flex items-center gap-2 text-sm font-medium text-slate-500 mb-3">
                    <a class="hover:text-blue-600 transition-colors" href="{{ route('dashboard') }}">Bảng điều khiển</a>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                    <a class="hover:text-blue-600 transition-colors" href="{{ route('classes.index') }}">Lớp học của tôi</a>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right w-4 h-4" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                    <span class="text-slate-900 font-bold dark:text-white">Tạo lớp học</span>
                </div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight mb-1">Tạo lớp học mới</h1>
                <p class="text-slate-500 font-medium text-sm dark:text-slate-400">Tạo lớp học, phân chia lịch và danh sách sinh viên.</p>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span class="font-bold text-sm">Vui lòng kiểm tra lại các thông tin bên dưới:</span>
                    </div>
                    <ul class="list-disc pl-5 text-xs space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6 items-start mt-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 xl:col-span-3 space-y-6">
                    <!-- Class Information Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open w-5 h-5 text-blue-600" aria-hidden="true">
                                <path d="M12 7v14"></path>
                                <path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"></path>
                            </svg>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Thông tin lớp học</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Tên lớp học *</label>
                                <input x-model="name" type="text" name="name" placeholder="Công nghệ phần mềm SE2201" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Mã lớp học *</label>
                                <input x-model="code" type="text" name="code" placeholder="SE2201" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Tên môn học *</label>
                                <input x-model="subjectName" type="text" name="subject_name" placeholder="Công nghệ phần mềm" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Mã môn học</label>
                                <input x-model="subjectCode" type="text" name="subject_code" placeholder="SE101" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400">
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Học kỳ *</label>
                                <div class="relative">
                                    <select x-model="semester" name="semester" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                        <option value="Học kỳ 1">Học kỳ 1</option>
                                        <option value="Học kỳ 2">Học kỳ 2</option>
                                        <option value="Học kỳ 3">Học kỳ 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Năm học *</label>
                                <div class="relative">
                                    <select x-model="schoolYear" name="school_year" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none appearance-none transition-all">
                                        <option value="2025 - 2026">2025 - 2026</option>
                                        <option value="2026 - 2027">2026 - 2027</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Tổng số tiết *</label>
                                <input x-model="totalLessons" type="number" name="total_lessons" min="0" placeholder="45" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Tổng số buổi *</label>
                                <input x-model="totalSessions" type="number" name="total_sessions" min="0" placeholder="15" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" required>
                            </div>
                        </div>

                        <!-- Class settings (Approval & Description) -->
                        <div class="mt-6 border-t border-slate-100 dark:border-slate-800 pt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <label class="text-sm font-bold text-slate-900 dark:text-white">Yêu cầu duyệt tham gia</label>
                                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sinh viên tham gia bằng mã lớp cần được bạn phê duyệt trước khi chính thức vào danh sách lớp.</p>
                                </div>
                                <button type="button" @click="requireApproval = !requireApproval" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" :class="requireApproval ? 'bg-blue-600' : 'bg-slate-200 dark:bg-slate-800'">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="requireApproval ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                            </div>

                            <div class="space-y-2 pt-2">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block">Mô tả chi tiết</label>
                                <textarea name="description" placeholder="Nhập mô tả lớp học, thông tin giảng đường, tài liệu học tập..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all placeholder:text-slate-400" rows="3">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Teaching Schedule Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center gap-2 mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar-days w-5 h-5 text-blue-600" aria-hidden="true">
                                <path d="M8 2v4"></path>
                                <path d="M16 2v4"></path>
                                <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                <path d="M3 10h18"></path>
                                <path d="M8 14h.01"></path>
                                <path d="M12 14h.01"></path>
                                <path d="M16 14h.01"></path>
                                <path d="M8 18h.01"></path>
                                <path d="M12 18h.01"></path>
                                <path d="M16 18h.01"></path>
                            </svg>
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Lịch giảng dạy chi tiết</h2>
                        </div>
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest block mb-2">Chọn các ngày giảng dạy trong tuần</label>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="day in ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'Chủ nhật']">
                                        <button type="button" @click="toggleDay(day)" class="px-4 py-2.5 rounded-xl text-sm font-bold transition-all border flex items-center gap-2 cursor-pointer" :class="selectedDays.includes(day) ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-950/40 dark:border-blue-900/60 dark:text-blue-300' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800'">
                                            <span class="w-2 h-2 rounded-full transition-all" :class="selectedDays.includes(day) ? 'bg-blue-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></span>
                                            <span x-text="day"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Cấu hình thời gian & phòng học theo từng ngày</h3>
                                
                                <div x-show="selectedDays.length === 0" class="py-8 text-center bg-slate-50 dark:bg-slate-950/50 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                                    <p class="text-sm font-medium text-slate-400 dark:text-slate-500">Vui lòng chọn ít nhất một ngày dạy để bắt đầu cấu hình chi tiết.</p>
                                </div>

                                <div x-show="selectedDays.length > 0" class="space-y-4" style="display: none;">
                                    <template x-for="day in selectedDays">
                                        <div class="p-4 border border-slate-200 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-950/20 space-y-4">
                                            <div class="flex items-center justify-between">
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="day"></span>
                                                <button type="button" @click="toggleDay(day)" class="text-xs text-red-500 hover:underline">Xóa</button>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="space-y-1">
                                                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Giờ bắt đầu</label>
                                                    <input type="time" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-xs font-medium outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Giờ kết thúc</label>
                                                    <input type="time" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-xs font-medium outline-none">
                                                </div>
                                                <div class="space-y-1">
                                                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">Phòng học</label>
                                                    <input type="text" placeholder="Phòng 402-A1" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg px-3 py-2 text-xs font-medium outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Management Card -->
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                            <div class="flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users w-5 h-5 text-blue-600" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <path d="M16 3.128a4 4 0 0 1 0 7.744"></path>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Quản lý sinh viên</h2>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <a href="{{ route('import.template') }}" class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-xl border focus:ring-slate-500 px-4 py-2 text-sm flex-1 sm:flex-none border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm font-semibold h-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download w-4 h-4 mr-2" aria-hidden="true">
                                        <path d="M12 15V3"></path>
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <path d="m7 10 5 5 5-5"></path>
                                    </svg>
                                    Tải tệp biểu mẫu
                                </a>
                                <button type="button" @click="students.push({ code: '2202' + Math.floor(Math.random()*10000), name: 'Sinh viên mới', email: 'student.new@gmail.com', status: 'Hoạt động' })" class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-xl border focus:ring-slate-500 px-4 py-2 text-sm flex-1 sm:flex-none border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm font-semibold h-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus w-4 h-4 mr-2" aria-hidden="true">
                                        <path d="M5 12h14"></path>
                                        <path d="M12 5v14"></path>
                                    </svg>
                                    Thêm thủ công
                                </button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Drag & Drop Upload Zone -->
                            <div @click="$refs.fileInput.click()" class="border-2 border-dashed border-slate-300 dark:border-slate-800 rounded-2xl p-10 flex flex-col items-center justify-center text-center bg-slate-50 dark:bg-slate-950/30 hover:bg-slate-100 dark:hover:bg-slate-900 transition-colors cursor-pointer group">
                                <input x-ref="fileInput" type="file" class="hidden" accept=".csv,.xlsx,.xls" @change="handleFileUpload($event)">
                                <div class="w-16 h-16 bg-white dark:bg-slate-900 rounded-full flex items-center justify-center shadow-sm mb-4 group-hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-cloud-upload w-8 h-8 text-blue-600" aria-hidden="true">
                                        <path d="M12 13v8"></path>
                                        <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"></path>
                                        <path d="m8 17 4-4 4 4"></path>
                                    </svg>
                                </div>
                                <p class="text-base font-bold text-slate-900 dark:text-white mb-1">Kéo thả hoặc nhấp để tải file Excel lên</p>
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 max-w-xs">Định dạng: .xlsx, .csv. Kích thước tối đa 10MB.</p>
                            </div>

                            <!-- Preview student list -->
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Xem trước danh sách sinh viên</h3>
                                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse min-w-[500px]">
                                            <thead class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
                                                <tr class="text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                                    <th class="px-5 py-3">Mã SV</th>
                                                    <th class="px-5 py-3">Họ và tên</th>
                                                    <th class="px-5 py-3">Email</th>
                                                    <th class="px-5 py-3 text-right">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Empty State -->
                                                <tr x-show="students.length === 0">
                                                    <td colspan="4" class="px-5 py-8 text-center bg-white dark:bg-slate-900">
                                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Chưa có sinh viên nào được tải lên.</p>
                                                    </td>
                                                </tr>
                                                <!-- Row Templates -->
                                                <template x-for="(student, index) in students" :key="index">
                                                    <tr class="border-b border-slate-100 dark:border-slate-800/60 hover:bg-slate-50/50 dark:hover:bg-slate-950/20">
                                                        <td class="px-5 py-3.5 text-sm font-semibold text-slate-900 dark:text-white font-mono" x-text="student.code"></td>
                                                        <td class="px-5 py-3.5 text-sm font-medium text-slate-900 dark:text-white" x-text="student.name"></td>
                                                        <td class="px-5 py-3.5 text-sm font-medium text-slate-500 dark:text-slate-400" x-text="student.email"></td>
                                                        <td class="px-5 py-3.5 text-sm text-right">
                                                            <span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-950/20 px-2.5 py-0.5 text-xs font-bold text-green-700 dark:text-green-400" x-text="student.status"></span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Sticky Summary) -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-[88px]">
                        <div class="p-6 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950">
                            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1">Tóm tắt lớp học</h3>
                            <p class="text-xl font-bold text-slate-900 dark:text-white leading-tight" x-text="name || 'Lớp học mới'"></p>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Học kỳ</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="semester"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Năm học</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="schoolYear"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Tổng số tiết</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="totalLessons + ' tiết'"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Tổng số buổi</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="totalSessions + ' buổi'"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Sinh viên</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white" x-text="students.length"></span>
                            </div>
                            <div class="py-2">
                                <span class="text-sm font-medium text-slate-500 dark:text-slate-400 block mb-1">Lịch học</span>
                                <span class="text-sm font-bold text-slate-900 dark:text-white block" x-text="selectedDays.length > 0 ? selectedDays.join(', ') : 'Chưa cấu hình'"></span>
                            </div>
                        </div>
                        <div class="p-6 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 space-y-3">
                            <button type="submit" class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-xl focus:ring-blue-500 px-4 py-2 text-sm w-full bg-blue-600 hover:bg-blue-700 text-white font-bold h-11 shadow-sm">
                                Tạo lớp học
                            </button>
                            <div class="flex gap-3">
                                <a href="{{ route('classes.index') }}" class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-xl border focus:ring-slate-500 px-4 py-2 text-sm flex-1 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold shadow-sm text-center">
                                    Hủy
                                </a>
                                <button type="button" @click="alert('Đã lưu bản nháp!')" class="inline-flex items-center justify-center transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-xl border focus:ring-slate-500 px-4 py-2 text-sm flex-1 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-semibold shadow-sm">
                                    Lưu nháp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
