@extends('layouts.app')

@section('title', 'Nâng cấp Pro - EduCheck Admin')

@section('content')
<div style="max-width:896px;margin:0 auto;padding:48px 24px;">

    <div style="text-align:center;margin-bottom:48px;max-width:640px;margin-left:auto;margin-right:auto;">
        <h1 style="font-size:clamp(28px,5vw,48px);font-weight:700;margin-bottom:16px;">Nâng tầm quản lý với EduCheck Pro</h1>
        <p class="text-muted">Lựa chọn gói phù hợp để mở khóa toàn bộ tính năng và tối ưu hóa quy trình điểm danh của bạn.</p>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;align-items:flex-end;margin-bottom:48px;">

        {{-- Free plan --}}
        <div class="card" style="padding:32px;">
            <div style="margin-bottom:24px;">
                <h2 style="font-size:20px;font-weight:600;margin-bottom:8px;">Gói Miễn phí</h2>
                <div style="font-size:30px;font-weight:700;">0đ</div>
                <p class="text-muted text-sm" style="margin-top:8px;">Phù hợp cho cá nhân bắt đầu trải nghiệm</p>
            </div>
            <hr style="border:none;border-top:1px solid var(--color-outline-variant);margin-bottom:24px;">
            <ul style="display:flex;flex-direction:column;gap:16px;margin-bottom:32px;">
                @foreach(['Tối đa 2 lớp học','Tạo mã QR điểm danh tĩnh','Báo cáo tổng quan cơ bản'] as $feat)
                <li style="display:flex;align-items:center;gap:12px;font-size:14px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="var(--color-outline)" style="width:20px;height:20px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ $feat }}</span>
                </li>
                @endforeach
            </ul>
            <button style="width:100%;padding:12px;background:var(--color-surface-container-highest);color:var(--color-on-surface-variant);font-weight:600;border-radius:8px;border:1px solid var(--color-outline-variant);font-size:14px;cursor:default;">Đang sử dụng</button>
        </div>

        {{-- Pro plan --}}
        <div style="position:relative;background:var(--color-surface-container-lowest);border:2px solid var(--color-pro-accent);border-radius:12px;padding:32px;box-shadow:0 8px 24px rgba(0,0,0,.1);transform:translateY(-16px);">
            <div style="position:absolute;top:0;left:50%;transform:translate(-50%,-50%);background:var(--color-pro-accent);color:#000;font-size:11px;font-weight:700;padding:4px 16px;border-radius:999px;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;">
                Khuyên dùng
            </div>
            <div style="margin-bottom:24px;margin-top:8px;">
                <h2 style="font-size:20px;font-weight:600;margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                    Gói Pro
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="var(--color-pro-accent)" style="width:20px;height:20px;"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z" clip-rule="evenodd"/></svg>
                </h2>
                <div style="display:flex;align-items:baseline;gap:4px;">
                    <span style="font-size:40px;font-weight:700;">199.000đ</span>
                    <span class="text-muted text-sm">/tháng</span>
                </div>
                <p class="text-muted text-sm" style="margin-top:8px;">Giải pháp toàn diện cho giảng viên và tổ chức</p>
            </div>
            <hr style="border:none;border-top:1px solid var(--color-outline-variant);margin-bottom:24px;">
            <ul style="display:flex;flex-direction:column;gap:16px;margin-bottom:32px;">
                @foreach(['Không giới hạn lớp học','QR động chống gian lận (15s)','Xác thực vị trí GPS & Thiết bị','Xuất báo cáo chi tiết Excel/CSV','Nhận diện khuôn mặt AI'] as $feat)
                <li style="display:flex;align-items:center;gap:12px;font-size:14px;font-weight:500;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="var(--color-pro-accent)" style="width:20px;height:20px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    <span>{{ $feat }}</span>
                </li>
                @endforeach
            </ul>
            <button style="width:100%;padding:14px;background:var(--color-pro-accent);color:#000;font-weight:700;border-radius:8px;font-size:15px;cursor:pointer;transition:opacity .2s;border:none;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                Nâng cấp ngay →
            </button>
        </div>
    </div>

</div>
@endsection
