<x-mail::message>
# Xin chào {{ $studentName }},

Bạn vừa được giảng viên thêm vào danh sách lớp học **{{ $className }}**.

Để theo dõi điểm danh, lịch học và nộp đơn xin phép trực tuyến, bạn vui lòng tạo tài khoản trên hệ thống và sử dụng tính năng "Tham gia lớp".

### Hướng dẫn:
1. Đăng ký tài khoản (nếu chưa có) bằng chính email **{{ $email }}** tại đây:
<x-mail::button :url="route('register', ['email' => $email])">
Đăng ký tài khoản
</x-mail::button>
2. Sau khi đăng nhập, chọn chức năng **Tham gia lớp**.
3. Nhập Mã lớp học: **{{ $classCode }}**

Hệ thống sẽ tự động liên kết tài khoản của bạn với danh sách lớp theo email.

Trân trọng,<br>
Hệ thống điểm danh
</x-mail::message>
