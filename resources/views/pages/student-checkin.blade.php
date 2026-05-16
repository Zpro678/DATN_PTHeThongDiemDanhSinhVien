<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điểm danh sinh viên - EduCheck</title>
    @vite(['resources/css/app.css'])
</head>
<body style="margin:0;">

<div style="background:var(--color-background);color:var(--color-on-background);min-height:100dvh;display:flex;flex-direction:column;align-items:center;">

    {{-- Header --}}
    <header style="width:100%;padding:16px 24px;display:flex;justify-content:center;align-items:center;">
        <h1 style="font-size:24px;color:var(--color-primary);font-weight:700;">EduCheck</h1>
    </header>

    {{-- Main --}}
    <main style="width:100%;max-width:448px;padding:0 16px;flex:1;display:flex;flex-direction:column;gap:24px;padding-top:24px;padding-bottom:96px;">

        {{-- GPS section --}}
        <section style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:16px;background:var(--color-surface-container-lowest);padding:24px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid var(--color-surface-variant);">
            <div style="width:96px;height:96px;border-radius:50%;background:var(--color-primary-fixed);display:flex;align-items:center;justify-content:center;color:var(--color-primary);margin-bottom:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:48px;height:48px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
            </div>
            <p class="text-muted text-sm" style="padding:0 16px;line-height:1.6;">
                Vui lòng cho phép truy cập vị trí để xác thực bạn đang có mặt tại lớp học.
            </p>
            <button id="gps-btn" onclick="requestGPS()" style="margin-top:8px;padding:12px 24px;background:var(--color-primary);color:var(--color-on-primary);border-radius:999px;font-weight:600;font-size:15px;border:none;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(0,74,198,.3);width:100%;"
                onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'"
                onmousedown="this.style.transform='scale(.98)'" onmouseup="this.style.transform='scale(1)'">
                Cấp quyền Vị trí (GPS)
            </button>
        </section>

        {{-- Student ID form --}}
        <section style="display:flex;flex-direction:column;gap:16px;margin-top:16px;">
            <div style="display:flex;flex-direction:column;gap:8px;">
                <label for="student_id" style="font-weight:600;color:var(--color-on-surface);">Mã số sinh viên (MSSV)</label>
                <div style="position:relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-outline)" style="position:absolute;left:16px;top:50%;transform:translateY(-50%);width:20px;height:20px;pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    <input id="student_id" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8"
                        placeholder="Nhập 8 chữ số..."
                        style="width:100%;padding:16px 16px 16px 52px;border-radius:12px;border:1px solid var(--color-outline-variant);background:var(--color-surface-container-lowest);font-size:16px;color:var(--color-on-surface);outline:none;transition:all .2s;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--color-primary)';this.style.boxShadow='0 0 0 2px rgba(0,74,198,.2)'"
                        onblur="this.style.borderColor='var(--color-outline-variant)';this.style.boxShadow='none'">
                </div>
            </div>
            <button onclick="submitAttendance()" style="width:100%;padding:16px;margin-top:16px;background:#2563eb;color:white;border-radius:12px;font-weight:700;font-size:16px;border:none;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(37,99,235,.35);"
                onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'"
                onmousedown="this.style.transform='scale(.98)'" onmouseup="this.style.transform='scale(1)'">
                GỬI ĐIỂM DANH
            </button>
        </section>
    </main>

    {{-- Success Modal --}}
    <div id="success-modal" style="display:none;position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;padding:16px;">
        <div onclick="closeModal()" style="position:absolute;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);"></div>
        <div style="position:relative;width:100%;max-width:384px;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-radius:16px;padding:32px;display:flex;flex-direction:column;align-items:center;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.2);">
            <div style="width:80px;height:80px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;color:#15803d;margin-bottom:16px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:40px;height:40px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <h2 style="font-size:24px;font-weight:600;margin-bottom:8px;">Điểm danh thành công!</h2>
            <div style="background:var(--color-surface-container-low);width:100%;border-radius:8px;padding:12px;margin-bottom:24px;border:1px solid var(--color-surface-variant);">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;color:var(--color-on-surface-variant);">
                    <span>Thời gian:</span>
                    <span style="font-weight:500;color:var(--color-on-surface);" id="check-time">--:--</span>
                </div>
                <div style="width:100%;height:1px;background:rgba(195,198,215,.3);margin:8px 0;"></div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:14px;color:var(--color-on-surface-variant);">
                    <span>Vị trí:</span>
                    <span style="font-weight:500;color:#15803d;">Hợp lệ</span>
                </div>
            </div>
            <button onclick="closeModal()" style="width:100%;padding:12px;background:var(--color-surface-container-high);color:var(--color-on-surface);border-radius:12px;font-weight:600;border:none;cursor:pointer;font-size:15px;transition:background .2s;" onmouseover="this.style.background='var(--color-surface-variant)'" onmouseout="this.style.background='var(--color-surface-container-high)'">Đóng</button>
        </div>
    </div>

    {{-- Mobile nav --}}
    <nav style="position:fixed;bottom:0;left:0;width:100%;z-index:50;display:flex;justify-content:space-around;align-items:center;padding:8px 16px;background:var(--color-surface);box-shadow:0 -4px 10px rgba(0,0,0,.05);border-radius:16px 16px 0 0;">
        <a href="{{ route('student-checkin') }}" style="display:flex;flex-direction:column;align-items:center;justify-content:center;background:var(--color-primary-fixed);color:var(--color-primary);border-radius:12px;padding:8px;width:64px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>
            <span style="font-size:10px;margin-top:4px;font-weight:500;">Check-in</span>
        </a>
        <a href="{{ route('history') }}" style="display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--color-on-surface-variant);border-radius:12px;padding:8px;width:64px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
            <span style="font-size:10px;margin-top:4px;font-weight:500;">History</span>
        </a>
        <a href="{{ route('settings') }}" style="display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--color-on-surface-variant);border-radius:12px;padding:8px;width:64px;text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
            <span style="font-size:10px;margin-top:4px;font-weight:500;">Profile</span>
        </a>
    </nav>
</div>

<script>
function requestGPS() {
    const btn = document.getElementById('gps-btn');
    btn.textContent = 'Đang lấy vị trí...';
    btn.style.opacity = '.7';
    btn.style.cursor = 'wait';
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            () => { btn.textContent = '✓ Vị trí đã xác nhận'; btn.style.background='#16a34a'; btn.style.opacity='1'; btn.style.cursor='default'; },
            () => { btn.textContent = 'Không thể lấy vị trí'; btn.style.background='var(--color-error)'; btn.style.opacity='1'; btn.style.cursor='pointer'; }
        );
    }
}

function submitAttendance() {
    const id = document.getElementById('student_id').value.trim();
    if (id.length < 6) { alert('Vui lòng nhập đủ mã sinh viên!'); return; }
    const now = new Date();
    document.getElementById('check-time').textContent = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
    document.getElementById('success-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('success-modal').style.display = 'none';
    document.getElementById('student_id').value = '';
}
</script>
</body>
</html>
