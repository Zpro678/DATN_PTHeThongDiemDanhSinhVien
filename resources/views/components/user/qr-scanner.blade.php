{{--
    Bộ quét QR toàn màn hình bằng camera thiết bị.
    Thư viện html5-qrcode được nạp ĐỘNG qua CDN lần đầu mở (không cần build lại Vite).

    Quét QR CHỈ để ĐIỂM DANH: nội dung mã là URL chứa /attendance/check-in/{token}
    -> điều hướng thẳng tới trang điểm danh. Server ([AttendanceCheckIn]) tự xử lý 2 DẠNG:
      • Đã đăng nhập  -> tự động điểm danh.
      • Khách chưa đăng nhập -> hiện form nhập MSSV/email.
    Mã QR không phải link điểm danh -> báo lỗi, không điều hướng.

    Mở bộ quét bằng cách phát sự kiện trình duyệt: $dispatch('open-qr-scanner').
--}}
<div
    x-data="{
        open: false,
        scanning: false,
        loading: false,
        error: '',
        handled: false,
        scanner: null,
        async openScanner() {
            this.open = true;
            this.error = '';
            this.handled = false;
            document.body.style.overflow = 'hidden';
            await this.$nextTick();
            this.startCamera();
        },
        closeScanner() {
            this.stopCamera();
            this.open = false;
            document.body.style.overflow = '';
        },
        ensureLib() {
            if (window.Html5Qrcode) return Promise.resolve();
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
                s.async = true;
                s.onload = () => resolve();
                s.onerror = () => reject(new Error('load-failed'));
                document.head.appendChild(s);
            });
        },
        async startCamera() {
            this.error = '';
            this.loading = true;
            // getUserMedia chỉ chạy trên HTTPS hoặc localhost.
            if (!navigator.mediaDevices || window.isSecureContext === false) {
                this.loading = false;
                this.error = 'Trình duyệt chặn camera vì trang chưa chạy HTTPS (hoặc localhost). Hãy mở web bằng HTTPS rồi thử lại.';
                return;
            }
            try {
                await this.ensureLib();
            } catch (e) {
                this.loading = false;
                this.error = 'Không tải được thư viện quét QR. Kiểm tra kết nối mạng rồi thử lại.';
                return;
            }
            try {
                this.scanner = new window.Html5Qrcode('qr-reader-region', { verbose: false });
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    (decodedText) => this.onDecoded(decodedText),
                    () => {}
                );
                this.scanning = true;
                this.loading = false;
            } catch (e) {
                this.loading = false;
                this.error = 'Không mở được camera. Vui lòng cấp quyền camera cho trình duyệt rồi thử lại.';
            }
        },
        async stopCamera() {
            this.scanning = false;
            if (this.scanner) {
                try { await this.scanner.stop(); } catch (e) {}
                try { this.scanner.clear(); } catch (e) {}
                this.scanner = null;
            }
        },
        onDecoded(text) {
            if (this.handled) return;
            this.handled = true;

            // Dạng 1: QR ĐIỂM DANH (/attendance/check-in/{token}) -> mở trang check-in.
            // Server tự xử lý: đã đăng nhập -> auto điểm danh; khách -> form nhập MSSV/email.
            const url = this.resolveCheckInUrl(text);
            if (url) {
                this.stopCamera();
                window.location.href = url;
                return;
            }

            // Dạng 2: QR THAM GIA LỚP (/student/join-class?code=... hoặc /join/{code}).
            // Mở luôn modal Tham gia lớp ngay tại chỗ (không tải lại trang) với mã đã điền sẵn.
            const joinCode = this.resolveJoinCode(text);
            if (joinCode) {
                this.stopCamera();
                this.open = false;
                document.body.style.overflow = '';
                this.$dispatch('open-join-class-modal', { code: joinCode });
                return;
            }

            // Không nhận diện được -> báo lỗi và tiếp tục quét.
            this.error = 'Mã QR không hợp lệ. Vui lòng quét mã QR điểm danh hoặc mã tham gia lớp do giảng viên cung cấp.';
            this.handled = false;
        },
        resolveCheckInUrl(raw) {
            const text = (raw || '').trim();
            if (!text) return null;
            try {
                const u = new URL(text, window.location.origin);
                const m = u.pathname.match(/\/attendance\/check-in\/([^\/?#]+)/);
                if (m) {
                    // Cùng hệ thống -> điều hướng nội bộ; khác host -> mở nguyên link điểm danh.
                    return (u.origin === window.location.origin) ? (u.pathname + u.search) : text;
                }
            } catch (e) { /* không phải URL */ }
            return null;
        },
        resolveJoinCode(raw) {
            const text = (raw || '').trim();
            if (!text) return null;
            try {
                const u = new URL(text, window.location.origin);
                // /student/join-class?code=XXX
                if (/\/student\/join-class\/?$/.test(u.pathname)) {
                    const code = u.searchParams.get('code');
                    if (code && code.trim() !== '') return code.trim();
                }
                // /join/XXX
                const m = u.pathname.match(/\/join\/([^\/?#]+)/);
                if (m) return decodeURIComponent(m[1]).trim();
            } catch (e) { /* không phải URL */ }
            return null;
        }
    }"
    x-on:open-qr-scanner.window="openScanner()"
    x-cloak
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-[120] flex flex-col bg-slate-950 text-white"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 pt-5 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10">
                    <x-user.icon name="qr-code" :size="20" class="text-white" />
                </span>
                <div>
                    <p class="text-base font-black leading-tight">Quét mã QR</p>
                    <p class="text-[11px] font-medium text-white/60">Điểm danh hoặc tham gia lớp bằng mã QR</p>
                </div>
            </div>
            <button type="button" @click="closeScanner()" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20">
                <x-user.icon name="x" :size="22" />
            </button>
        </div>

        {{-- Vùng camera --}}
        <div class="relative flex flex-1 items-center justify-center overflow-hidden">
            <div id="qr-reader-region" class="h-full w-full [&_video]:h-full [&_video]:w-full [&_video]:object-cover"></div>

            {{-- Khung ngắm --}}
            <template x-if="scanning">
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="relative h-60 w-60 max-w-[70vw] max-h-[70vw]">
                        <span class="absolute -left-1 -top-1 h-8 w-8 rounded-tl-2xl border-l-4 border-t-4 border-white"></span>
                        <span class="absolute -right-1 -top-1 h-8 w-8 rounded-tr-2xl border-r-4 border-t-4 border-white"></span>
                        <span class="absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-2xl border-b-4 border-l-4 border-white"></span>
                        <span class="absolute -bottom-1 -right-1 h-8 w-8 rounded-br-2xl border-b-4 border-r-4 border-white"></span>
                    </div>
                </div>
            </template>

            {{-- Đang tải --}}
            <div x-show="loading" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-slate-950/80">
                <x-user.icon name="loader" :size="32" class="animate-spin text-white/80" />
                <p class="text-sm font-semibold text-white/80">Đang khởi động camera…</p>
            </div>

            {{-- Lỗi --}}
            <div x-show="error" x-cloak class="absolute inset-x-5 top-5">
                <div class="flex items-start gap-3 rounded-2xl border border-rose-400/30 bg-rose-500/15 p-4 text-sm font-semibold text-rose-100 backdrop-blur">
                    <x-user.icon name="alert-triangle" :size="20" class="mt-0.5 shrink-0" />
                    <span x-text="error"></span>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="space-y-3 px-5 pb-8 pt-4">
            <p class="text-center text-xs font-medium text-white/60">Đưa mã QR điểm danh hoặc mã tham gia lớp vào giữa khung để quét tự động</p>
            <button type="button" x-show="error" x-cloak @click="handled = false; startCamera()" class="w-full rounded-2xl bg-white py-3.5 text-sm font-black text-slate-900 transition active:scale-[0.98]">
                Thử lại
            </button>
        </div>
    </div>
</div>
