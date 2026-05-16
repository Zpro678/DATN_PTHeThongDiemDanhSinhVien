@extends('layouts.app')

@section('title', 'Cài đặt - EduCheck Admin')

@section('content')
<div style="max-width:1024px;margin:0 auto;padding:32px 0;">

    <header style="margin-bottom:32px;">
        <h2 style="font-size:30px;font-weight:700;margin-bottom:4px;">Cài đặt hệ thống</h2>
        <p class="text-muted">Quản lý tài khoản và cấu hình ứng dụng EduCheck.</p>
    </header>

    <div style="display:flex;gap:32px;">

        {{-- Sidebar nav --}}
        <aside style="width:256px;flex-shrink:0;">
            <nav style="display:flex;flex-direction:column;gap:4px;position:sticky;top:32px;">
                <a href="#profile"       class="nav-link active" onclick="showSection('profile',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                    Thông tin cá nhân
                </a>
                <a href="#notifications" class="nav-link" onclick="showSection('notifications',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                    Thông báo
                </a>
                <a href="#security" class="nav-link" onclick="showSection('security',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    Bảo mật
                </a>
                <a href="#attendance" class="nav-link" onclick="showSection('attendance',this)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/></svg>
                    Cấu hình điểm danh
                </a>
            </nav>
        </aside>

        {{-- Sections --}}
        <div style="flex:1;display:flex;flex-direction:column;gap:24px;">

            {{-- Profile section --}}
            <section class="card" id="section-profile" style="overflow:hidden;">
                <div style="padding:24px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface-container-low);">
                    <h3 style="font-size:18px;font-weight:600;display:flex;align-items:center;gap:8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="var(--color-primary)" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        Thông tin cá nhân
                    </h3>
                </div>
                <div style="padding:24px;">
                    <div style="display:flex;align-items:center;gap:24px;margin-bottom:32px;">
                        <img src="https://i.pravatar.cc/150?u=123" alt="Avatar" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:4px solid var(--color-surface-container-low);">
                        <div>
                            <button class="btn-secondary" style="font-size:14px;margin-bottom:8px;">Thay đổi Avatar</button>
                            <p class="text-xs text-muted">JPG, GIF hoặc PNG. Tối đa 2MB.</p>
                        </div>
                    </div>
                    <form style="display:grid;grid-template-columns:repeat(2,1fr);gap:24px;">
                        @csrf
                        <div>
                            <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Họ và tên</label>
                            <input type="text" value="Nguyễn Văn A" class="form-input">
                        </div>
                        <div>
                            <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Email</label>
                            <input type="email" value="nguyenvana@university.edu.vn" class="form-input">
                        </div>
                        <div>
                            <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Số điện thoại</label>
                            <input type="tel" value="0987654321" class="form-input">
                        </div>
                        <div>
                            <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Khoa / Phòng ban</label>
                            <select class="form-input">
                                <option selected>Khoa Công nghệ Thông tin</option>
                                <option>Khoa Kinh tế</option>
                            </select>
                        </div>
                        <div style="grid-column:1/-1;display:flex;justify-content:flex-end;margin-top:8px;">
                            <button type="submit" class="btn-primary">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- Notifications section (hidden by default) --}}
            <section class="card" id="section-notifications" style="display:none;overflow:hidden;">
                <div style="padding:24px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface-container-low);">
                    <h3 style="font-size:18px;font-weight:600;">Cài đặt thông báo</h3>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    @foreach(['Thông báo điểm danh mới','Yêu cầu nghỉ học cần duyệt','Báo cáo tuần'] as $notif)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--color-outline-variant);border-radius:12px;">
                        <span style="font-size:14px;font-weight:500;">{{ $notif }}</span>
                        <label class="toggle-wrap">
                            <input type="checkbox" checked>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </section>

            {{-- Security section --}}
            <section class="card" id="section-security" style="display:none;overflow:hidden;">
                <div style="padding:24px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface-container-low);">
                    <h3 style="font-size:18px;font-weight:600;">Bảo mật</h3>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div>
                        <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Mật khẩu hiện tại</label>
                        <input type="password" placeholder="••••••••" class="form-input">
                    </div>
                    <div>
                        <label style="display:block;font-size:14px;font-weight:500;color:var(--color-on-surface-variant);margin-bottom:4px;">Mật khẩu mới</label>
                        <input type="password" placeholder="••••••••" class="form-input">
                    </div>
                    <div style="display:flex;justify-content:flex-end;">
                        <button class="btn-primary">Đổi mật khẩu</button>
                    </div>
                </div>
            </section>

            {{-- Attendance config section --}}
            <section class="card" id="section-attendance" style="display:none;overflow:hidden;">
                <div style="padding:24px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface-container-low);">
                    <h3 style="font-size:18px;font-weight:600;">Cấu hình điểm danh</h3>
                </div>
                <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border:1px solid var(--color-outline-variant);border-radius:12px;">
                        <div>
                            <p style="font-weight:500;font-size:14px;">Ngưỡng vắng tự động cảnh báo</p>
                            <p class="text-muted text-xs" style="margin-top:2px;">Gửi cảnh báo khi sinh viên vắng quá ngưỡng</p>
                        </div>
                        <div style="position:relative;width:80px;">
                            <input type="number" value="20" class="form-input" style="padding-right:28px;text-align:center;">
                            <span style="position:absolute;right:8px;top:50%;transform:translateY(-50%);font-size:12px;color:var(--color-on-surface-variant);">%</span>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

@push('scripts')
<script>
function showSection(id, link) {
    document.querySelectorAll('[id^="section-"]').forEach(s => s.style.display = 'none');
    document.getElementById('section-' + id).style.display = 'block';
    document.querySelectorAll('aside .nav-link').forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    return false;
}
</script>
@endpush
@endsection
