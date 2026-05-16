@extends('layouts.app')

@section('title', 'Lịch sử điểm danh - EduCheck Admin')

@section('content')
<div style="display:flex;flex-direction:column;height:100%;gap:24px;">

    {{-- Top bar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div>
            <h2 style="font-size:24px;font-weight:600;margin-bottom:8px;">Lịch sử điểm danh</h2>
            <p class="text-muted text-sm">Theo dõi và quản lý dữ liệu chuyên cần của các lớp học bạn phụ trách.</p>
        </div>
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
            <button class="btn-secondary" style="padding:8px 16px;font-size:14px;">
                Tất cả lớp học
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <button class="btn-secondary" style="padding:8px 16px;font-size:14px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                7 ngày qua
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </button>
            <button class="btn-primary" style="padding:8px 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Xuất báo cáo
            </button>
        </div>
    </div>

    {{-- Content grid --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;flex:1;min-height:500px;">

        {{-- Table --}}
        <div class="card" style="display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:16px;border-bottom:1px solid var(--color-outline-variant);display:flex;justify-content:space-between;align-items:center;background:var(--color-surface);">
                <h3 style="font-weight:600;">Danh sách phiên điểm danh</h3>
                <button class="icon-btn" title="Bộ lọc">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/></svg>
                </button>
            </div>
            <div style="flex:1;overflow:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Lớp học</th>
                            <th>Thời gian</th>
                            <th>Sĩ số</th>
                            <th>Tỷ lệ</th>
                            <th style="text-align:center;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['date'=>'24/10/2023','class'=>'Công nghệ Phần mềm','code'=>'SE102.N11','time'=>'07:30 - 09:30','count'=>'42/45','pct'=>93,'selected'=>true],
                            ['date'=>'23/10/2023','class'=>'Lập trình Web nâng cao','code'=>'IT4021.N2','time'=>'08:00 - 10:30','count'=>'44/45','pct'=>98,'selected'=>false],
                            ['date'=>'22/10/2023','class'=>'Cơ sở dữ liệu phân tán','code'=>'CS3050.N1','time'=>'13:00 - 15:30','count'=>'55/60','pct'=>92,'selected'=>false],
                        ] as $session)
                        <tr class="{{ $session['selected'] ? 'selected' : '' }}" onclick="selectSession(this)">
                            <td style="white-space:nowrap;">{{ $session['date'] }}</td>
                            <td>
                                <p style="font-weight:600;font-size:14px;">{{ $session['class'] }}</p>
                                <p style="font-size:12px;color:var(--color-on-surface-variant);">{{ $session['code'] }}</p>
                            </td>
                            <td style="color:var(--color-on-surface-variant);">{{ $session['time'] }}</td>
                            <td>{{ $session['count'] }}</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <span style="font-size:14px;">{{ $session['pct'] }}%</span>
                                    <div class="progress-bar" style="width:48px;height:6px;">
                                        <div class="progress-fill" style="width:{{ $session['pct'] }}%;background:var(--color-primary);"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <button class="text-primary text-sm font-medium" style="background:none;border:none;cursor:pointer;">Xem chi tiết</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Detail Panel --}}
        <div class="card" style="display:flex;flex-direction:column;overflow:hidden;" id="detail-panel">
            <div style="padding:16px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface);position:sticky;top:0;z-index:20;">
                <h4 style="font-weight:600;font-size:18px;margin-bottom:4px;">Chi tiết phiên</h4>
                <p class="text-muted text-sm">Công nghệ Phần mềm (SE102.N11)</p>
                <div style="display:flex;margin-top:16px;border-bottom:1px solid var(--color-outline-variant);">
                    <button style="flex:1;padding-bottom:8px;font-size:14px;color:var(--color-primary);border-bottom:2px solid var(--color-primary);font-weight:600;background:none;border-top:none;border-left:none;border-right:none;cursor:pointer;">Tất cả (45)</button>
                </div>
            </div>

            <div style="flex:1;overflow-y:auto;padding:8px;">
                <div style="padding:8px;position:sticky;top:0;background:var(--color-surface-container-lowest);z-index:10;margin-bottom:8px;">
                    <div style="position:relative;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-on-surface-variant);pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                        <input type="text" placeholder="Tìm sinh viên..." class="form-input" style="padding-left:36px;padding-top:6px;padding-bottom:6px;">
                    </div>
                </div>

                <ul style="display:flex;flex-direction:column;gap:4px;">
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-radius:8px;transition:background .15s;" onmouseover="this.style.background='var(--color-surface-container-low)'" onmouseout="this.style.background=''">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="avatar avatar-primary">NA</div>
                            <div>
                                <p style="font-size:14px;font-weight:500;line-height:1.2;">Nguyễn Văn A</p>
                                <p style="font-size:11px;color:var(--color-on-surface-variant);">SV2021001</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke="var(--color-primary)" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </li>
                    <li style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-radius:8px;background:rgba(255,218,214,.3);border:1px solid var(--color-error-container);">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="avatar avatar-muted">TB</div>
                            <div>
                                <p style="font-size:14px;font-weight:500;line-height:1.2;">Trần Thị B</p>
                                <p style="font-size:11px;color:var(--color-on-surface-variant);">SV2021045</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="var(--color-error)" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectSession(row) {
    document.querySelectorAll('.data-table tbody tr').forEach(r => r.classList.remove('selected'));
    row.classList.add('selected');
}
</script>
@endpush
@endsection
