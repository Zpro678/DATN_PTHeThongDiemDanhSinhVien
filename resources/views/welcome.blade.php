<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AttendanceWeb — Hệ Thống Điểm Danh Học Viên</title>
    <meta name="description" content="Hệ thống quản lý điểm danh thông minh dành cho giảng viên và học viên.">
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @livewireStyles

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    {{-- Alpine được Livewire (@livewireScripts) đóng gói sẵn; không nạp thêm Alpine từ CDN để tránh tải Alpine 2 lần (gây lỗi phải bấm 2 lần). --}}

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; }

        /* ── Hiệu ứng nền ── */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(148,163,184,0.15) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* ── Spotlight Card (CSS kết hợp biến từ Alpine) ── */
        .spotlight-card {
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.4s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.4s ease;
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            top: var(--mouse-y, 50%);
            left: var(--mouse-x, 50%);
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 60%);
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
            pointer-events: none;
        }
        .spotlight-card.is-hovered::before { opacity: 1; }
        .spotlight-card.is-hovered {
            box-shadow: 0 25px 50px -12px rgba(99,102,241,0.15), 0 0 0 1px rgba(99,102,241,0.05);
        }

        /* ── Border Glow ── */
        @property --border-angle { syntax: '<angle>'; initial-value: 0deg; inherits: false; }
        .border-glow::after {
            content: ''; position: absolute; inset: -2px;
            border-radius: inherit;
            background: conic-gradient(from var(--border-angle), transparent 40%, rgba(99,102,241,0.6) 60%, rgba(236,72,153,0.4) 80%, transparent 95%);
            opacity: 0; transition: opacity 0.5s ease; z-index: -1;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            padding: 2px;
        }
        .border-glow:hover::after { opacity: 1; animation: borderSpin 3s linear infinite; }
        @keyframes borderSpin { to { --border-angle: 360deg; } }

        /* ── Ripple ── */
        @keyframes ripple-effect {
            to { transform: scale(4); opacity: 0; }
        }
        .animate-ripple {
            animation: ripple-effect 0.6s ease-out forwards;
        }

        /* ── Shimmer & Scrollbar ── */
        .shimmer-text {
            background-size: 200% auto;
            animation: shimmerSlide 4s ease-in-out infinite;
        }
        @keyframes shimmerSlide {
            0% { background-position: 0% center; }
            50% { background-position: 100% center; }
            100% { background-position: 0% center; }
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    </style>
</head>
<body class="antialiased text-slate-900 bg-[#fafbff] min-h-[100dvh] flex flex-col overflow-x-hidden md:h-screen md:overflow-hidden" x-data="globalParallax()" :style="`--mouse-x: ${cursorX}px; --mouse-y: ${cursorY}px`">
    <div class="fixed inset-0 dot-grid opacity-40 -z-20 pointer-events-none"></div>

    <!-- Grid bắt sáng khi rê chuột (Interactive Mask Grid) -->
    <div class="fixed inset-0 pointer-events-none -z-10"
         style="background-image: radial-gradient(circle, rgba(59, 130, 246, 0.5) 1.5px, transparent 1.5px);
                background-size: 32px 32px;
                mask-image: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y), black, transparent);
                -webkit-mask-image: radial-gradient(400px circle at var(--mouse-x) var(--mouse-y), black, transparent);">
    </div>

    <div class="fixed top-0 left-0 w-[500px] h-[500px] bg-blue-500/15 rounded-full blur-[80px] pointer-events-none -z-15 transition-transform duration-100 ease-out will-change-transform"
         :style="`transform: translate(calc(var(--mouse-x) - 250px), calc(var(--mouse-y) - 250px))`">
    </div>

    <header class="flex items-center justify-between px-6 lg:px-12 py-5 bg-white/70 backdrop-blur-xl sticky top-0 z-50 border-b border-slate-200/50">
        <a href="/" wire:navigate class="flex items-center gap-3 group">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-500/25 transition-all duration-500 ease-out group-hover:rotate-[15deg] group-hover:scale-110">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-slate-800 transition-colors duration-300 group-hover:text-blue-600">
                AttendanceApp
            </span>
        </a>
        
        <nav class="flex items-center gap-3 md:gap-5">
            @auth
                <a href="{{ route('dashboard') }}" wire:navigate class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Bảng điều khiển</a>
                <div x-data="magneticButton()" @mousemove="handleHover" @mouseleave="reset" :style="`transform: translate(${mx}px, ${my}px)`" class="transition-transform duration-300">
                    <button class="px-5 py-2.5 text-sm font-bold text-white bg-slate-900 rounded-full hover:bg-slate-800 transition-colors shadow-md">
                        Tài khoản
                    </button>
                </div>
            @else
                <a href="{{ route('login') }}" wire:navigate class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Đăng nhập</a>
                <a href="{{ route('register') }}" wire:navigate class="px-4 py-2 sm:px-5 sm:py-2.5 text-sm font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 transition-colors shadow-md shadow-blue-500/20">
                    Đăng ký
                </a>
            @endauth
        </nav>
    </header>

    <main class="flex-grow flex flex-col items-center justify-center text-center px-6 relative py-12">

        <section class="max-w-4xl mx-auto w-full z-10 relative space-y-8 sm:space-y-12">
            <h1 class="text-4xl md:text-5xl lg:text-[4.5rem] font-extrabold tracking-tight leading-[1.15]">
                Nơi học tập bắt đầu với<br>
                sự <span class="text-blue-600">kết nối.</span>
            </h1>
            
            <p class="text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed font-normal">
                Hệ thống quản lý điểm danh thông minh giúp giảng viên và học viên tiết kiệm thời gian, theo dõi tiến độ chuyên cần chính xác và tương tác dễ dàng trong mỗi buổi học.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-5 pt-8 w-full px-4 sm:px-0">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate class="w-full sm:w-auto px-6 py-3.5 sm:px-8 sm:py-4 text-base font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 transition-all shadow-md shadow-blue-500/20">
                        Đi đến Bảng điều khiển
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="w-full sm:w-auto px-6 py-3.5 sm:px-8 sm:py-4 text-base font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 transition-all shadow-md shadow-blue-500/20">
                        Đăng nhập ngay
                    </a>
                    <a href="{{ route('register') }}" wire:navigate class="w-full sm:w-auto px-6 py-3.5 sm:px-8 sm:py-4 text-base font-semibold text-slate-700 bg-white border border-slate-200 rounded-full hover:bg-slate-50 hover:border-slate-300 transition-all shadow-sm">
                        Tạo tài khoản mới
                    </a>
                @endauth
            </div>
        </section>


    </main>

    <footer class="bg-transparent border-t border-slate-200/50 py-6 text-center text-sm text-slate-500 w-full z-20">
        <p>&copy; {{ date('Y') }} AttendanceApp. Bảo lưu mọi quyền.</p>
    </footer>

    <script>
        document.addEventListener('alpine:init', () => {
            
            // Theo dõi chuột toàn màn hình cho Blob và Glow
            Alpine.data('globalParallax', () => ({
                mouseX: 0, mouseY: 0,
                cursorX: window.innerWidth / 2, cursorY: window.innerHeight / 2,
                init() {
                    window.addEventListener('mousemove', (e) => {
                        this.cursorX = e.clientX;
                        this.cursorY = e.clientY;
                        this.mouseX = (e.clientX / window.innerWidth) - 0.5;
                        this.mouseY = (e.clientY / window.innerHeight) - 0.5;
                    });
                }
            }));

            // Xử lý hiệu ứng Tilt 3D & Spotlight
            Alpine.data('spotlightCard', (tiltIntensity = 6, maxTilt = 15) => ({
                isHovered: false, mx: 0, my: 0, tiltX: 0, tiltY: 0,
                handleHover(e) {
                    const rect = this.$el.getBoundingClientRect();
                    this.mx = e.clientX - rect.left;
                    this.my = e.clientY - rect.top;
                    const x = (this.mx / rect.width) - 0.5;
                    const y = (this.my / rect.height) - 0.5;
                    this.tiltY = Math.max(Math.min(x * tiltIntensity, maxTilt), -maxTilt);
                    this.tiltX = Math.max(Math.min(-y * tiltIntensity, maxTilt), -maxTilt);
                },
                reset() {
                    this.isHovered = false;
                    this.tiltX = 0; this.tiltY = 0;
                }
            }));

            // Hiệu ứng hút con trỏ (Magnetic)
            Alpine.data('magneticButton', () => ({
                mx: 0, my: 0,
                handleHover(e) {
                    const rect = this.$el.getBoundingClientRect();
                    this.mx = (e.clientX - rect.left - rect.width / 2) * 0.3;
                    this.my = (e.clientY - rect.top - rect.height / 2) * 0.3;
                },
                reset() { this.mx = 0; this.my = 0; }
            }));

            // Component x-bind cho magnetic (gắn vào thẻ a)
            Alpine.bind('magneticContainer', () => ({
                '@mousemove'(e) {
                    const rect = this.$el.getBoundingClientRect();
                    const x = (e.clientX - rect.left - rect.width / 2) * 0.2;
                    const y = (e.clientY - rect.top - rect.height / 2) * 0.2;
                    this.$el.style.transform = `translate(${x}px, ${y}px)`;
                },
                '@mouseleave'() {
                    this.$el.style.transform = 'translate(0px, 0px)';
                }
            }));

            // Hiệu ứng lan tỏa (Ripple)
            Alpine.data('rippleEffect', () => ({
                ripples: [],
                addRipple(e) {
                    const rect = this.$el.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    const id = Date.now();
                    this.ripples.push({ id, x, y, size });
                    setTimeout(() => {
                        this.ripples = this.ripples.filter(r => r.id !== id);
                    }, 600);
                }
            }));

            // Xử lý nạp thanh tiến trình khi cuộn tới
            Alpine.data('statBar', (targetWidth) => ({
                width: '0%',
                init() {
                    const observer = new IntersectionObserver((entries) => {
                        if (entries[0].isIntersecting) {
                            setTimeout(() => { this.width = targetWidth; }, 200);
                            observer.disconnect();
                        }
                    }, { threshold: 0.2 });
                    observer.observe(this.$el);
                }
            }));
        });
    </script>

    @livewireScripts
</body>
</html>