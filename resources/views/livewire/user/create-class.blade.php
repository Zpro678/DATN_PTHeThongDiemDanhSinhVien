@php
    $tabs = [
        ['key' => 'info', 'label' => '1. Thông tin lớp', 'icon' => 'book-open'],
        ['key' => 'attendance', 'label' => '2. Cấu hình điểm danh', 'icon' => 'settings'],
        ['key' => 'warning', 'label' => '3. Cài đặt chuyên cần', 'icon' => 'shield-alert'],
    ];
    $colors = ['#005B4F', '#004ac6', '#5C2E91', '#C62828', '#E65100'];
@endphp

<div class="mx-auto max-w-[1000px] space-y-6 p-4 pb-24 sm:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <section class="flex flex-col justify-between gap-4 rounded-[2rem] border border-outline-variant/10 bg-white p-6 shadow-sm md:flex-row md:items-end">
        <div>
            <h3 class="mb-2 flex items-center gap-2 text-2xl font-bold text-on-surface">
                <x-user.icon name="plus" class="text-primary" />
                Tạo lớp mới
            </h3>
            <p class="text-body-md text-on-surface-variant">Thiết lập thông tin và cấu hình điểm danh cho lớp học của bạn</p>
        </div>
        <div class="flex w-full items-center gap-2 md:w-auto">
            <a href="{{ route('managed-classes') }}" class="flex-1 rounded-full px-6 py-3 text-center font-bold text-on-surface transition-colors hover:bg-surface-container md:flex-none">Hủy</a>
            <button type="button" class="flex-1 rounded-full border border-primary/30 px-6 py-3 font-bold text-primary transition-colors hover:bg-primary/5 md:flex-none">Lưu nháp</button>
            <button type="button" class="flex flex-1 items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 font-bold text-white transition-all hover:shadow-lg active:scale-95 md:flex-none">
                <x-user.icon name="check-circle" />
                Tạo lớp
            </button>
        </div>
    </section>

    <section class="flex flex-col overflow-hidden rounded-[2rem] border border-outline-variant/10 bg-white shadow-sm md:flex-row">
        <aside class="flex w-full flex-row gap-2 overflow-x-auto border-b border-outline-variant/10 bg-surface-container-lowest p-6 md:w-64 md:flex-col md:border-b-0 md:border-r hide-scrollbar">
            @foreach ($tabs as $tab)
                <button
                    type="button"
                    wire:click="setActiveTab('{{ $tab['key'] }}')"
                    @class([
                        'flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-all md:whitespace-normal',
                        'bg-primary/10 text-primary' => $activeTab === $tab['key'],
                        'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' => $activeTab !== $tab['key'],
                    ])
                >
                    <x-user.icon :name="$tab['icon']" :size="20" @class([
                        'text-primary' => $activeTab === $tab['key'],
                        'text-on-surface-variant' => $activeTab !== $tab['key'],
                    ]) />
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </aside>

        <div class="min-w-0 flex-1 p-6 md:p-8">
            @if ($activeTab === 'info')
                <div class="space-y-6 animate-in fade-in duration-300">
                    <h4 class="mb-6 border-b border-outline-variant/10 pb-4 text-xl font-bold text-on-surface">Thông tin cơ bản</h4>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <label class="space-y-2 md:col-span-2">
                            <span class="block text-sm font-bold text-on-surface">Tên lớp <span class="text-error">*</span></span>
                            <input type="text" placeholder="VD: Lập trình Web Frontend - Nhóm 1" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Tên môn học</span>
                            <input type="text" placeholder="VD: Lập trình Web" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Mã học phần</span>
                            <input type="text" placeholder="VD: WEB301" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Học kỳ</span>
                            <select class="w-full cursor-pointer appearance-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option>Học kỳ 1</option>
                                <option>Học kỳ 2</option>
                                <option>Học kỳ 3 (Hè)</option>
                            </select>
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Năm học</span>
                            <select class="w-full cursor-pointer appearance-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option>2025-2026</option>
                                <option>2024-2025</option>
                            </select>
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Tổng số buổi dự kiến</span>
                            <input type="number" placeholder="VD: 15" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>

                        <label class="space-y-2">
                            <span class="block text-sm font-bold text-on-surface">Tổng số tiết / Tín chỉ</span>
                            <input type="text" placeholder="VD: 45 / 3" class="w-full rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </label>

                        <label class="space-y-2 md:col-span-2">
                            <span class="block text-sm font-bold text-on-surface">Mô tả lớp</span>
                            <textarea rows="3" placeholder="Mô tả ngắn gọn về lớp học..." class="w-full resize-none rounded-xl border border-outline-variant/30 bg-surface-container-lowest px-4 py-3 transition-all focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></textarea>
                        </label>

                        <div class="space-y-2 md:col-span-2">
                            <p class="text-sm font-bold text-on-surface">Màu đại diện</p>
                            <div class="flex gap-3">
                                @foreach ($colors as $color)
                                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-transparent transition-transform hover:scale-110" style="background-color: {{ $color }}">
                                        @if ($color === '#005B4F')
                                            <x-user.icon name="check-circle" class="text-white" />
                                        @endif
                                    </button>
                                @endforeach
                                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-dashed border-outline-variant text-outline-variant transition-colors hover:bg-surface-container hover:text-on-surface">
                                    <x-user.icon name="image" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="button" wire:click="setActiveTab('attendance')" class="rounded-full bg-primary px-6 py-2.5 font-bold text-white">Tiếp theo</button>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'attendance')
                <div class="space-y-6 animate-in fade-in duration-300">
                    <h4 class="mb-6 border-b border-outline-variant/10 pb-4 text-xl font-bold text-on-surface">Cấu hình điểm danh mặc định</h4>

                    <div class="space-y-4">
                        <button type="button" wire:click="toggleQr" class="flex w-full items-center justify-between rounded-2xl border border-outline-variant/20 p-4 text-left transition-colors hover:bg-surface-container-lowest">
                            <span class="flex items-center gap-4">
                                <span @class([
                                    'rounded-xl p-2',
                                    'bg-primary/10 text-primary' => $qrEnabled,
                                    'bg-surface-container text-on-surface-variant' => ! $qrEnabled,
                                ])>
                                    <x-user.icon name="qr-code" :size="24" />
                                </span>
                                <span>
                                    <span class="block font-bold text-on-surface">Điểm danh bằng quét mã QR</span>
                                    <span class="block text-sm text-on-surface-variant">Sinh viên dùng app để quét mã QR do ứng dụng trên web hiển thị</span>
                                </span>
                            </span>
                            <span @class([
                                'relative h-6 w-12 rounded-full transition-colors',
                                'bg-primary' => $qrEnabled,
                                'bg-surface-variant' => ! $qrEnabled,
                            ])>
                                <span @class([
                                    'absolute top-1 h-4 w-4 rounded-full bg-white transition-all',
                                    'left-7' => $qrEnabled,
                                    'left-1' => ! $qrEnabled,
                                ])></span>
                            </span>
                        </button>

                        <button type="button" wire:click="toggleManual" class="flex w-full items-center justify-between rounded-2xl border border-outline-variant/20 p-4 text-left transition-colors hover:bg-surface-container-lowest">
                            <span class="flex items-center gap-4">
                                <span @class([
                                    'rounded-xl p-2',
                                    'bg-primary/10 text-primary' => $manualEnabled,
                                    'bg-surface-container text-on-surface-variant' => ! $manualEnabled,
                                ])>
                                    <x-user.icon name="check-square" :size="24" />
                                </span>
                                <span>
                                    <span class="block font-bold text-on-surface">Cho phép điểm danh thủ công</span>
                                    <span class="block text-sm text-on-surface-variant">Chủ lớp có quyền tích tay điểm danh trên danh sách</span>
                                </span>
                            </span>
                            <span @class([
                                'relative h-6 w-12 rounded-full transition-colors',
                                'bg-primary' => $manualEnabled,
                                'bg-surface-variant' => ! $manualEnabled,
                            ])>
                                <span @class([
                                    'absolute top-1 h-4 w-4 rounded-full bg-white transition-all',
                                    'left-7' => $manualEnabled,
                                    'left-1' => ! $manualEnabled,
                                ])></span>
                            </span>
                        </button>

                        <div class="overflow-hidden rounded-2xl border border-outline-variant/20">
                            <button type="button" wire:click="toggleGps" class="flex w-full items-center justify-between p-4 text-left transition-colors hover:bg-surface-container-lowest">
                                <span class="flex items-center gap-4">
                                    <span @class([
                                        'rounded-xl p-2',
                                        'bg-tertiary/10 text-tertiary' => $gpsEnabled,
                                        'bg-surface-container text-on-surface-variant' => ! $gpsEnabled,
                                    ])>
                                        <x-user.icon name="map-pin" :size="24" />
                                    </span>
                                    <span>
                                        <span class="block font-bold text-on-surface">Bật xác thực vị trí (GPS)</span>
                                        <span class="block text-sm text-on-surface-variant">Yêu cầu sinh viên phải ở gần vị trí điểm danh khi quét QR</span>
                                    </span>
                                </span>
                                <span @class([
                                    'relative h-6 w-12 rounded-full transition-colors',
                                    'bg-tertiary' => $gpsEnabled,
                                    'bg-surface-variant' => ! $gpsEnabled,
                                ])>
                                    <span @class([
                                        'absolute top-1 h-4 w-4 rounded-full bg-white transition-all',
                                        'left-7' => $gpsEnabled,
                                        'left-1' => ! $gpsEnabled,
                                    ])></span>
                                </span>
                            </button>

                            @if ($gpsEnabled)
                                <div class="border-t border-outline-variant/20 bg-surface-container-lowest p-4 animate-in slide-in-from-top-2">
                                    <label class="space-y-2">
                                        <span class="flex justify-between text-sm font-bold text-on-surface">
                                            <span>Khoảng cách cho phép (bán kính)</span>
                                            <span class="text-tertiary">50 mét</span>
                                        </span>
                                        <input type="range" min="10" max="200" value="50" class="w-full accent-tertiary">
                                        <span class="flex justify-between text-xs text-on-surface-variant">
                                            <span>10m</span>
                                            <span>200m</span>
                                        </span>
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" wire:click="setActiveTab('info')" class="rounded-lg px-4 py-2 font-bold text-on-surface-variant hover:bg-surface-container">Quay lại</button>
                        <button type="button" wire:click="setActiveTab('warning')" class="rounded-full bg-primary px-6 py-2.5 font-bold text-white">Tiếp theo</button>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'warning')
                <div class="space-y-6 animate-in fade-in duration-300">
                    <h4 class="mb-6 border-b border-outline-variant/10 pb-4 text-xl font-bold text-on-surface">Cài đặt chuyên cần & Cảnh báo</h4>

                    <div class="space-y-6">
                        <div>
                            <h5 class="mb-4 flex items-center gap-2 font-bold text-on-surface">
                                <x-user.icon name="clock" :size="18" class="text-secondary" />
                                Cài đặt thời gian đi trễ
                            </h5>
                            <div class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest p-5">
                                <div class="max-w-sm space-y-4">
                                    <label class="block text-sm font-medium text-on-surface">Đánh dấu "Đi trễ" khi quét QR sau khi mở lớp:</label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" value="15" class="w-20 rounded-xl border border-outline-variant/30 bg-white px-4 py-2.5 text-center text-lg font-bold focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        <span class="font-medium text-on-surface-variant">phút</span>
                                    </div>
                                    <p class="text-xs text-on-surface-variant">Sau khoảng thời gian này, hệ thống sẽ tự động ghi nhận là "Vắng". Chủ lớp vẫn có thể cập nhật tay.</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h5 class="mb-4 flex items-center gap-2 font-bold text-on-surface">
                                <x-user.icon name="alert-triangle" :size="18" class="text-error" />
                                Ngưỡng cảnh báo chuyên cần
                            </h5>
                            <div class="space-y-5 rounded-2xl border border-outline-variant/30 bg-surface-container-lowest p-5">
                                <label class="space-y-2">
                                    <span class="flex justify-between text-sm font-bold text-on-surface">
                                        <span>Tỷ lệ vắng tối đa cho phép</span>
                                        <span class="text-error">20%</span>
                                    </span>
                                    <input type="range" min="10" max="50" value="20" class="w-full accent-error">
                                    <span class="block text-xs text-on-surface-variant">Hệ thống sẽ hiển thị cảnh báo đỏ trên ứng dụng của sinh viên khi tổng số buổi vắng đạt mức này.</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl border border-primary/20 bg-primary/5 p-4">
                            <x-user.icon name="check-circle" class="mt-0.5 text-primary" />
                            <div>
                                <h5 class="text-sm font-bold text-primary">Sẵn sàng tạo lớp</h5>
                                <p class="mt-1 text-sm text-on-surface-variant">Sau khi tạo thành công, hệ thống sẽ cấp một <strong class="text-on-surface">Mã tham gia lớp</strong> để bạn chia sẻ cho sinh viên đăng ký vào lớp.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between pt-4">
                        <button type="button" wire:click="setActiveTab('attendance')" class="rounded-lg px-4 py-2 font-bold text-on-surface-variant hover:bg-surface-container">Quay lại</button>
                        <button type="button" class="flex items-center gap-2 rounded-full bg-primary px-6 py-2.5 font-bold text-white">
                            <x-user.icon name="check-circle" :size="18" />
                            Tạo lớp ngay
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
