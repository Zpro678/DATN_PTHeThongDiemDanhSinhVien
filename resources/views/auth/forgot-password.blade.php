<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quên mật khẩu - SmartAttendance</title>
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

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-white">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">QUÊN MẬT KHẨU?</h2>
                    <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                        Đừng lo lắng! Chỉ cần nhập địa chỉ email của bạn dưới đây, chúng tôi sẽ gửi cho bạn một đường link để đặt lại mật khẩu mới.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200">
                        <p class="font-bold text-sm text-emerald-600 flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            {{ session('status') }}
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-2 w-full">
                        <label for="email" class="text-sm font-bold text-slate-700 uppercase tracking-widest block">
                            Email <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-6 h-6"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full bg-slate-50 text-slate-900 border text-base font-medium rounded-xl outline-none transition-all duration-200 pl-12 py-4 @error('email') pr-10 border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else pr-4 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
                                placeholder="giangvien@truong.edu.vn"
                            />
                            @error('email')
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-rose-500">
                                    <i data-lucide="alert-circle" class="w-6 h-6"></i>
                                </div>
                            @enderror
                        </div>
                        @error('email')
                            <p class="text-sm font-bold text-rose-500 mt-1 flex items-center gap-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base px-4 py-4 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-500/50">
                            Gửi link đặt lại mật khẩu
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="text-center mt-6">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-1">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            Quay lại trang đăng nhập
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.lucide?.createIcons();
    </script>
</body>
</html>
