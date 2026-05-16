@extends('layouts.app')

@section('title', 'Thống kê - EduCheck Admin')

@section('content')
<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- Header --}}
    <div style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;">
        <div>
            <h2 style="font-size:24px;font-weight:600;margin-bottom:8px;">Báo cáo tổng hợp đơn xin nghỉ</h2>
            <p class="text-muted text-sm">Phân tích chuyên sâu dữ liệu vắng mặt của học sinh</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="position:relative;">
                <select class="form-input" style="padding-right:36px;appearance:none;cursor:pointer;font-size:14px;">
                    <option>Tháng này</option>
                    <option>Tháng trước</option>
                    <option>3 tháng qua</option>
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-on-surface-variant);pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </div>
            <button style="background:var(--color-surface-container-low);border:1px solid var(--color-outline-variant);border-radius:8px;padding:8px;color:var(--color-primary);cursor:pointer;transition:background .2s;" onmouseover="this.style.background='var(--color-surface-container-high)'" onmouseout="this.style.background='var(--color-surface-container-low)'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:20px;height:20px;display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-4">
        <div class="stat-card">
            <div class="stat-icon" style="color:var(--color-primary);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                <span style="font-size:11px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Tổng số đơn</span>
                <span style="font-size:10px;background:rgba(186,26,26,.1);color:var(--color-error);padding:2px 8px;border-radius:4px;font-weight:500;">+12%</span>
            </div>
            <div style="font-size:40px;font-weight:700;">342</div>
            <div class="text-muted text-sm" style="margin-top:4px;">vs 305 tháng trước</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color:#16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                <span style="font-size:11px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Tỷ lệ duyệt</span>
                <span style="font-size:10px;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:4px;font-weight:500;">0%</span>
            </div>
            <div style="font-size:40px;font-weight:700;">86<span style="font-size:24px;color:var(--color-on-surface-variant);">%</span></div>
            <div class="text-muted text-sm" style="margin-top:4px;">Mục tiêu: &gt;85%</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color:var(--color-primary);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
            </div>
            <div style="margin-bottom:16px;">
                <span style="font-size:11px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Lý do phổ biến nhất</span>
            </div>
            <div style="font-size:20px;font-weight:600;margin-bottom:4px;">Ốm đau / Sức khỏe</div>
            <div class="text-muted text-sm" style="display:flex;align-items:center;gap:8px;">
                <span style="width:8px;height:8px;border-radius:50%;background:var(--color-primary);display:inline-block;"></span>
                68% tổng số đơn
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="color:var(--color-primary);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            </div>
            <div style="margin-bottom:16px;">
                <span style="font-size:11px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Lớp nghỉ nhiều nhất</span>
            </div>
            <div style="font-size:20px;font-weight:600;margin-bottom:4px;">10A4 - Toán</div>
            <div class="text-muted text-sm">Trung bình 3 HS vắng/buổi</div>
        </div>
    </div>

    {{-- Charts row --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

        {{-- Line Chart (CSS-based) --}}
        <div class="card" style="padding:24px;display:flex;flex-direction:column;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                <h3 style="font-size:18px;font-weight:600;">Xu hướng nghỉ phép (Theo tuần)</h3>
                <button class="icon-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                </button>
            </div>
            <div style="flex:1;min-height:250px;display:flex;align-items:flex-end;gap:8px;padding:0 8px 24px;border-bottom:1px solid var(--color-outline-variant);position:relative;">
                @php $chartData = [['label'=>'Tuần 1','val'=>30],['label'=>'Tuần 2','val'=>50],['label'=>'Tuần 3','val'=>60],['label'=>'Tuần 4','val'=>45],['label'=>'Tuần 5','val'=>80]]; @endphp
                @foreach($chartData as $d)
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;">
                    <span style="font-size:12px;font-weight:600;color:var(--color-primary);">{{ $d['val'] }}</span>
                    <div style="width:100%;background:rgba(0,74,198,.15);border-radius:6px 6px 0 0;height:{{ $d['val'] * 2.5 }}px;position:relative;transition:height .3s;" onmouseover="this.style.background='rgba(0,74,198,.3)'" onmouseout="this.style.background='rgba(0,74,198,.15)'">
                        <div style="position:absolute;bottom:0;left:0;right:0;height:{{ $d['val'] * 2.5 }}px;background:linear-gradient(180deg,var(--color-primary) 0%,rgba(0,74,198,.6) 100%);border-radius:6px 6px 0 0;"></div>
                    </div>
                    <span style="font-size:12px;color:var(--color-on-surface-variant);">{{ $d['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Reason Breakdown --}}
        <div class="card" style="padding:24px;display:flex;flex-direction:column;">
            <h3 style="font-size:18px;font-weight:600;margin-bottom:24px;">Phân loại lý do</h3>
            <div style="flex:1;display:flex;flex-direction:column;justify-content:center;gap:20px;">
                @foreach([
                    ['label'=>'Ốm đau','color'=>'var(--color-primary)','pct'=>68],
                    ['label'=>'Việc gia đình','color'=>'#f59e0b','pct'=>18],
                    ['label'=>'Lý do khác','color'=>'#8b5cf6','pct'=>14],
                ] as $reason)
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:4px;">
                        <span style="display:flex;align-items:center;gap:8px;">
                            <span style="width:12px;height:12px;border-radius:3px;background:{{ $reason['color'] }};display:inline-block;"></span>
                            {{ $reason['label'] }}
                        </span>
                        <span class="font-medium">{{ $reason['pct'] }}%</span>
                    </div>
                    <div class="progress-bar" style="height:8px;">
                        <div class="progress-fill" style="width:{{ $reason['pct'] }}%;background:{{ $reason['color'] }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
