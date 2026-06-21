<div class="mx-auto max-w-4xl p-4 sm:p-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-bold text-on-surface md:text-3xl">
                <x-user.icon name="settings" class="text-primary" :size="28" />
                Cài đặt lớp học
            </h2>
            <p class="mt-2 text-body-lg text-on-surface-variant">Chỉnh sửa thông tin và thiết lập cho lớp {{ $courseClass->code }}</p>
        </div>
        <a href="{{ route('managed-classes', ['ma_user' => auth()->id()]) }}" class="flex items-center gap-2 rounded-full border border-outline-variant/30 bg-white px-4 py-2 font-bold text-on-surface-variant transition-colors hover:bg-surface-container-low hover:text-on-surface">
            <x-user.icon name="arrow-left" :size="18" />
            Quay lại
        </a>
    </div>

    @if (session('status'))
        <div class="mb-6 flex items-center rounded-2xl border border-green-200 bg-green-50 p-4 font-bold text-green-700">
            <x-user.icon name="check-circle" :size="20" class="mr-3 text-green-500" />
            {{ session('status') }}
        </div>
    @endif

    {{-- ── Mã lớp nổi bật ───────────────────────────────────────────────── --}}
    <div x-data="{ copied: false }" class="mb-6 overflow-hidden rounded-[2rem] border border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10 shadow-sm">
        <div class="flex flex-col items-center gap-4 p-6 sm:flex-row sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-primary/70">Mã tham gia lớp</p>
                <div class="mt-2 flex items-center gap-3">
                    <span
                        :class="copied ? 'text-green-600' : 'text-primary'"
                        class="font-mono text-3xl font-black tracking-wider transition-colors sm:text-4xl"
                    >{{ $code }}</span>
                </div>
                <p class="mt-1 text-xs text-on-surface-variant">Gửi mã này cho sinh viên để họ tham gia lớp học.</p>
            </div>
            <div class="flex shrink-0 flex-col gap-2 sm:items-end">
                {{-- Nút Copy mã --}}
                <button
                    type="button"
                    id="copy-class-code-btn"
                    :class="copied ? 'bg-green-100 text-green-700 border-green-300' : 'bg-white text-on-surface border-outline-variant/30 hover:bg-surface-container-low hover:border-primary/40 hover:text-primary'"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold shadow-sm transition-all"
                    x-on:click="navigator.clipboard.writeText('{{ $code }}'); copied = true; setTimeout(() => copied = false, 2500)"
                >
                    <template x-if="!copied">
                        <x-user.icon name="copy" :size="16" />
                    </template>
                    <template x-if="copied">
                        <x-user.icon name="check-circle" :size="16" />
                    </template>
                    <span x-text="copied ? 'Đã sao chép!' : 'Sao chép mã'"></span>
                </button>

                {{-- Nút Đổi mã lớp --}}
                <button
                    type="button"
                    id="regen-class-code-btn"
                    wire:click="confirmRegenCode"
                    class="flex items-center gap-2 rounded-xl border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-bold text-orange-700 shadow-sm transition-all hover:bg-orange-100"
                >
                    <x-user.icon name="refresh-cw" :size="16" />
                    Đổi mã lớp
                </button>
            </div>
        </div>
    </div>

    <div class="rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm overflow-hidden">
        <form wire:submit="save">
            <div class="p-6 md:p-10 space-y-8">
                <!-- Thông tin chung -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Thông tin chung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tên lớp / môn học <span class="text-error">*</span></span>
                            <input type="text" wire:model="name" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập tên môn học">
                            @error('name') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mã môn học (Tùy chọn)</span>
                            <input type="text" wire:model="subjectCode" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none uppercase transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: WEB301">
                            @error('subjectCode') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Học kỳ (Tùy chọn)</span>
                            <input type="text" wire:model="semester" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="VD: HK2 2025-2026">
                            @error('semester') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="block col-span-1 md:col-span-2">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Trạng thái lớp</span>
                            <select wire:model="status" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                                <option value="active">Đang hoạt động</option>
                                <option value="ended">Đã kết thúc</option>
                            </select>
                            @error('status') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                        
                        <label class="col-span-1 md:col-span-2 block">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Mô tả lớp học (Tùy chọn)</span>
                            <textarea wire:model="description" class="h-24 w-full resize-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20" placeholder="Nhập mô tả..."></textarea>
                            @error('description') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <!-- Cấu hình điểm danh -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Cấu hình điểm danh</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Tổng số tiết <span class="text-error">*</span></span>
                            <input type="number" wire:model="totalLessons" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20">
                            @error('totalLessons') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <!-- Cấu hình định vị GPS mặc định -->
                <div x-data="{
                    gpsEnabled: @entangle('gpsEnabled').live,
                    gpsLatitude: @entangle('gpsLatitude'),
                    gpsLongitude: @entangle('gpsLongitude'),
                    fetchLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.gpsLatitude = position.coords.latitude;
                                    this.gpsLongitude = position.coords.longitude;
                                },
                                (error) => {
                                    console.warn('Cannot get location', error);
                                    alert('Không thể lấy tọa độ GPS. Vui lòng cấp quyền vị trí cho trình duyệt.');
                                    this.gpsEnabled = false;
                                }
                            );
                        } else {
                            alert('Trình duyệt của bạn không hỗ trợ định vị.');
                            this.gpsEnabled = false;
                        }
                    }
                }">
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Định vị GPS mặc định</h3>
                    
                    <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-4 mb-4">
                        <div>
                            <span class="block text-sm font-bold text-on-surface">Kích hoạt xác minh vị trí GPS mặc định</span>
                            <span class="text-xs text-on-surface-variant">Khi tạo buổi học QR, hệ thống sẽ sử dụng vị trí GPS này làm mặc định để đối chiếu khoảng cách của sinh viên.</span>
                        </div>
                        <button 
                            type="button" 
                            @click="gpsEnabled = !gpsEnabled; if(gpsEnabled) fetchLocation();"
                            class="relative h-6 w-12 rounded-full transition-colors"
                            :class="gpsEnabled ? 'bg-primary' : 'bg-outline-variant/50'"
                        >
                            <span class="absolute top-1 h-4 w-4 rounded-full bg-white transition-all" :class="gpsEnabled ? 'right-1' : 'left-1'"></span>
                        </button>
                    </div>

                    <div x-show="gpsEnabled" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6 rounded-2xl border border-outline-variant/20 bg-slate-50/50 p-5">
                        <div class="md:col-span-2 flex items-center justify-between gap-3">
                            <span class="text-sm font-bold text-on-surface">Tọa độ địa lý mặc định</span>
                            <button type="button" @click="fetchLocation()" class="inline-flex items-center gap-2 rounded-xl bg-white border border-outline-variant/30 px-4 py-2 text-xs font-bold text-on-surface-variant hover:bg-slate-100 hover:text-primary transition-all">
                                <x-user.icon name="map-pin" :size="14" />
                                Lấy tọa độ hiện tại
                            </button>
                        </div>

                        <div>
                            <span class="mb-2 block text-sm font-bold text-on-surface">Vĩ độ (Latitude)</span>
                            <input type="text" wire:model="gpsLatitude" readonly class="w-full rounded-xl border border-outline-variant/30 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 outline-none">
                            @error('gpsLatitude') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <span class="mb-2 block text-sm font-bold text-on-surface">Kinh độ (Longitude)</span>
                            <input type="text" wire:model="gpsLongitude" readonly class="w-full rounded-xl border border-outline-variant/30 bg-slate-100 px-4 py-3 text-sm font-bold text-slate-500 outline-none">
                            @error('gpsLongitude') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <span class="mb-2 block text-sm font-bold text-on-surface">Bán kính GPS mặc định (mét)</span>
                            <div class="flex gap-4 items-center">
                                <input type="number" wire:model="gpsRadius" class="w-24 rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20 text-center font-bold">
                                <span class="text-sm text-on-surface-variant">m (phạm vi cho phép điểm danh xung quanh tọa độ, tối thiểu 5m, tối đa 2500m)</span>
                            </div>
                            @error('gpsRadius') <span class="text-error text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Cấu hình bảo mật -->
                <div>
                    <h3 class="mb-4 text-lg font-bold text-on-surface border-b border-outline-variant/10 pb-2">Bảo mật tham gia</h3>
                    <div class="flex items-center justify-between rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-4">
                        <div>
                            <span class="block text-sm font-bold text-on-surface">Yêu cầu duyệt khi tham gia lớp</span>
                            <span class="text-xs text-on-surface-variant">Sinh viên sẽ phải chờ bạn phê duyệt thay vì được thêm ngay vào danh sách.</span>
                        </div>
                        <button 
                            type="button" 
                            wire:click="$toggle('requireApproval')"
                            class="relative h-6 w-12 rounded-full transition-colors {{ $requireApproval ? 'bg-primary' : 'bg-outline-variant/50' }}"
                        >
                            <span class="absolute top-1 h-4 w-4 rounded-full bg-white transition-all {{ $requireApproval ? 'right-1' : 'left-1' }}"></span>
                        </button>
                    </div>
                </div>

            <!-- Nút lưu -->
            <div class="flex items-center justify-between border-t border-outline-variant/20 bg-surface-container-lowest p-6">
                <div class="text-sm text-on-surface-variant">
                    <span wire:loading wire:target="save">Đang lưu thay đổi...</span>
                </div>
                <div class="flex gap-4">
                    <button type="button" wire:click="save" class="flex items-center gap-2 rounded-xl bg-primary px-6 py-3 font-bold text-white shadow-md shadow-primary/20 transition-all hover:bg-primary-container disabled:opacity-50">
                        <x-user.icon name="save" :size="18" />
                        Lưu thay đổi
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Vùng nguy hiểm --}}
    <div class="mt-8 rounded-[2rem] border border-error/20 bg-error/5 p-6 md:p-10 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-2xl border border-error/10 bg-white p-5">
            <div>
                <h4 class="font-bold text-on-surface">Xóa lớp học</h4>
                <p class="text-xs text-on-surface-variant mt-1">Xóa lớp học khỏi hệ thống quản lý của bạn.</p>
            </div>
            <button 
                type="button" 
                wire:click="confirmDelete"
                class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-error/20 bg-error/10 px-5 py-2.5 font-bold text-error transition-colors hover:bg-error hover:text-white"
            >
                <x-user.icon name="trash-2" :size="18" />
                Xóa lớp học
            </button>
        </div>
    </div>

    {{-- Modal xác nhận ĐỔI MÃ LỚP --}}
    @if ($isConfirmingRegenCode)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl" x-data x-on:click.away="$wire.closeRegenCodeConfirm()">
                    <div class="mb-4 flex items-center gap-3 text-orange-600">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-orange-100">
                            <x-user.icon name="refresh-cw" :size="20" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Đổi mã lớp học?</h3>
                    </div>
                    <div class="rounded-xl border border-orange-200 bg-orange-50 p-4 mb-4">
                        <p class="text-sm font-bold text-orange-800">⚠️ Lưu ý quan trọng</p>
                        <p class="mt-1 text-sm text-orange-700">Mã lớp hiện tại <span class="font-mono font-black">{{ $code }}</span> sẽ bị vô hiệu hóa. Sinh viên đang giữ mã cũ sẽ không thể dùng để tham gia lớp nữa.</p>
                    </div>
                    <p class="text-sm text-on-surface-variant">Hệ thống sẽ tự động sinh một mã lớp mới duy nhất. Bạn cần thông báo mã mới cho sinh viên chưa tham gia.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeRegenCodeConfirm" class="rounded-xl px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-low transition-colors">Hủy</button>
                        <button type="button" wire:click="regenerateCode" class="flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-orange-700 transition-colors">
                            <x-user.icon name="refresh-cw" :size="16" />
                            Xác nhận đổi mã
                        </button>
                    </div>
                </div>
            </div>
        </template>
    @endif

    {{-- Modal xác nhận XÓA --}}
    @if ($isConfirmingDelete)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3 text-error">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-error/10">
                            <x-user.icon name="alert-triangle" :size="20" />
                        </div>
                        <h3 class="text-lg font-bold text-on-surface">Xác nhận xóa lớp học</h3>
                    </div>
                    <p class="text-sm text-on-surface-variant">Bạn có chắc chắn muốn xóa lớp học này không? Mọi thông tin điểm danh có thể sẽ bị vô hiệu hóa.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="closeDeleteConfirm" class="rounded-xl px-5 py-2.5 text-sm font-bold text-on-surface-variant hover:bg-surface-container-low transition-colors">Hủy</button>
                        <button type="button" wire:click="deleteClass" class="rounded-xl bg-error px-5 py-2.5 text-sm font-bold text-white hover:bg-error/90 transition-colors">Xóa lớp học</button>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
