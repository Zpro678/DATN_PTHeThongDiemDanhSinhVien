# Tài khoản Demo — Hệ thống Điểm danh & Chuyên cần

> Dựng lại bằng: `php artisan migrate:fresh --seed` (seeder: `database/seeders/DemoSeeder.php`).
> **Mật khẩu chung cho MỌI tài khoản: `password`**

## Danh sách tài khoản

| Vai trò | Email | Tên | Ghi chú |
|---|---|---|---|
| **ADMIN** | `nguyenkhoi020705@gmail.com` | Nguyễn Quang Lê Khôi | Quản trị hệ thống (`/admin`) |
| **Chủ lớp** (owner) | `minhhieut947@gmail.com` | Trần Minh Hiếu | Sở hữu lớp *Lập trình Web*; có gói **PRO** (mở khoá xuất Excel) |
| **Đồng chủ** (co-owner) | `thaobee2407@gmail.com` | Trần Thị Thu Thảo | Cùng quản lý lớp *Lập trình Web* |
| Sinh viên — **CẤM THI** | `0306231156@caothang.edu.vn` | Nguyễn Tuấn Khanh | Chuyên cần **~73%** (đỏ) |
| Sinh viên — **CẢNH BÁO** | `0306231120@caothang.edu.vn` | Lê Hoàng Nam | Chuyên cần **~83%** (vàng) |
| Sinh viên | `0306231114@caothang.edu.vn` | Phạm Thị Mai | ~97% |
| Sinh viên | `0306231108@caothang.edu.vn` | Võ Minh Quân | 100% |
| Sinh viên | `minhtranz6789@gmail.com` | Trần Gia Bảo | 100% |
| Sinh viên | `minhtranz9867@gmail.com` | Đỗ Thanh Tùng | ~93% |
| Sinh viên | `minhhieut1415@gmail.com` | Bùi Khánh Linh | 100% |

## Lớp học

| Lớp | Mã tham gia (join key) | Trạng thái dữ liệu |
|---|---|---|
| Lập trình Web | `WEB2026` | Đầy đủ: 10 thành viên (7 có TK + 3 chỉ hồ sơ), 7 buổi đã chốt, có SV cấm thi/cảnh báo |
| Cơ sở dữ liệu | `DB2026` | Trống — dùng để demo tạo lớp / import sinh viên |
| Công nghệ phần mềm | `SE2026` | Bật duyệt thành viên — có **2 yêu cầu vào lớp đang chờ** |

## Dữ liệu kèm theo (lớp Lập trình Web)

- **7 buổi** đã chốt (mỗi buổi 1 phiên), tổng dự kiến **15 buổi**.
- **Đơn xin nghỉ:** 1 đã duyệt (SV Võ Minh Quân, buổi 4 → *có phép*) + 1 đang chờ (SV Nguyễn Tuấn Khanh).
- **Yêu cầu vào lớp:** 2 đang chờ duyệt ở lớp `SE2026`.
- **Đồng chủ:** Trần Thị Thu Thảo đã được thêm vào lớp Web.

## Gợi ý demo theo module

| Module | Người | Tài khoản đăng nhập | Thao tác chính |
|---|---|---|---|
| 1 – Xác thực & QL người dùng | Khanh | Chủ lớp (Hiếu) | Tạo lớp `DB2026` → xem MSSV → duyệt yêu cầu ở `SE2026` → thêm đồng chủ |
| 2 – Điểm danh | Hiếu | Chủ lớp (Hiếu) | Mở phiên QR/thủ công lớp Web → demo realtime (SV quét bằng TK sinh viên) |
| 3 – Chuyên cần & Cảnh báo | Thảo | Chủ lớp / Đồng chủ | Lọc "Nguy cơ cấm thi" → SV Khanh (73%) → Gửi cảnh báo |
| 4 – Thống kê & Báo cáo | Khanh | Chủ lớp (Hiếu) | Trang Thống kê lớp Web → biểu đồ → Xuất Excel (PRO) |
| 5 – Quản trị hệ thống | Khôi | Admin (Khôi) | users / gói dịch vụ / giao dịch / cấu hình Telegram |

## Lưu ý

- **Telegram** (Module 3) chỉ gửi tin thật nếu: SV đã nhắn `/start` cho bot (liên kết Chat ID) **và** admin đã nhập Bot Token + bật "Bật thông báo Telegram" trong `/admin/settings`. Nếu chưa kịp, demo tới trang `/student/warnings` là đủ.
- **Realtime** (Module 2) cần **`server.cjs` (Node) và Redis đang chạy** trước khi demo, nếu không màn hình giảng viên sẽ không tự cập nhật.
- Đăng nhập bằng email + mật khẩu `password`. Đăng nhập Google cũng khớp theo email nếu tài khoản Google trùng địa chỉ ở trên.
