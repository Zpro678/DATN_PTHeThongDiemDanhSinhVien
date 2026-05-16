@extends('layouts.app')

@section('title', 'Dashboard - EduCheck Admin')

@section('content')
<div style="max-width:1152px; margin:0 auto;">

    {{-- Header --}}
    <header style="margin-bottom:32px;">
        <h2 style="font-size:30px;font-weight:700;letter-spacing:-.5px;color:var(--color-on-background);margin-bottom:4px;">
            Chào buổi sáng, TS. Nguyễn Văn A
        </h2>
        <p class="text-muted text-sm">Thứ Năm, ngày 24 tháng 10, 2024</p>
    </header>

    {{-- Stats --}}
    <section class="grid grid-cols-3" style="margin-bottom:32px;">
        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--color-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            </div>
            <p class="stat-label">Tổng số sinh viên</p>
            <h3 class="stat-value">1,250</h3>
            <div class="stat-sub text-success">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                <span>Tăng 5% so với tháng trước</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#16a34a"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <p class="stat-label">Tỷ lệ chuyên cần hôm nay</p>
            <h3 class="stat-value text-success">94%</h3>
            <div class="progress-bar" style="height:6px;margin-top:12px;">
                <div class="progress-fill" style="width:94%;background:#16a34a;"></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--color-error)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <p class="stat-label">Vắng mặt &gt; 20%</p>
            <h3 class="stat-value text-error">12 sinh viên</h3>
            <div class="stat-sub text-error">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <span>Cần lưu ý kiểm tra</span>
            </div>
        </div>
    </section>

    {{-- Classes --}}
    <section>
        <div class="section-header">
            <h3>Lớp học của bạn</h3>
            <button class="text-primary text-sm font-medium" style="background:none;border:none;cursor:pointer;">Xem tất cả</button>
        </div>

        <div class="grid" style="grid-template-columns:repeat(2,1fr);">
            {{-- Class Card 1 --}}
            <div class="card" style="padding:20px;display:flex;flex-direction:column;transition:border-color .2s;" onmouseover="this.style.borderColor='rgba(0,74,198,.4)'" onmouseout="this.style.borderColor='var(--color-outline-variant)'">
                <div class="flex justify-between items-start" style="margin-bottom:16px;">
                    <div>
                        <h4 style="font-size:18px;font-weight:600;margin-bottom:4px;">Lập trình Web nâng cao</h4>
                        <p class="text-muted text-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/></svg>
                            IT4021 - Nhóm 2
                        </p>
                    </div>
                    <span class="badge badge-primary">Sắp diễn ra</span>
                </div>
                <div class="grid grid-cols-2" style="margin-bottom:24px;margin-top:8px;gap:16px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="text-xs text-muted font-medium">Sĩ số</span>
                        <span class="text-sm font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-outline)" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            45 Sinh viên
                        </span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="text-xs text-muted font-medium">Thời gian</span>
                        <span class="text-sm font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-outline)" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            08:00 - 10:30
                        </span>
                    </div>
                </div>
                <a href="{{ route('sessions.create') }}" class="btn-primary" style="margin-top:auto;">
                    Bắt đầu điểm danh
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            {{-- Class Card 2 --}}
            <div class="card" style="padding:20px;display:flex;flex-direction:column;transition:border-color .2s;" onmouseover="this.style.borderColor='rgba(0,74,198,.4)'" onmouseout="this.style.borderColor='var(--color-outline-variant)'">
                <div class="flex justify-between items-start" style="margin-bottom:16px;">
                    <div>
                        <h4 style="font-size:18px;font-weight:600;margin-bottom:4px;">Cơ sở dữ liệu phân tán</h4>
                        <p class="text-muted text-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:14px;height:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5"/></svg>
                            CS3050 - Nhóm 1
                        </p>
                    </div>
                    <span class="badge badge-muted">Chiều nay</span>
                </div>
                <div class="grid grid-cols-2" style="margin-bottom:24px;margin-top:8px;gap:16px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="text-xs text-muted font-medium">Sĩ số</span>
                        <span class="text-sm font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-outline)" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            60 Sinh viên
                        </span>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <span class="text-xs text-muted font-medium">Thời gian</span>
                        <span class="text-sm font-medium flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-outline)" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            13:00 - 15:30
                        </span>
                    </div>
                </div>
                <button class="btn-secondary" style="margin-top:auto;">Xem chi tiết lớp</button>
            </div>
        </div>
    </section>

</div>
@endsection
