<x-mail::message>
# Xin chào {{ $studentName }},

Bạn vừa được giảng viên thêm vào danh sách lớp học **{{ $className }}**.

Tài khoản của bạn (email **{{ $email }}**) đã được liên kết với lớp này. Bạn chỉ cần đăng nhập để theo dõi điểm danh, lịch học và nộp đơn xin phép trực tuyến.

<x-mail::button :url="route('login')">
Đăng nhập ngay
</x-mail::button>

Nếu lớp chưa hiển thị sau khi đăng nhập, bạn có thể dùng chức năng **Tham gia lớp** và nhập Mã lớp học: **{{ $classCode }}**.

Trân trọng,<br>
Hệ thống điểm danh
</x-mail::message>
