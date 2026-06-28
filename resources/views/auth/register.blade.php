<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đăng ký - SmartAttendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-white antialiased overflow-hidden">
    <div class="h-screen flex">
        @include('auth.partials.auth-illustration')

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-8 py-6 xl:py-8 bg-white h-screen">
            <div class="w-full max-w-md my-auto">
                <div class="mb-4 text-center">
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">ĐĂNG KÝ TÀI KHOẢN</h2>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-3">
                    @csrf

                    <div class="space-y-1.5 w-full">
                        <label for="name" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                            Họ và tên <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="user" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-2.5 @error('name') pr-10 border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else pr-4 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
                                placeholder="ThS. Nguyễn Văn A"
                            />
                            @error('name')
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-rose-500">
                                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                </div>
                            @enderror
                        </div>
                        @error('name')
                            <p class="text-xs font-bold text-rose-500 mt-1 flex items-center gap-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5 w-full">
                        <label for="email" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                            Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-2.5 @error('email') pr-10 border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else pr-4 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
                                placeholder="giangvien@school.edu.vn"
                            />
                            @error('email')
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-rose-500">
                                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                                </div>
                            @enderror
                        </div>
                        @error('email')
                            <p class="text-xs font-bold text-rose-500 mt-1 flex items-center gap-1">{{ $message }}</p>
                        @enderror
                    </div>



                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5 w-full">
                            <label for="password" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                                Mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="lock" class="w-5 h-5"></i>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-2.5 pr-10 @error('password') border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
                                    placeholder="••••••••"
                                />
                                <button type="button" onclick="togglePasswordVisibility('password', 'togglePasswordIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer z-10">
                                    <i data-lucide="eye" id="togglePasswordIcon" class="w-4 h-4"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1.5 w-full">
                            <label for="password_confirmation" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                                Xác nhận mật khẩu <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <i data-lucide="lock" class="w-5 h-5"></i>
                                </div>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-2.5 pr-10 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400"
                                    placeholder="••••••••"
                                />
                                <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer z-10">
                                    <i data-lucide="eye" id="togglePasswordConfirmIcon" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start mt-2">
                        <div class="flex items-center h-5">
                            <input id="terms" name="terms" type="checkbox" required class="w-4 h-4 border border-slate-300 rounded bg-slate-50 focus:ring-3 focus:ring-blue-300 text-blue-600">
                        </div>
                        <label for="terms" class="ml-2 text-xs font-medium text-slate-600">
                            Tôi đồng ý với <a href="#" class="text-blue-600 hover:underline font-bold">Điều khoản sử dụng</a> và <a href="#" class="text-blue-600 hover:underline font-bold">Chính sách bảo mật</a> của hệ thống.
                        </label>
                    </div>

                    <button type="submit" class="w-full mt-2 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-3 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-500/50">
                        Đăng ký
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>

                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-xs font-medium tracking-widest">
                            <span class="bg-white px-4 text-slate-400 uppercase">Hoặc</span>
                        </div>
                    </div>

                    <a href="{{ route('google.login') }}" class="w-full flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 active:bg-slate-100 text-slate-700 font-bold text-sm px-4 py-2.5 rounded-xl transition-all shadow-sm cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l3.66-2.84z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                        </svg>
                        <span>Đăng ký bằng Google</span>
                    </a>
                </form>

                <p class="mt-4 text-center text-xs font-medium text-slate-500">
                    Đã có tài khoản?
                    <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-bold hover:underline">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            window.lucide?.createIcons();
        }

        window.lucide?.createIcons();

        document.addEventListener('wheel', function(e) {
            if (e.ctrlKey) {
                e.preventDefault();
            }
        }, { passive: false });

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === '=' || e.key === '-' || e.key === '0' || e.key === '+')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
