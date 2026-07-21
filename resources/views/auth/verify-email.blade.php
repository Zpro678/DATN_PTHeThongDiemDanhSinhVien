<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Xác thực Email - SmartAttendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <div class="w-full max-w-md my-auto text-center">
                
                <div class="mb-6 flex justify-center">
                    <div class="bg-blue-100 p-4 rounded-full text-blue-600">
                        <i data-lucide="mail-open" class="w-12 h-12"></i>
                    </div>
                </div>

                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-4">XÁC THỰC EMAIL</h2>
                
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    Cảm ơn bạn đã đăng ký tài khoản! Trước khi bắt đầu, vui lòng xác thực địa chỉ email bằng cách nhấn vào đường link chúng tôi vừa gửi đến hộp thư của bạn. 
                    <br><br>
                    Nếu bạn không nhận được email, hãy nhấn nút bên dưới để chúng tôi gửi lại nhé.
                </p>



                <div class="mt-8 space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-4 py-3 rounded-xl transition-all shadow-md hover:shadow-lg focus:ring-4 focus:ring-blue-500/50">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            Gửi lại email xác thực
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 bg-white border-2 border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-sm px-4 py-3 rounded-xl transition-all">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            Đăng xuất
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        window.lucide?.createIcons();

        // Polling to check email verification status
        setInterval(() => {
            fetch('{{ url('api/check-email-verification') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.verified) {
                    window.location.href = "{{ route('user.dashboard', ['ma_user' => auth()->id()], absolute: false) }}?verified=1";
                }
            })
            .catch(error => console.error('Error checking verification:', error));
        }, 3000);
    </script>
    <x-notification.notification />
</body>
</html>
