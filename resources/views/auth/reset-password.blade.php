<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Đặt lại mật khẩu - SmartAttendance</title>
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
                <div class="mb-6 text-center">
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">ĐẶT LẠI MẬT KHẨU</h2>
                    <p class="text-sm text-slate-600 mt-2">
                        Vui lòng nhập mật khẩu mới cho tài khoản của bạn.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                                value="{{ old('email', $request->email) }}"
                                required
                                autofocus
                                readonly
                                class="w-full bg-slate-100 text-slate-500 border border-slate-200 text-sm font-medium rounded-xl outline-none pl-10 py-3 cursor-not-allowed"
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5 w-full">
                        <label for="password" class="text-xs font-bold text-slate-600 uppercase tracking-wider block">
                            Mật khẩu mới <span class="text-rose-500">*</span>
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
                                class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-3 pr-10 @error('password') border-rose-400 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 bg-rose-50/20 @else border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 @enderror placeholder:text-slate-400"
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
                            Xác nhận mật khẩu mới <span class="text-rose-500">*</span>
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
                                class="w-full bg-slate-50 text-slate-900 border text-sm font-medium rounded-xl outline-none transition-all duration-200 pl-10 py-3 pr-10 border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 placeholder:text-slate-400"
                                placeholder="••••••••"
                            />
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer z-10">
                                <i data-lucide="eye" id="togglePasswordConfirmIcon" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-3 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-500/50">
                            Cập nhật mật khẩu
                            <i data-lucide="check" class="w-4 h-4"></i>
                        </button>
                    </div>
                </form>
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
    </script>
</body>
</html>
