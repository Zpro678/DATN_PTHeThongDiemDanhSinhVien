@extends('layouts.app')

@section('title', 'Yêu cầu nghỉ học - EduCheck Admin')

@section('content')
<div style="display:flex;flex-direction:column;height:100%;margin:-24px;">

    {{-- Header --}}
    <header style="padding:20px 24px;border-bottom:1px solid var(--color-outline-variant);background:var(--color-surface-container-lowest);position:sticky;top:0;z-index:30;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
        <h2 style="font-size:24px;font-weight:600;">Danh sách yêu cầu</h2>
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="position:relative;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:18px;height:18px;color:var(--color-on-surface-variant);pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="text" placeholder="Tìm kiếm sinh viên..." class="form-input" style="padding-left:40px;width:256px;">
            </div>
            <div style="position:relative;">
                <select class="form-input" style="padding-right:36px;appearance:none;cursor:pointer;">
                    <option>Tất cả Lớp học</option>
                    <option>CS101 - Lập trình cơ bản</option>
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--color-on-surface-variant);pointer-events:none;"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
            </div>
        </div>
    </header>

    <div style="flex:1;display:flex;overflow:hidden;">

        {{-- List --}}
        <div style="flex:1;display:flex;flex-direction:column;border-right:1px solid var(--color-outline-variant);overflow:hidden;">
            <div class="tab-bar">
                <button class="tab-item active">Chờ duyệt (5)</button>
                <button class="tab-item">Đã duyệt</button>
                <button class="tab-item">Từ chối</button>
            </div>

            <div style="flex:1;overflow-y:auto;background:var(--color-surface-bright);padding:24px;">
                <div class="card" style="overflow:hidden;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sinh viên</th>
                                <th>Lớp học</th>
                                <th>Ngày xin nghỉ</th>
                                <th>Lý do</th>
                                <th style="text-align:right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['name'=>'Nguyễn Văn A','id'=>'SV00123','class'=>'CS101','date'=>'15/10/2023','reason'=>'Ốm sốt cao...','active'=>true],
                                ['name'=>'Trần Thị B','id'=>'SV00145','class'=>'IT4021','date'=>'15/10/2023','reason'=>'Việc gia đình...','active'=>false],
                                ['name'=>'Lê Văn C','id'=>'SV00167','class'=>'CS3050','date'=>'14/10/2023','reason'=>'Khám bệnh định kỳ...','active'=>false],
                            ] as $req)
                            <tr style="{{ $req['active'] ? 'background:rgba(219,225,255,.2);' : '' }}">
                                <td>
                                    <div style="display:flex;align-items:center;gap:12px;">
                                        <img src="https://i.pravatar.cc/150?u={{ $req['id'] }}" alt="Avatar" style="width:32px;height:32px;border-radius:50%;">
                                        <div>
                                            <p style="font-weight:500;">{{ $req['name'] }}</p>
                                            <p style="font-size:10px;color:var(--color-on-surface-variant);">{{ $req['id'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $req['class'] }}</td>
                                <td>{{ $req['date'] }}</td>
                                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $req['reason'] }}</td>
                                <td style="text-align:right;">
                                    <button class="text-primary text-sm font-medium" style="background:none;border:none;cursor:pointer;">Chi tiết</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Detail Panel --}}
        <div style="width:400px;background:var(--color-surface-container-lowest);display:flex;flex-direction:column;height:100%;flex-shrink:0;border-left:1px solid var(--color-outline-variant);">
            <div style="padding:24px;border-bottom:1px solid var(--color-outline-variant);display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;background:var(--color-surface-container-lowest);z-index:10;">
                <h3 style="font-size:20px;font-weight:600;">Chi tiết yêu cầu</h3>
                <button class="icon-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div style="flex:1;overflow-y:auto;padding:24px;display:flex;flex-direction:column;gap:24px;">
                {{-- Student info --}}
                <div style="display:flex;align-items:center;gap:16px;background:var(--color-surface-container-low);padding:16px;border-radius:12px;border:1px solid var(--color-outline-variant);">
                    <img src="https://i.pravatar.cc/150?u=SV00123" alt="Avatar" style="width:64px;height:64px;border-radius:50%;border:2px solid var(--color-surface-container-lowest);box-shadow:0 1px 3px rgba(0,0,0,.1);">
                    <div>
                        <h4 style="font-size:18px;font-weight:600;">Nguyễn Văn A</h4>
                        <p class="text-muted text-sm" style="margin-top:4px;">SV00123</p>
                        <p class="text-primary text-sm" style="margin-top:4px;">Lớp: CS101</p>
                    </div>
                </div>

                {{-- Absence info --}}
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <h5 style="font-size:12px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Thông tin nghỉ học</h5>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="background:var(--color-surface-container-low);padding:12px;border-radius:8px;border:1px solid var(--color-outline-variant);">
                            <p class="text-xs text-muted" style="margin-bottom:4px;">Ngày nghỉ</p>
                            <p class="font-medium">15/10/2023</p>
                        </div>
                        <div style="background:var(--color-surface-container-low);padding:12px;border-radius:8px;border:1px solid var(--color-outline-variant);">
                            <p class="text-xs text-muted" style="margin-bottom:4px;">Thời lượng</p>
                            <p class="font-medium">1 Buổi</p>
                        </div>
                    </div>
                    <div style="background:var(--color-surface-container-low);padding:16px;border-radius:12px;border:1px solid var(--color-outline-variant);">
                        <p class="text-xs font-medium text-muted" style="margin-bottom:8px;">Lý do chi tiết</p>
                        <p class="text-sm" style="line-height:1.6;">Em bị sốt cao từ đêm qua và có đi khám bác sĩ sáng nay. Bác sĩ chỉ định cần nghỉ ngơi 1 ngày. Em xin phép thầy cô cho em nghỉ buổi học hôm nay.</p>
                    </div>
                </div>

                {{-- Attachment --}}
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <h5 style="font-size:12px;font-weight:600;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Minh chứng đính kèm</h5>
                    <div style="border:1px solid var(--color-outline-variant);border-radius:12px;padding:8px;background:var(--color-surface-container-low);">
                        <div style="background:var(--color-surface-container-highest);border-radius:8px;height:128px;display:flex;align-items:center;justify-content:center;border:1px dashed var(--color-outline);cursor:pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="var(--color-outline)" style="width:32px;height:32px;"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13"/></svg>
                        </div>
                        <p class="text-sm text-muted text-center" style="margin-top:8px;padding:0 8px;">giay_kham_benh.jpg</p>
                    </div>
                </div>
            </div>

            <div style="padding:24px;border-top:1px solid var(--color-outline-variant);background:var(--color-surface-container-lowest);display:flex;gap:12px;position:sticky;bottom:0;">
                <button class="btn-outline-error" style="flex:1;">Từ chối</button>
                <button class="btn-primary" style="flex:1;">Duyệt đơn</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.tab-item').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-item').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
@endpush
@endsection
