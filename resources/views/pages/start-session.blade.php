@extends('layouts.app')

@section('title', 'Bắt đầu phiên điểm danh - EduCheck Admin')

@section('content')
<div style="max-width:1024px;margin:0 auto;">

    {{-- Breadcrumb --}}
    <div style="display:flex;align-items:center;gap:4px;font-size:14px;color:var(--color-on-surface-variant);margin-bottom:24px;">
        <span>Cài đặt</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <span class="font-medium text-primary">Bắt đầu phiên mới</span>
    </div>

    <header style="margin-bottom:24px;">
        <h2 style="font-size:30px;font-weight:700;margin-bottom:8px;">Bắt đầu phiên điểm danh</h2>
        <p class="text-muted">Cấu hình các thông số và phương thức xác thực cho buổi học hôm nay.</p>
    </header>

    <div class="card" style="display:grid;grid-template-columns:1fr 2fr;overflow:hidden;">

        {{-- Left: Status panel --}}
        <div style="background:#f8faff;padding:32px;display:flex;flex-direction:column;align-items:center;text-align:center;border-right:1px solid var(--color-outline-variant);">
            <div style="width:96px;height:96px;background:var(--color-primary);color:var(--color-on-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:24px;box-shadow:0 8px 24px rgba(0,74,198,.25);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:40px;height:40px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z"/></svg>
            </div>
            <h3 style="font-size:20px;font-weight:700;color:var(--color-primary);margin-bottom:12px;">Chế độ Sẵn sàng</h3>
            <p class="text-muted text-sm" style="line-height:1.6;margin-bottom:32px;">Sau khi bắt đầu, hệ thống sẽ tự động tạo mã QR và kích hoạt các lớp bảo mật AI.</p>

            <div style="width:100%;background:white;border-radius:12px;border:1px solid var(--color-outline-variant);padding:16px;text-align:left;margin-top:auto;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:var(--color-primary);"></div>
                    <span style="font-size:11px;font-weight:700;color:var(--color-on-surface-variant);text-transform:uppercase;letter-spacing:.05em;">Trạng thái hệ thống</span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:14px;">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Máy chủ AI:</span>
                        <span class="font-semibold text-primary">Ổn định</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Định vị GPS:</span>
                        <span class="font-semibold text-primary">Đã sẵn sàng</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Config --}}
        <div style="padding:32px;display:flex;flex-direction:column;">
            <div style="display:flex;flex-direction:column;gap:32px;flex:1;">

                {{-- Class selection --}}
                <div>
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Chọn lớp học</h3>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="class-card selected" data-id="1" onclick="selectClass(1)" style="position:relative;padding:16px;border-radius:12px;border:2px solid var(--color-primary);background:rgba(0,74,198,.05);cursor:pointer;transition:all .2s;">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke="var(--color-primary)" style="position:absolute;top:16px;right:16px;width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <h4 style="font-weight:600;margin-bottom:4px;padding-right:32px;">Lập trình Web nâng cao</h4>
                            <p class="text-muted text-sm">Nhóm 2 • Thứ 4, Tiết 1-3</p>
                        </div>
                        <div class="class-card" data-id="2" onclick="selectClass(2)" style="position:relative;padding:16px;border-radius:12px;border:2px solid var(--color-outline-variant);cursor:pointer;transition:all .2s;" onmouseover="if(!this.classList.contains('selected'))this.style.borderColor='rgba(0,74,198,.4)'" onmouseout="if(!this.classList.contains('selected'))this.style.borderColor='var(--color-outline-variant)'">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke="var(--color-primary)" style="position:absolute;top:16px;right:16px;width:24px;height:24px;display:none;" class="check-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <h4 style="font-weight:600;margin-bottom:4px;padding-right:32px;">Cơ sở dữ liệu phân tán</h4>
                            <p class="text-muted text-sm">Nhóm 1 • Thứ 6, Tiết 4-6</p>
                        </div>
                    </div>
                </div>

                {{-- Time settings --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                    <div>
                        <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Thời gian phản hồi</h3>
                        <div style="position:relative;">
                            <input type="number" value="15" class="form-input" style="padding-right:48px;">
                            <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);color:var(--color-on-surface-variant);font-size:14px;">phút</span>
                        </div>
                    </div>
                    <div>
                        <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Ngưỡng đi muộn</h3>
                        <div style="position:relative;">
                            <input type="number" value="5" class="form-input" style="padding-right:48px;">
                            <span style="position:absolute;right:16px;top:50%;transform:translateY(-50%);color:var(--color-on-surface-variant);font-size:14px;">phút</span>
                        </div>
                    </div>
                </div>

                {{-- Auth methods --}}
                <div>
                    <h3 style="font-size:18px;font-weight:600;margin-bottom:16px;">Phương thức xác thực</h3>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        @foreach([
                            ['icon'=>'shield','label'=>'Chống gian lận AI','desc'=>'Phát hiện thiết bị lạ và hành vi đáng ngờ','checked'=>true,'color'=>'primary'],
                            ['icon'=>'map','label'=>'Định vị GPS','desc'=>'Yêu cầu sinh viên có mặt trong bán kính lớp học','checked'=>false,'color'=>'muted'],
                            ['icon'=>'smile','label'=>'Nhận diện khuôn mặt','desc'=>'Xác thực danh tính qua camera (Yêu cầu Pro)','checked'=>false,'color'=>'muted'],
                        ] as $method)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:12px;border:1px solid var(--color-outline-variant);background:var(--color-surface);">
                            <div style="display:flex;align-items:center;gap:16px;">
                                <div style="width:40px;height:40px;border-radius:50%;background:{{ $method['color']==='primary' ? 'rgba(0,74,198,.1)' : 'var(--color-surface-variant)' }};color:{{ $method['color']==='primary' ? 'var(--color-primary)' : 'var(--color-on-surface-variant)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    @if($method['icon']==='shield')
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                                    @elseif($method['icon']==='map')
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                                    @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 style="font-weight:600;font-size:14px;">{{ $method['label'] }}</h4>
                                    <p class="text-muted text-sm" style="margin-top:2px;">{{ $method['desc'] }}</p>
                                </div>
                            </div>
                            <label class="toggle-wrap">
                                <input type="checkbox" {{ $method['checked'] ? 'checked' : '' }}>
                                <div class="toggle-track"><div class="toggle-thumb"></div></div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div style="padding-top:24px;margin-top:24px;border-top:1px solid var(--color-outline-variant);display:flex;justify-content:flex-end;gap:16px;">
                <a href="{{ route('dashboard') }}" class="btn-secondary">Hủy</a>
                <a href="{{ route('presentation') }}" class="btn-primary">Bắt đầu điểm danh</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectClass(id) {
    document.querySelectorAll('.class-card').forEach(c => {
        const isSelected = parseInt(c.dataset.id) === id;
        c.classList.toggle('selected', isSelected);
        c.style.border = isSelected ? '2px solid var(--color-primary)' : '2px solid var(--color-outline-variant)';
        c.style.background = isSelected ? 'rgba(0,74,198,.05)' : '';
        const icon = c.querySelector('.check-icon');
        if (icon) icon.style.display = isSelected ? 'block' : 'none';
    });
}
</script>
@endpush
@endsection
