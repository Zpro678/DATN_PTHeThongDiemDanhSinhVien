<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hệ thống đang bảo trì - Attendia Tech</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-[#f8fafc] text-slate-900 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-8 text-center border border-slate-100">
        
        <div class="mx-auto w-20 h-20 bg-rose-50 rounded-full flex items-center justify-center mb-6">
            <i data-lucide="settings" class="w-10 h-10 text-rose-500 animate-[spin_4s_linear_infinite]"></i>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Hệ thống đang bảo trì</h1>
        
        <p class="text-slate-500 mb-6 leading-relaxed">
            Chúng tôi đang tiến hành nâng cấp và bảo trì hệ thống để mang lại trải nghiệm tốt hơn. 
            Mọi chức năng đăng nhập hiện đang tạm khóa. Vui lòng quay lại sau!
        </p>

        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-600 font-medium border border-rose-100">
                {{ session('error') }}
            </div>
        @endif

        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 mb-6">
            @php
                $start = \App\Models\Setting::get('maintenance_start');
                $end = \App\Models\Setting::get('maintenance_end');
            @endphp
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-slate-500">Bắt đầu:</span>
                <span class="font-semibold text-slate-700">{{ $start ? \Carbon\Carbon::parse($start)->format('H:i - d/m/Y') : '--' }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">Dự kiến kết thúc:</span>
                <span class="font-semibold text-slate-700">{{ $end ? \Carbon\Carbon::parse($end)->format('H:i - d/m/Y') : '--' }}</span>
            </div>
        </div>

        <a href="{{ url('/') }}" class="inline-flex items-center justify-center w-full px-6 py-3 bg-slate-900 text-white font-medium rounded-xl hover:bg-slate-800 transition shadow-sm hover:shadow-md">
            Trở về Trang chủ
        </a>

        @if(!auth()->check())
            <div class="mt-4 text-sm text-slate-400">
                Bạn là quản trị viên? <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Đăng nhập</a>
            </div>
        @endif
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
