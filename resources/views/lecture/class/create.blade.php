<x-app-layout variant="lecturer" page-title="Thêm lớp học">
    <div class="font-sans" x-data="{
        name: '{{ old('name') }}',
        code: '{{ old('code') }}',
        semester: '{{ old('semester') }}',
        total_sessions: {{ old('total_sessions', 15) }},
        total_lessons: {{ old('total_lessons', 45) }},
        gps_radius: {{ old('gps_radius', 100) }},
        absence_threshold: {{ old('absence_threshold', 20) }},
        require_join_approval: {{ old('require_join_approval', 0) }}
    }">
        <div class="mb-6 flex items-center gap-3">
            <div>
                <h1 class="text-[24px] font-extrabold text-slate-900 leading-none uppercase tracking-tight">Thêm lớp học mới</h1>
                <p class="text-[14px] text-slate-500 mt-1">Khởi tạo thông tin lớp học và các cấu hình điểm danh.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="font-bold text-sm">Vui lòng kiểm tra lại các thông tin bên dưới:</span>
                </div>
                <ul class="list-disc pl-5 text-[13px] space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('classes.store') }}">
            @csrf
            <input type="hidden" name="require_join_approval" :value="require_join_approval ? 1 : 0">

            <div class="space-y-6">
                <!-- Thông tin cơ bản -->
                <div class="bg-white border border-slate-200/80 rounded-[20px] p-6 sm:p-8 shadow-sm">
                    <h2 class="text-[18px] font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Thông tin cơ bản
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Tên lớp <span class="text-red-500">*</span></label>
                            <input x-model="name" type="text" name="name" placeholder="Ví dụ: Công nghệ phần mềm 1" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Mã môn học <span class="text-red-500">*</span></label>
                            <input x-model="code" type="text" name="code" placeholder="Ví dụ: INT3110" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required>
                        </div>
                        
                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Học kỳ</label>
                            <input x-model="semester" type="text" name="semester" placeholder="Ví dụ: HK1 2024-2025" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-2 sm:col-span-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Mô tả</label>
                            <textarea name="description" placeholder="Nhập mô tả thêm về lớp học..." rows="3" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-3 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Cấu hình điểm danh -->
                <div class="bg-white border border-slate-200/80 rounded-[20px] p-6 sm:p-8 shadow-sm">
                    <h2 class="text-[18px] font-bold text-slate-900 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Cấu hình lớp học
                    </h2>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Tổng số buổi <span class="text-red-500">*</span></label>
                            <input x-model="total_sessions" type="number" name="total_sessions" min="1" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Tổng số tiết <span class="text-red-500">*</span></label>
                            <input x-model="total_lessons" type="number" name="total_lessons" min="1" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all" required>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Bán kính điểm danh GPS (mét)</label>
                            <input x-model="gps_radius" type="number" name="gps_radius" min="10" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-2">
                            <label class="text-[13px] font-semibold text-slate-700 block">Ngưỡng cảnh báo vắng (%)</label>
                            <input x-model="absence_threshold" type="number" name="absence_threshold" min="1" max="100" class="w-full bg-white border border-slate-200 hover:border-slate-300 rounded-xl px-4 py-2.5 text-[14px] font-medium text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <label class="text-[14px] font-bold text-slate-900 block">Yêu cầu duyệt tham gia</label>
                            <p class="text-[13px] text-slate-500 mt-0.5">Học viên tham gia bằng mã lớp cần được duyệt trước khi vào danh sách.</p>
                        </div>
                        <button type="button" @click="require_join_approval = !require_join_approval" class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none" :class="require_join_approval ? 'bg-blue-600' : 'bg-slate-200'">
                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" :class="require_join_approval ? 'translate-x-5' : 'translate-x-0'"></span>
                        </button>
                    </div>
                </div>

                <!-- Thao tác -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="/lecturer/courses" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-[14px] font-semibold hover:bg-slate-50 transition-colors">
                        Hủy
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-[14px] font-semibold hover:bg-blue-700 transition-colors shadow-sm shadow-blue-600/20">
                        Lưu lớp học
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
