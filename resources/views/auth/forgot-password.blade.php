<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Quên mật khẩu - SmartAttendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Import Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-white antialiased overflow-hidden">
    <div class="h-screen flex">
        
        <!-- ======================= -->
        <!-- AuthIllustration (Bên trái) -->
        <!-- ======================= -->
        @include('auth.partials.auth-illustration')

        <!-- ======================= -->
        <!-- FORM QUÊN MẬT KHẨU (Bên phải) -->
        <!-- ======================= -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-8 py-6 bg-white h-screen">
            <div class="w-full max-w-md my-auto">
                
                <div class="mb-8 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-blue-100">
                        <i data-lucide="key-round" class="w-8 h-8 text-blue-600"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">QUÊN MẬT KHẨU?</h2>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Đừng lo lắng! Vui lòng nhập địa chỉ email của bạn. Chúng tôi sẽ gửi một liên kết để bạn có thể đặt lại mật khẩu mới.
                    </p>
                </div>

                <!-- Session Status (thông báo thành công) -->
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                        <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <!-- AuthInput: Email -->
                    <div class="space-y-2 w-full">
                        <label for="email" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                            Email công tác <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required autofocus
                                class="w-full bg-slate-50 text-slate-900 border text-base font-medium rounded-xl outline-none transition-all duration-200 pl-11 py-3.5 @error('email') pr-10 border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else pr-4 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
                                placeholder="giangvien@school.edu.vn"
                            />
                            @error('email')
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-rose-500">
                                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                            </div>
                            @enderror
                        </div>
                        @error('email')
                            <p class="text-sm font-bold text-rose-500 mt-1 flex items-center gap-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-base px-4 py-4 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-500/50">
                        Gửi liên kết khôi phục
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                    
                </form>

                <!-- Back to Login -->
                <div class="mt-8 text-center">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 text-sm font-bold text-slate-500 hover:text-blue-600 transition-colors group">
                        <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        Quay lại Đăng nhập
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
