<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trình chiếu điểm danh - EduCheck</title>
    @vite(['resources/css/app.css'])
</head>
<body style="margin:0;padding:0;overflow:hidden;">

<div style="display:flex;flex-direction:column;height:100vh;width:100%;background:var(--color-background);overflow:hidden;position:relative;">

    {{-- Header --}}
    <header style="display:flex;justify-content:space-between;align-items:center;padding:0 24px;background:var(--color-surface);border-bottom:1px solid var(--color-outline-variant);box-shadow:0 1px 3px rgba(0,0,0,.06);z-index:50;height:80px;flex-shrink:0;">
        <div style="display:flex;align-items:center;gap:16px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-primary)" style="width:32px;height:32px;"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-1.342M12 20.904v-7.415"/></svg>
            <span style="font-size:20px;font-weight:700;color:var(--color-primary);">EduCheck</span>
            <div style="width:1px;height:24px;background:var(--color-outline-variant);margin:0 8px;"></div>
            <span style="font-size:18px;color:var(--color-on-surface);">Trình chiếu điểm danh</span>
        </div>
        <form action="{{ route('dashboard') }}" method="GET">
            <button type="submit" style="display:flex;align-items:center;gap:8px;padding:8px 16px;background:var(--color-error);color:var(--color-on-error);border-radius:8px;font-weight:600;font-size:14px;cursor:pointer;border:none;transition:opacity .2s;" onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 9.563C9 9.252 9.252 9 9.563 9h4.874c.311 0 .563.252.563.563v4.874c0 .311-.252.563-.563.563H9.564A.562.562 0 0 1 9 14.437V9.564Z"/></svg>
                Kết thúc phiên
            </button>
        </form>
    </header>

    {{-- Main --}}
    <main style="flex:1;display:flex;width:100%;height:calc(100vh - 80px);overflow:hidden;">

        {{-- QR Sidebar --}}
        <section style="width:33.33%;height:100%;display:flex;flex-direction:column;padding:24px;border-right:1px solid var(--color-outline-variant);background:var(--color-surface-container-lowest);">
            <div style="margin-bottom:32px;text-align:center;">
                <h1 style="font-size:32px;font-weight:700;margin-bottom:8px;">Lập trình Web nâng cao</h1>
                <p style="font-size:18px;color:var(--color-on-surface-variant);">Nhóm 2 - Buổi 5</p>
            </div>

            <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <div style="position:relative;background:white;padding:32px;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.12);border:2px solid var(--color-primary-fixed);">
                    {{-- QR Code --}}
                    <div style="width:256px;height:256px;background:var(--color-surface-container);border-radius:8px;overflow:hidden;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=educheck-session-{{ time() }}" alt="QR Code" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    {{-- Corner decorations --}}
                    <div style="position:absolute;top:-8px;left:-8px;width:32px;height:32px;border-top:4px solid var(--color-primary);border-left:4px solid var(--color-primary);border-radius:4px 0 0 0;"></div>
                    <div style="position:absolute;top:-8px;right:-8px;width:32px;height:32px;border-top:4px solid var(--color-primary);border-right:4px solid var(--color-primary);border-radius:0 4px 0 0;"></div>
                    <div style="position:absolute;bottom:-8px;left:-8px;width:32px;height:32px;border-bottom:4px solid var(--color-primary);border-left:4px solid var(--color-primary);border-radius:0 0 0 4px;"></div>
                    <div style="position:absolute;bottom:-8px;right:-8px;width:32px;height:32px;border-bottom:4px solid var(--color-primary);border-right:4px solid var(--color-primary);border-radius:0 0 4px 0;"></div>
                </div>

                <div style="margin-top:48px;display:flex;flex-direction:column;align-items:center;">
                    <div id="timer" style="font-size:52px;font-weight:700;color:var(--color-primary);letter-spacing:-.5px;">00:30</div>
                    <p class="text-muted text-sm" style="margin-top:8px;">Mã QR sẽ làm mới sau...</p>
                </div>
            </div>

            <div style="margin-top:auto;padding-top:24px;border-top:1px solid var(--color-outline-variant);display:flex;justify-content:space-between;align-items:center;color:var(--color-on-surface-variant);">
                <div style="display:flex;align-items:center;gap:8px;font-size:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                    Phòng P.302
                </div>
                <div style="display:flex;align-items:center;gap:8px;font-size:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/></svg>
                    Edu_Wifi_Zone_1
                </div>
            </div>
        </section>

        {{-- Student list --}}
        <section style="flex:1;height:100%;display:flex;flex-direction:column;background:var(--color-surface-container-low);padding:24px;position:relative;">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px;padding-bottom:16px;border-bottom:1px solid var(--color-outline-variant);">
                <div>
                    <h2 style="font-size:24px;font-weight:700;">Sinh viên đã điểm danh</h2>
                    <p class="text-muted text-sm" style="margin-top:4px;">Sĩ số lớp: 45</p>
                </div>
                <div style="display:flex;align-items:center;gap:8px;background:var(--color-surface-container-lowest);padding:8px 16px;border-radius:999px;border:1px solid var(--color-outline-variant);">
                    <span id="pulse-dot" style="width:12px;height:12px;border-radius:50%;background:#22c55e;display:inline-block;"></span>
                    <span style="font-size:18px;font-weight:600;" id="student-count">3 / 45</span>
                </div>
            </div>

            <div style="flex:1;overflow-y:auto;padding-right:8px;padding-bottom:96px;">
                <div style="display:flex;flex-direction:column;gap:12px;" id="student-list">
                    @foreach([
                        ['name'=>'Nguyễn Văn An','id'=>'20110001','time'=>'08:05:12','active'=>true],
                        ['name'=>'Trần Thị Bình','id'=>'20110002','time'=>'08:04:45','active'=>false],
                        ['name'=>'Lê Hoàng Cường','id'=>'20110003','time'=>'08:03:20','active'=>false],
                    ] as $student)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:{{ $student['active'] ? '2px solid var(--color-primary)' : '1px solid var(--color-outline-variant)' }};background:{{ $student['active'] ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.9)' }};{{ $student['active'] ? 'transform:scale(1.02);box-shadow:0 4px 16px rgba(0,74,198,.15);' : '' }}backdrop-filter:blur(4px);">
                        <div style="display:flex;align-items:center;gap:16px;">
                            <img src="https://i.pravatar.cc/150?u={{ $student['id'] }}" alt="avatar" style="width:64px;height:64px;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.1);">
                            <div>
                                <h3 style="font-size:18px;font-weight:700;">{{ $student['name'] }}</h3>
                                <p class="text-muted text-sm">MSSV: {{ $student['id'] }}</p>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:24px;">
                            <p style="font-size:18px;font-weight:600;">{{ $student['time'] }}</p>
                            <div style="display:flex;align-items:center;gap:6px;background:#dcfce7;color:#15803d;padding:6px 12px;border-radius:999px;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;{{ $student['active'] ? 'animation:pulse 2s infinite;' : '' }}"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span style="font-size:14px;font-weight:700;">Hợp lệ</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
</div>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
</style>

<script>
let seconds = 30;
const timerEl = document.getElementById('timer');
const dot = document.getElementById('pulse-dot');

setInterval(() => {
    seconds = seconds > 0 ? seconds - 1 : 30;
    timerEl.textContent = '00:' + String(seconds).padStart(2, '0');
    if (seconds === 0) {
        timerEl.style.color = 'var(--color-error)';
        setTimeout(() => { timerEl.style.color = 'var(--color-primary)'; }, 500);
    }
}, 1000);

// Pulse dot animation
setInterval(() => {
    dot.style.opacity = dot.style.opacity === '0.4' ? '1' : '0.4';
}, 800);
</script>
</body>
</html>
