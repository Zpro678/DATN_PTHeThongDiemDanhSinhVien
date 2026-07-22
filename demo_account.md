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
| Lập trình Web | `WEB2026` | Đầy đủ: 10 thành viên (7 có TK + 3 chỉ hồ sơ), 8 buổi đã chốt, có SV cấm thi/cảnh báo |
| Cơ sở dữ liệu | `DB2026` | Trống — dùng để demo tạo lớp / import sinh viên |
| Công nghệ phần mềm | `SE2026` | Bật duyệt thành viên — có **2 yêu cầu vào lớp đang chờ** |

## Dữ liệu kèm theo (lớp Lập trình Web)

- **8 buổi** đã chốt, tổng dự kiến **15 buổi**:
  - **Buổi 1–7** (mỗi tuần 1 buổi, buổi 7 cách đây 1 tuần): mỗi buổi **1 phiên**.
  - **Buổi 8** (**hôm nay**): **3 phiên** — *QR đầu giờ → thủ công giữa giờ → QR cuối giờ*. Dựng riêng để demo trang **Tổng kết buổi**, phủ đủ 5 tình huống gộp phiên → buổi (xem bảng dưới).
- **Đơn xin nghỉ:** 2 đã duyệt (buổi 4 → *có phép*; buổi 8 → *có phép*) + 1 đang chờ (SV cấm thi).
- **Yêu cầu vào lớp:** 2 đang chờ duyệt ở lớp `SE2026`.
- **Đồng chủ:** Trần Thị Thu Thảo đã được thêm vào lớp Web.

### Buổi 8 — kịch bản demo "Tổng kết" (1 buổi / 3 phiên)

Vào **lớp Lập trình Web → Điểm danh → Buổi 8 → Tổng kết**. Bảng có 3 cột *Lần 1/2/3* + cột *Tổng kết*:

| Lần 1 (QR) | Lần 2 (thủ công) | Lần 3 (QR) | Tổng kết | Quy tắc minh hoạ |
|---|---|---|---|---|
| Có mặt | Có mặt | Có mặt | **Có mặt** | dự đủ buổi |
| **Vắng** | Có mặt | Có mặt | **Đi muộn** | vắng phiên **đầu** → vào trễ |
| Có mặt | Có mặt | **Vắng** | **Vắng** | vắng phiên **cuối** → bỏ về giữa chừng |
| Có mặt | **Muộn** | Có mặt | **Đi muộn** | có phiên bị đánh dấu muộn |
| Có mặt | **Vắng** | Có mặt | **Có mặt** | hụt phiên **giữa** vẫn tính có mặt |
| Có phép | Có phép | Có phép | **Có phép** | có đơn nghỉ đã duyệt |
| Vắng | Vắng | Vắng | **Vắng** | vắng cả buổi |

> Chốt lại: chuyên cần tính theo **BUỔI**, không theo phiên — buổi 8 có 3 phiên vẫn chỉ chiếm **1** đơn vị trong % chuyên cần. Nút **"Tính lại"** dựng lại cột Tổng kết từ các phiên; sửa tay 1 dòng rồi **Lưu** để demo cờ *"Đã sửa (gốc: …)"* + thông báo gửi học viên.

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
