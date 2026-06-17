<x-app-layout variant="student" pageTitle="Đơn xin nghỉ học">
    {{-- Main Content Wrapper --}}
    <div class="w-full max-w-[1440px] mx-auto p-4 md:p-8 animate-in fade-in duration-300">
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Đơn xin nghỉ học</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Quản lý và gửi các yêu cầu xin nghỉ phép học phần của bạn.</p>
        </div>

        {{-- Success/Error Message --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400 rounded-xl text-sm font-semibold flex items-center gap-2">
                <x-sams.icon name="shield-check" class="h-5 w-5 shrink-0 text-emerald-600" />
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Col: Form -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Tạo đơn mới
                    </h3>
                    <form action="{{ route('student.leaves.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Môn học / Lớp</label>
                            <select id="class-select" name="class_id" class="w-full p-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-350 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                <option disabled selected value="">Chọn môn học/lớp học...</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('class_id')
                                <p class="text-rose-500 text-[10px] mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Buổi học xin nghỉ</label>
                            <select id="session-select" name="class_session_id" disabled class="w-full p-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-350 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                <option disabled selected value="">Chọn môn học trước...</option>
                            </select>
                            @error('class_session_id')
                                <p class="text-rose-500 text-[10px] mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Lý do nghỉ</label>
                            <textarea name="reason" class="w-full p-2.5 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-350 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all resize-none" placeholder="Nhập lý do chi tiết..." rows="4">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-rose-500 text-[10px] mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">Minh chứng (Giấy khám bệnh, v.v.)</label>
                            <div onclick="document.getElementById('proof-file-input').click()" class="border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center hover:bg-slate-50 dark:hover:bg-slate-950/30 transition-colors cursor-pointer group">
                                <input type="file" id="proof-file-input" name="proof_image" class="hidden" accept="image/*,.pdf" onchange="updateFileNameDisplay(this)">
                                <svg id="upload-icon" class="w-8 h-8 text-slate-300 dark:text-slate-700 mx-auto mb-2 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                
                                {{-- Preview Container --}}
                                <div id="preview-container" class="hidden mt-2 mb-4">
                                    <img id="image-preview" class="max-h-40 mx-auto rounded-lg object-contain shadow-sm border border-slate-200 dark:border-slate-800" src="" />
                                    <div id="pdf-preview" class="hidden flex flex-col items-center justify-center gap-2 p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl">
                                        <svg class="w-10 h-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span id="pdf-name" class="text-xs font-semibold text-slate-700 dark:text-slate-350 truncate max-w-[200px]">document.pdf</span>
                                    </div>
                                </div>

                                <p id="file-upload-text" class="text-xs text-slate-500 dark:text-slate-400">Kéo thả file vào đây hoặc <span class="text-blue-600 font-bold">Chọn file</span></p>
                                <p class="text-[10px] text-slate-400 mt-1">PDF, JPG, PNG (Tối đa 5MB)</p>
                            </div>
                            @error('proof_image')
                                <p class="text-rose-500 text-[10px] mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 font-bold text-xs transition-colors shadow-sm cursor-pointer">
                            Gửi yêu cầu
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Col: History List -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm p-6 flex flex-col h-full min-h-[500px]">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <x-sams.icon name="history" class="text-blue-600" />
                     {{-- Desktop Table View --}}
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 text-xs uppercase tracking-wider font-bold">
                                    <th class="pb-3 pl-2 font-bold select-none">Môn học</th>
                                    <th class="pb-3 font-bold select-none">Buổi học</th>
                                    <th class="pb-3 font-bold select-none">Lý do</th>
                                    <th class="pb-3 font-bold select-none">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100 dark:divide-slate-800/50">
                                @forelse($leaveRequests as $req)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors">
                                        <td class="py-4 pl-2 text-slate-900 dark:text-white font-bold leading-tight">
                                            {{ $req->classSession->courseClass->name ?? 'N/A' }}<br>
                                            <span class="text-slate-405 dark:text-slate-500 text-[10px] font-mono font-bold uppercase leading-none block mt-1">
                                                {{ $req->classSession->courseClass->code ?? '' }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-slate-600 dark:text-slate-350 font-semibold leading-tight">
                                            {{ $req->classSession->name ?? 'N/A' }}<br>
                                            <span class="text-slate-400 text-[10px] font-mono font-bold leading-none block mt-1">
                                                {{ $req->classSession->date ? $req->classSession->date->format('d/m/Y') : '' }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-slate-500 dark:text-slate-400 max-w-[200px] truncate" title="{{ $req->reason }}">
                                            {{ $req->reason }}
                                        </td>
                                        <td class="py-4">
                                            @if($req->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                                                    Đang chờ duyệt
                                                </span>
                                            @elseif($req->status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">
                                                    Đã duyệt
                                                </span>
                                            @elseif($req->status === 'rejected')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-955/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50" title="{{ $req->rejected_reason }}">
                                                    Từ chối
                                                </span>
                                                @if($req->rejected_reason)
                                                    <span class="block text-[9px] text-rose-500 font-semibold mt-1 max-w-[150px] truncate" title="{{ $req->rejected_reason }}">Lý do: {{ $req->rejected_reason }}</span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center text-slate-400 font-medium select-none">
                                            Chưa có lịch sử đơn xin nghỉ học nào.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Card List View --}}
                    <div class="block lg:hidden space-y-3">
                        @forelse($leaveRequests as $req)
                            <div class="bg-slate-50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 space-y-3">
                                <div class="flex justify-between items-start gap-2">
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-mono font-bold bg-blue-50 dark:bg-blue-955/30 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded uppercase">
                                            {{ $req->classSession->courseClass->code ?? '' }}
                                        </span>
                                        <h4 class="text-xs font-black text-slate-900 dark:text-white mt-1 leading-snug truncate max-w-[200px]" title="{{ $req->classSession->courseClass->name ?? '' }}">
                                            {{ $req->classSession->courseClass->name ?? 'N/A' }}
                                        </h4>
                                    </div>
                                    <div class="shrink-0">
                                        @if($req->status === 'pending')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-50 dark:bg-amber-955/30 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-900/50">
                                                Chờ duyệt
                                            </span>
                                        @elseif($req->status === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-955/30 text-emerald-700 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/50">
                                                Đã duyệt
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-50 dark:bg-rose-955/30 text-rose-700 dark:text-rose-400 border border-rose-100 dark:border-rose-900/50">
                                                Từ chối
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-600 dark:text-slate-400 space-y-1.5 font-semibold leading-relaxed border-t border-slate-100/60 dark:border-slate-800/80 pt-2.5">
                                    <div class="flex justify-between"><span class="text-slate-400 font-medium">Buổi học:</span><span class="text-slate-800 dark:text-slate-200">{{ $req->classSession->name ?? 'N/A' }} ({{ $req->classSession->date ? $req->classSession->date->format('d/m/Y') : '' }})</span></div>
                                    <div class="flex justify-between gap-4"><span class="text-slate-400 font-medium shrink-0">Lý do:</span><span class="text-slate-800 dark:text-slate-200 text-right truncate max-w-[180px]" title="{{ $req->reason }}">{{ $req->reason }}</span></div>
                                    @if($req->status === 'rejected' && $req->rejected_reason)
                                        <div class="flex justify-between gap-4"><span class="text-rose-500 font-bold shrink-0">Lý do từ chối:</span><span class="text-rose-600 dark:text-rose-400 text-right truncate max-w-[180px]" title="{{ $req->rejected_reason }}">{{ $req->rejected_reason }}</span></div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs font-semibold">
                                Chưa có lịch sử đơn xin nghỉ học nào.
                            </div>
                        @endforelse
                    </div>
                    <!-- Pagination -->
                    @if($leaveRequests->total() > 0)
                        <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-500">
                            <span>Hiển thị {{ $leaveRequests->firstItem() ?? 0 }}-{{ $leaveRequests->lastItem() ?? 0 }} trong {{ $leaveRequests->total() }}</span>
                            <div class="flex gap-1">
                                @if($leaveRequests->onFirstPage())
                                    <button class="p-1.5 rounded opacity-50 cursor-not-allowed" disabled>
                                        <x-sams.icon name="chevron-down" class="h-4.5 w-4.5 shrink-0 rotate-90" />
                                    </button>
                                @else
                                    <a href="{{ $leaveRequests->previousPageUrl() }}" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350">
                                        <x-sams.icon name="chevron-down" class="h-4.5 w-4.5 shrink-0 rotate-90" />
                                    </a>
                                @endif

                                @if($leaveRequests->hasMorePages())
                                    <a href="{{ $leaveRequests->nextPageUrl() }}" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350">
                                        <x-sams.icon name="chevron-down" class="h-4.5 w-4.5 shrink-0 -rotate-90" />
                                    </a>
                                @else
                                    <button class="p-1.5 rounded opacity-50 cursor-not-allowed" disabled>
                                        <x-sams.icon name="chevron-down" class="h-4.5 w-4.5 shrink-0 -rotate-90" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Javascript for dynamic session fetching --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const classSelect = document.getElementById('class-select');
            const sessionSelect = document.getElementById('session-select');

            // Hàm load danh sách buổi học
            function loadSessions(classId, selectedSessionId = null) {
                sessionSelect.innerHTML = '<option disabled selected value="">Đang tải các buổi học...</option>';
                sessionSelect.disabled = true;

                fetch(`/student/classes/${classId}/sessions-json`)
                    .then(response => response.json())
                    .then(data => {
                        sessionSelect.innerHTML = '<option disabled selected value="">Chọn buổi học...</option>';
                        if (data.length === 0) {
                            sessionSelect.innerHTML = '<option disabled selected value="">Lớp này chưa có buổi học nào.</option>';
                        } else {
                            data.forEach(session => {
                                const option = document.createElement('option');
                                option.value = session.id;
                                option.textContent = session.name;
                                if (selectedSessionId == session.id) {
                                    option.selected = true;
                                }
                                sessionSelect.appendChild(option);
                            });
                            sessionSelect.disabled = false;
                        }
                    })
                    .catch(err => {
                        sessionSelect.innerHTML = '<option disabled selected value="">Lỗi tải dữ liệu buổi học.</option>';
                    });
            }

            // Lắng nghe sự kiện đổi class
            classSelect.addEventListener('change', function () {
                loadSessions(this.value);
            });

            // Nếu redirect về có validation error (đã chọn sẵn class)
            if (classSelect.value) {
                const oldSessionId = "{{ old('class_session_id') }}";
                loadSessions(classSelect.value, oldSessionId);
            }
        });

        // Cập nhật tên file hiển thị và xem trước minh chứng
        function updateFileNameDisplay(input) {
            const fileText = document.getElementById('file-upload-text');
            const previewContainer = document.getElementById('preview-container');
            const imagePreview = document.getElementById('image-preview');
            const pdfPreview = document.getElementById('pdf-preview');
            const pdfName = document.getElementById('pdf-name');
            const uploadIcon = document.getElementById('upload-icon');

            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                fileText.innerHTML = 'Đã chọn file: <span class="text-blue-600 font-bold">' + file.name + '</span>';
                
                previewContainer.classList.remove('hidden');
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        pdfPreview.classList.add('hidden');
                        if (uploadIcon) uploadIcon.classList.add('hidden');
                    }
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    imagePreview.classList.add('hidden');
                    pdfPreview.classList.remove('hidden');
                    pdfName.textContent = file.name;
                    if (uploadIcon) uploadIcon.classList.add('hidden');
                } else {
                    previewContainer.classList.add('hidden');
                    if (uploadIcon) uploadIcon.classList.remove('hidden');
                }
            } else {
                fileText.innerHTML = 'Kéo thả file vào đây hoặc <span class="text-blue-600 font-bold">Chọn file</span>';
                previewContainer.classList.add('hidden');
                if (uploadIcon) uploadIcon.classList.remove('hidden');
            }
        }
    </script>
</x-app-layout>
