# README2 - Luồng xử lý dữ liệu Backend ↔ Frontend

 **luồng xử lý mẫu/thiết kế đề xuất**

## 1. Mục đích tài liệu

Tài liệu mô tả cách dữ liệu đi qua lại giữa:

- **Backend**: Laravel 11, Controller, Service, Middleware, Queue, Event, Database.
- **Frontend**: Blade, Livewire, Alpine.js, Tailwind CSS, JavaScript, Chart.js.
- **Realtime**: Redis Pub/Sub, Socket.IO/WebSocket, Event Broadcasting.
- **Hệ thống ngoài**: Google OAuth2, SMTP Mail, PayOS/VietQR/MoMo, Firebase Cloud Messaging nếu triển khai.

Mục tiêu là làm rõ:

- Backend đưa dữ liệu xuống frontend như thế nào.
- Frontend gửi dữ liệu lên backend như thế nào.
- Khi nào dùng form submit, Livewire request, JSON API, upload file, WebSocket, queue, webhook.
- Luồng realtime cập nhật dữ liệu điểm danh, cảnh báo gian lận, thông báo và báo cáo.

## 2. Vai trò tham gia luồng dữ liệu

| Vai trò | Mô tả | Kiểu dữ liệu thường xử lý |
|---|---|---|
| Admin | Quản trị hệ thống, người dùng, gói dịch vụ, doanh thu, log, cấu hình | Dashboard, danh sách user, giao dịch, log, cảnh báo hệ thống |
| Giảng viên / Chủ lớp | Tạo lớp, quản lý sinh viên, tạo phiên điểm danh, xem báo cáo | Lớp học, danh sách sinh viên, phiên điểm danh, kết quả điểm danh, báo cáo |
| Sinh viên có tài khoản | Tham gia lớp, xem lịch sử điểm danh, nhận thông báo | Lớp đã tham gia, trạng thái điểm danh, cảnh báo vắng |
| Sinh viên không có tài khoản | Điểm danh bằng QR/link và nhập MSSV | MSSV, họ tên, GPS, thiết bị, kết quả điểm danh |

## 3. Các kiểu truyền dữ liệu trong hệ thống

| Kiểu truyền dữ liệu | Chiều dữ liệu | Ví dụ sử dụng |
|---|---|---|
| SSR Blade | Backend → Frontend | Render dashboard, form đăng nhập, danh sách lớp, báo cáo |
| Livewire Request | Frontend ↔ Backend | Tìm kiếm, phân trang, lọc báo cáo, cập nhật trạng thái nhanh |
| HTML Form POST | Frontend → Backend | Đăng nhập, đăng ký, tạo lớp, tạo phiên điểm danh, gửi đơn xin phép |
| JSON API | Frontend → Backend | Gửi dữ liệu QR/GPS/device fingerprint, cập nhật realtime-friendly |
| Multipart Upload | Frontend → Backend | Import danh sách sinh viên Excel/CSV, upload minh chứng nghỉ phép |
| File Download | Backend → Frontend | Xuất Excel/PDF báo cáo điểm danh |
| WebSocket / Socket.IO | Backend → Frontend | Cập nhật số lượng điểm danh realtime, cảnh báo gian lận |
| Queue / Job nội bộ | Backend → Backend | Gửi email, tính thống kê, phân tích gian lận, export báo cáo |
| OAuth Redirect | Frontend ↔ Google ↔ Backend | Đăng nhập bằng Google |
| Payment Webhook | PayOS → Backend | Xác nhận thanh toán nâng cấp gói Pro |
| Email / Push Notification | Backend → User | Quên mật khẩu, cảnh báo vắng, thông báo hệ thống |

## 4. Kiến trúc luồng dữ liệu tổng quát

### 4.1. Backend render dữ liệu xuống frontend

```text
Browser
  |
  | GET /teacher/classes, /student/classes, /admin/dashboard
  v
Laravel Route
  |
  v
Middleware
  |-- auth
  |-- role / permission
  |-- owner / tenant check
  |-- plan check nếu cần
  v
Controller hoặc Livewire Component
  |
  v
Service / Eloquent Query
  |
  v
MySQL
  |
  v
Blade View + Tailwind CSS + Alpine.js + Chart.js
  |
  v
HTML hiển thị trên trình duyệt
```

### 4.2. Frontend gửi dữ liệu lên backend

```text
Người dùng thao tác UI
  |
  | Form submit / Livewire action / fetch JSON / upload file
  v
Laravel Route
  |
  v
CSRF + Auth + Role + Validation
  |
  v
Controller
  |
  v
Service xử lý nghiệp vụ
  |
  v
Database Transaction
  |
  |-- Ghi dữ liệu chính
  |-- Ghi audit log
  |-- Dispatch Job nếu xử lý nền
  |-- Broadcast Event nếu cần realtime
  v
Response
  |
  | Redirect + flash / JSON / Blade / File download
  v
Frontend cập nhật giao diện
```

### 4.3. Luồng realtime

```text
Laravel Application
  |
  | Fire Business Event
  v
Laravel Event / Broadcast
  |
  v
Redis Pub/Sub
  |
  v
Socket.IO Server
  |
  v
Connected Clients
  |
  |-- Dashboard giảng viên
  |-- Trang điểm danh sinh viên
  |-- Dashboard admin
```

### 4.4. Luồng xử lý nền bằng queue

```text
Controller / Service
  |
  | Dispatch Job
  v
Redis Queue
  |
  v
Queue Worker / Supervisor / Horizon
  |
  |-- Gửi email
  |-- Tính tỷ lệ vắng
  |-- Phân tích gian lận
  |-- Import Excel theo chunk
  |-- Export Excel/PDF
  v
Database + Event + Notification
```

## 5. Backend đưa dữ liệu xuống frontend

### 5.1. Render trang bằng Blade

Backend lấy dữ liệu từ database rồi truyền xuống Blade view.

Ví dụ màn hình:

- Trang chủ `/`.
- Đăng nhập `/login`.
- Đăng ký `/register`.
- Dashboard giảng viên `/teacher/classes`.
- Dashboard sinh viên `/student/classes`.
- Dashboard admin `/admin/dashboard`.
- Báo cáo `/teacher/reports`.

Luồng mẫu:

```text
GET /teacher/classes
  -> Auth middleware kiểm tra đăng nhập
  -> Role middleware kiểm tra quyền chủ lớp
  -> Controller lấy danh sách lớp theo owner_id
  -> Truyền biến $classes sang Blade
  -> Blade render HTML bằng Tailwind
  -> Browser hiển thị danh sách lớp
```

Dữ liệu backend đưa xuống frontend:

- Danh sách lớp.
- Danh sách sinh viên.
- Trạng thái phiên điểm danh.
- Kết quả điểm danh.
- Thông báo flash success/error.
- Dữ liệu biểu đồ cho Chart.js.
- Quyền hiện tại của user để ẩn/hiện nút chức năng.

### 5.2. Render component động bằng Livewire

Livewire dùng cho các phần cần tương tác nhưng không muốn viết quá nhiều JavaScript.

Trường hợp phù hợp:

- Tìm kiếm lớp.
- Lọc sinh viên.
- Phân trang danh sách điểm danh.
- Lọc báo cáo theo lớp, học kỳ, ngày.
- Cập nhật trạng thái điểm danh thủ công.

Luồng mẫu:

```text
User nhập ô tìm kiếm
  -> Livewire gửi AJAX request
  -> Component cập nhật state
  -> Backend query lại database
  -> Backend trả HTML diff
  -> Livewire thay đổi DOM tương ứng
```

### 5.3. Dữ liệu biểu đồ cho Chart.js

Backend tổng hợp dữ liệu, frontend chỉ nhận dataset đã chuẩn hóa.

Ví dụ:

```text
GET /teacher/reports?class_id=10&semester=2025A
  -> Backend lấy attendance_records
  -> Tính tổng có mặt, đi muộn, vắng, vắng có phép
  -> Tạo dataset cho Chart.js
  -> Blade render script hoặc JSON data attribute
  -> Chart.js vẽ biểu đồ trên frontend
```

Dataset mẫu:

```json
{
  "labels": ["Có mặt", "Muộn", "Vắng", "Vắng có phép"],
  "datasets": [
    {
      "label": "Kết quả điểm danh",
      "data": [42, 3, 5, 1]
    }
  ]
}
```

### 5.4. File download

Dùng khi giảng viên/admin xuất báo cáo Excel hoặc PDF.

Luồng mẫu:

```text
User click "Xuất Excel"
  -> Frontend gửi request có filter
  -> Backend kiểm tra quyền và gói dịch vụ
  -> Backend tạo file Excel/PDF
  -> Nếu file nhỏ: trả về response download ngay
  -> Nếu file lớn: dispatch job export và trả thông báo "Đang xử lý"
```

Response download mẫu:

```text
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment; filename="bao-cao-diem-danh.xlsx"
```

### 5.5. Thông báo từ backend xuống frontend

Các kiểu thông báo:

- Flash message sau redirect.
- Toast bằng Alpine.js.
- Realtime event qua Socket.IO.
- Email qua SMTP.
- Push notification qua Firebase Cloud Messaging nếu triển khai.

Ví dụ:

```text
POST /classes
  -> Tạo lớp thành công
  -> Redirect /teacher/classes
  -> Session flash: "Tạo lớp thành công"
  -> Blade hiển thị toast trong 3 giây
```

## 6. Frontend gửi dữ liệu lên backend

### 6.1. HTML form submit

Dùng cho các tác vụ truyền thống:

- Đăng ký.
- Đăng nhập.
- Tạo lớp.
- Cập nhật lớp.
- Tạo phiên điểm danh.
- Gửi đơn xin phép.

Luồng mẫu:

```text
User nhập form
  -> Browser gửi POST kèm CSRF token
  -> Laravel validate dữ liệu
  -> Nếu lỗi: redirect back + error bag + old input
  -> Nếu đúng: ghi database
  -> Redirect về màn hình phù hợp
```

### 6.2. Livewire action

Dùng khi muốn thao tác nhanh mà không reload cả trang.

Ví dụ:

- Tích chọn sinh viên có mặt/vắng.
- Lọc danh sách.
- Chuyển trang phân trang.
- Duyệt đơn xin phép.

Luồng mẫu:

```text
Click nút "Duyệt"
  -> Livewire gọi method approveLeaveRequest()
  -> Backend kiểm tra quyền giảng viên
  -> Cập nhật leave_requests.status = approved
  -> Cập nhật attendance_records.status = excused nếu cần
  -> Component render lại danh sách
```

### 6.3. JSON API bằng fetch/Axios

Dùng cho luồng cần phản hồi nhanh hoặc dễ tích hợp JavaScript.

Trường hợp tiêu biểu:

- Gửi GPS khi điểm danh QR.
- Gửi device fingerprint.
- Cập nhật token FCM.
- Kiểm tra QR còn hạn.
- Ghi nhận scan thất bại.

Request mẫu khi sinh viên điểm danh:

```json
{
  "student_code": "SV001",
  "full_name": "Nguyễn Văn A",
  "qr_token": "signed-token",
  "latitude": 10.762622,
  "longitude": 106.660172,
  "accuracy": 25,
  "device_id": "browser-fingerprint",
  "client_time": "2026-06-10T09:00:00+07:00"
}
```

Response thành công:

```json
{
  "success": true,
  "message": "Điểm danh thành công",
  "data": {
    "status": "present",
    "check_in_time": "2026-06-10T09:00:05+07:00",
    "class_session_id": 15
  }
}
```

Response thất bại:

```json
{
  "success": false,
  "message": "Bạn đang ở ngoài phạm vi điểm danh",
  "errors": {
    "distance": 120,
    "allowed_radius": 50
  }
}
```

### 6.4. Multipart upload

Dùng khi frontend gửi file lên backend.

Trường hợp:

- Import danh sách sinh viên Excel/CSV.
- Upload ảnh minh chứng nghỉ phép.
- Upload avatar nếu có.

Luồng mẫu:

```text
Frontend chọn file
  -> POST multipart/form-data
  -> Backend kiểm tra extension, MIME, dung lượng
  -> Lưu file tạm trong storage
  -> Đọc dữ liệu bằng Laravel Excel hoặc League CSV
  -> Validate từng dòng
  -> Nếu lỗi: trả danh sách dòng lỗi
  -> Nếu đúng: ghi database hoặc dispatch import job
```

### 6.5. WebSocket từ backend xuống frontend

Frontend không chủ động reload, mà mở kết nối WebSocket để nhận sự kiện.

Luồng mẫu:

```text
Frontend mở Socket.IO connection
  -> Gửi token/session để xác thực
  -> Backend/Socket server kiểm tra quyền
  -> Client join room class_10 hoặc teacher_5
  -> Khi có event, server push dữ liệu xuống client
  -> Frontend cập nhật danh sách/số lượng/toast
```

### 6.6. External callback/webhook

Dùng khi hệ thống ngoài gửi dữ liệu về backend.

Trường hợp:

- Google OAuth2 callback.
- PayOS payment webhook.
- Mail provider callback nếu có.

Luồng mẫu:

```text
PayOS gửi POST webhook
  -> Backend xác thực chữ ký HMAC-SHA256
  -> Tìm transaction theo orderCode
  -> Cập nhật transaction success
  -> Nâng cấp subscription sang PRO
  -> Ghi audit log
```

## 7. Luồng xử lý chi tiết theo từng trường hợp

## 7.1. Đăng ký tài khoản

```text
Frontend
  -> GET /register
  -> Nhận form Blade
  -> POST /register với name, email, password

Backend
  -> Validate email unique, password hợp lệ
  -> Hash password
  -> Tạo users
  -> Gán role mặc định user nếu áp dụng RBAC
  -> Tạo session đăng nhập
  -> Redirect dashboard
```

Dữ liệu gửi lên:

- `name`
- `email`
- `password`
- `password_confirmation`
- `_token`

Dữ liệu trả xuống:

- Redirect URL.
- Session đăng nhập.
- Flash message hoặc validation errors.

## 7.2. Đăng nhập bằng email/password

```text
Frontend POST /login
  -> Backend kiểm tra credentials
  -> Nếu sai: trả error bag
  -> Nếu đúng: tạo session
  -> Điều hướng theo vai trò
```

Điều hướng mẫu:

- Admin → `/admin/dashboard`.
- Giảng viên/chủ lớp → `/teacher/classes`.
- Sinh viên → `/student/classes`.

## 7.3. Đăng nhập bằng Google OAuth2

```text
User click "Đăng nhập Google"
  -> GET /auth/google
  -> Backend tạo state và redirect sang Google
  -> Google xác thực người dùng
  -> Google redirect về /auth/google/callback?code=...&state=...
  -> Backend kiểm tra state
  -> Backend đổi code lấy profile
  -> updateOrCreate user theo email/google_id
  -> Auth::login()
  -> Redirect dashboard
```

Mapping dữ liệu:

| Dữ liệu Google | Bảng `users` |
|---|---|
| `id` | `google_id` |
| `name` | `name` |
| `email` | `email` |
| `avatar` | `avatar` |

Trường hợp lỗi:

- Sai hoặc hết hạn `state`.
- Người dùng bấm từ chối quyền.
- Google không trả email.
- Network lỗi.
- Sai `client_id` hoặc `client_secret`.

## 7.4. Tạo lớp học

```text
Giảng viên mở form tạo lớp
  -> Backend render form
  -> Frontend POST thông tin lớp
  -> Backend kiểm tra quyền user
  -> Backend validate mã lớp không trùng trong tenant
  -> Ghi bảng classes
  -> Ghi audit_logs
  -> Redirect danh sách lớp
```

Dữ liệu gửi lên:

- `code`
- `name`
- `description`
- `subject_code`
- `semester`
- `require_approval`
- `total_sessions`
- `lessons_per_session`

Dữ liệu ghi xuống database:

- `classes.tenant_id`
- `classes.owner_id`
- `classes.status`
- `classes.created_at`
- `audit_logs.action = CLASS_CREATED`

## 7.5. Sinh viên tham gia lớp

Có hai hướng xử lý.

### Trường hợp 1: Sinh viên có tài khoản

```text
Sinh viên nhập mã mời hoặc mở link mời
  -> POST /classes/join
  -> Backend kiểm tra lớp tồn tại
  -> Nếu lớp yêu cầu duyệt: tạo class_join_requests pending
  -> Nếu không yêu cầu duyệt: tạo hoặc liên kết class_members.user_id
  -> Trả thông báo kết quả
```

### Trường hợp 2: Sinh viên chưa có tài khoản

```text
Giảng viên import danh sách sinh viên trước
  -> class_members.user_id = null
  -> Sinh viên điểm danh bằng MSSV
  -> Sau này sinh viên đăng ký tài khoản
  -> Hệ thống late binding theo email hoặc student_code
  -> Cập nhật class_members.user_id
```

## 7.6. Import danh sách sinh viên Excel/CSV

```text
Giảng viên chọn file Excel/CSV
  -> POST /teacher/classes/{class}/students/import
  -> Backend kiểm tra auth + owner class
  -> Backend kiểm tra file thật: extension, MIME, size
  -> Đọc header và map cột
  -> Validate từng dòng
  -> Nếu có lỗi: trả danh sách lỗi, không ghi DB
  -> Nếu hợp lệ: DB transaction
  -> updateOrCreate class_members theo student_code
  -> Ghi import log/audit log
  -> Trả thống kê created/updated
```

Cột dữ liệu mẫu:

| Cột | Bắt buộc | Ghi chú |
|---|---|---|
| `student_code` | Có | Không được rỗng, không trùng trong cùng lớp |
| `full_name` | Có | Họ tên sinh viên |
| `email` | Không | Dùng để late binding nếu có |
| `class` | Không | Có thể dùng để đối chiếu |
| `absent_count` | Không | Số nguyên không âm nếu import lịch sử |
| `attendance_score` | Không | Điểm trong khoảng 0-10 nếu có |

Với file lớn:

```text
Upload file
  -> Tách chunk 100 dòng
  -> Dispatch ImportStudentChunkJob vào queue import_chunks
  -> Worker xử lý từng chunk
  -> Nếu chunk lỗi: ghi failed_jobs, retry chunk
  -> Khi xong: gửi notification cho giảng viên
```

## 7.7. Điểm danh thủ công

```text
Giảng viên chọn lớp
  -> Tạo phiên điểm danh thủ công
  -> Backend tạo class_sessions.status = active
  -> Backend lấy class_members
  -> Frontend hiển thị danh sách sinh viên
  -> Giảng viên chọn trạng thái từng sinh viên
  -> Frontend gửi Livewire action hoặc POST
  -> Backend validate quyền owner
  -> Backend upsert attendance_records
  -> Backend cập nhật thống kê tạm
  -> Frontend cập nhật UI
  -> Giảng viên đóng phiên
  -> Backend khóa class_sessions.status = closed
  -> Dispatch job tính tổng kết
```

Trạng thái điểm danh mẫu:

- `pending`: Chưa điểm danh.
- `present`: Có mặt.
- `late`: Đi muộn.
- `absent`: Vắng không phép.
- `excused`: Vắng có phép.
- `invalid`: Không hợp lệ.

Request mẫu:

```json
{
  "class_session_id": 15,
  "records": [
    {
      "class_member_id": 101,
      "status": "present",
      "note": null
    },
    {
      "class_member_id": 102,
      "status": "absent",
      "note": "Không có mặt khi gọi tên"
    }
  ]
}
```

Dữ liệu backend trả xuống:

```json
{
  "success": true,
  "summary": {
    "present": 40,
    "late": 2,
    "absent": 3,
    "excused": 1
  }
}
```

## 7.8. Tạo phiên điểm danh bằng QR/GPS

```text
Giảng viên mở màn hình tạo điểm danh
  -> Browser xin GPS của giảng viên
  -> Frontend gửi class_id, GPS gốc, bán kính, thời hạn QR
  -> Backend kiểm tra owner lớp
  -> Backend tạo class_sessions
  -> Backend sinh qr_token hoặc signed token
  -> Backend lưu token_expires_at, gps_latitude, gps_longitude, gps_radius
  -> Backend trả QR/link về frontend
  -> Frontend hiển thị QR lớn cho sinh viên quét
```

Request mẫu:

```json
{
  "class_id": 10,
  "method": "qr_gps",
  "ttl_minutes": 15,
  "gps_latitude": 10.762622,
  "gps_longitude": 106.660172,
  "gps_radius": 50
}
```

Response mẫu:

```json
{
  "success": true,
  "data": {
    "class_session_id": 15,
    "qr_url": "https://domain.test/attendance/signed-token",
    "expires_at": "2026-06-10T09:15:00+07:00"
  }
}
```

## 7.9. Sinh viên điểm danh bằng QR/link/GPS

```text
Sinh viên quét QR hoặc mở link
  -> GET /attendance/{qr_token}
  -> Backend kiểm tra token tồn tại, chưa hết hạn, phiên active
  -> Backend render trang nhập MSSV hoặc xác nhận nếu đã đăng nhập
  -> Browser xin quyền GPS
  -> Frontend lấy latitude, longitude, accuracy
  -> Frontend lấy device fingerprint
  -> Frontend POST dữ liệu điểm danh
  -> Backend verify token, thời hạn, session, GPS, device, MSSV
  -> Nếu hợp lệ: ghi attendance_records và check_in_scans
  -> Nếu không hợp lệ: ghi check_in_scans với fail_reason
  -> Backend dispatch audit/fraud/statistic jobs
  -> Backend broadcast AttendanceUpdate
  -> Frontend sinh viên hiển thị thành công/thất bại
  -> Dashboard giảng viên cập nhật realtime
```

Backend kiểm tra lần lượt:

1. Token có đúng chữ ký/HMAC không.
2. Token còn hạn không.
3. Phiên điểm danh có đang `active` không.
4. MSSV có trong `class_members` không.
5. Sinh viên đã điểm danh trong phiên này chưa.
6. GPS có nằm trong bán kính cho phép không.
7. Độ chính xác GPS có hợp lệ không.
8. Device/IP có dấu hiệu trùng bất thường không.

Trường hợp lỗi:

| Lỗi | Backend xử lý | Frontend hiển thị |
|---|---|---|
| QR hết hạn | Không ghi attendance_records, ghi failed scan | `Mã điểm danh đã hết hạn` |
| GPS bị từ chối | Không cho gửi điểm danh | `Vui lòng bật vị trí` |
| Ngoài bán kính | Ghi check_in_scans fail_reason | `Bạn đang ở ngoài phạm vi điểm danh` |
| MSSV không thuộc lớp | Từ chối request | `Mã sinh viên không có trong lớp` |
| Đã điểm danh | Không tạo bản ghi trùng | `Bạn đã điểm danh phiên này` |
| Phiên đã đóng | Từ chối | `Phiên điểm danh đã kết thúc` |
| Device nghi vấn | Ghi log, có thể cho pending hoặc invalid | Cảnh báo tùy chính sách |

## 7.10. Hiển thị điểm danh realtime trên dashboard giảng viên

```text
Sinh viên điểm danh thành công
  -> Backend insert attendance_records
  -> Backend fire AttendanceProcessed event
  -> Event publish sang Redis
  -> Socket.IO server nhận message
  -> Socket.IO emit vào room class_{class_id}
  -> Dashboard giảng viên nhận AttendanceUpdate
  -> Frontend thêm sinh viên vào danh sách realtime
  -> Frontend tăng số lượng đã điểm danh
  -> Frontend cập nhật biểu đồ/tỷ lệ
```

Event mẫu:

```json
{
  "event": "AttendanceUpdate",
  "channel": "class_10",
  "data": {
    "class_session_id": 15,
    "student_code": "SV001",
    "full_name": "Nguyễn Văn A",
    "status": "present",
    "method": "qr_gps",
    "check_in_time": "2026-06-10T09:00:05+07:00",
    "is_verified": true
  }
}
```

Frontend xử lý:

- Nếu sinh viên chưa có trong danh sách realtime: thêm mới.
- Nếu sinh viên đã tồn tại: cập nhật trạng thái.
- Tăng counter `present_count`.
- Hiển thị toast.
- Nếu có cảnh báo gian lận: đổi màu dòng hoặc mở panel cảnh báo.

## 7.11. Cảnh báo gian lận realtime

```text
Backend nhận dữ liệu điểm danh
  -> Ghi dữ liệu cơ bản trước
  -> Dispatch FraudDetectionJob
  -> Worker phân tích IP, GPS, DeviceID, Token
  -> Nếu nghi vấn: tạo SecurityAlert/FraudDetected event
  -> Socket.IO gửi vào teacher_{teacher_id}
  -> Dashboard giảng viên hiện cảnh báo đỏ
```

Security event mẫu:

```json
{
  "event": "SecurityAlert",
  "channel": "teacher_5",
  "data": {
    "level": "warning",
    "type": "duplicate_device",
    "message": "Một thiết bị đang điểm danh cho nhiều MSSV",
    "class_session_id": 15,
    "student_codes": ["SV001", "SV009"],
    "device_id": "browser-fingerprint"
  }
}
```

Dữ liệu cần ghi log:

- `ip_address`
- `device_info`
- `student_code_attempt`
- `scan_type`
- `is_valid`
- `fail_reason`
- `scanned_at`
- `payload_signature`

## 7.12. Đóng phiên điểm danh

```text
Giảng viên click "Đóng phiên"
  -> Frontend gửi PATCH /teacher/sessions/{id}/close
  -> Backend kiểm tra owner class
  -> Backend cập nhật class_sessions.status = closed
  -> Backend khóa QR/link
  -> Dispatch RecalculateAttendanceChunkJob
  -> Broadcast SessionControl event
  -> Frontend giảng viên cập nhật trạng thái
  -> Frontend sinh viên không còn gửi điểm danh được
```

Event mẫu:

```json
{
  "event": "SessionControl",
  "channel": "class_10",
  "data": {
    "class_session_id": 15,
    "status": "closed",
    "message": "Phiên điểm danh đã kết thúc"
  }
}
```

## 7.13. Sinh viên xem lịch sử điểm danh

```text
Sinh viên đăng nhập
  -> GET /student/attendance
  -> Backend lấy class_members theo user_id
  -> Backend join attendance_records và class_sessions
  -> Backend trả Blade view
  -> Frontend hiển thị lịch sử, tỷ lệ vắng, cảnh báo
```

Dữ liệu hiển thị:

- Tên lớp.
- Buổi học.
- Ngày học.
- Trạng thái điểm danh.
- Giờ check-in.
- Phương thức điểm danh.
- Tỷ lệ vắng.
- Cảnh báo cấm thi nếu vượt ngưỡng.

## 7.14. Sinh viên gửi đơn xin nghỉ phép

```text
Sinh viên mở form xin nghỉ
  -> Chọn lớp, buổi học, lý do, ảnh minh chứng
  -> POST multipart/form-data
  -> Backend validate quyền thành viên lớp
  -> Backend lưu file vào storage
  -> Backend tạo leave_requests.status = pending
  -> Backend gửi notification realtime cho giảng viên
  -> Giảng viên duyệt/từ chối
  -> Nếu duyệt: attendance_records.status = excused
  -> Backend broadcast kết quả cho sinh viên
```

Request dữ liệu:

- `class_session_id`
- `reason`
- `proof_image`
- `_token`

Kết quả:

- `pending`: chờ duyệt.
- `approved`: được duyệt, điểm danh chuyển sang vắng có phép.
- `rejected`: bị từ chối, giữ trạng thái vắng.

## 7.15. Báo cáo và biểu đồ điểm danh

```text
Giảng viên chọn bộ lọc báo cáo
  -> class_id, semester, date_from, date_to
  -> Backend kiểm tra owner
  -> Query attendance_records
  -> Aggregate theo sinh viên/buổi/trạng thái
  -> Tính tỷ lệ vắng
  -> Trả dữ liệu cho Blade/Livewire
  -> Chart.js vẽ biểu đồ
```

Dữ liệu tính toán:

- Tổng số buổi.
- Số buổi có mặt.
- Số buổi đi muộn.
- Số buổi vắng.
- Số buổi vắng có phép.
- Tỷ lệ vắng.
- Danh sách sinh viên có nguy cơ bị cấm thi.

## 7.16. Export báo cáo Excel/PDF

```text
User click Export
  -> Frontend gửi filter
  -> Backend kiểm tra quyền và gói Pro nếu áp dụng
  -> Nếu dữ liệu nhỏ: tạo file và trả download
  -> Nếu dữ liệu lớn: dispatch ExportAttendanceChunkJob
  -> Worker tạo từng phần
  -> MergeExportFileJob ghép file
  -> Lưu đường dẫn file
  -> Broadcast ReportReady event
  -> Frontend hiển thị nút tải file
```

Event mẫu:

```json
{
  "event": "ReportReady",
  "channel": "teacher_5",
  "data": {
    "report_type": "attendance_excel",
    "download_url": "/teacher/reports/download/abc123",
    "message": "Báo cáo đã sẵn sàng"
  }
}
```

## 7.17. Cảnh báo vắng và email

```text
Phiên điểm danh được đóng
  -> Backend dispatch RecalculateAttendanceChunkJob
  -> Worker tính tỷ lệ vắng từng sinh viên
  -> Nếu vượt ngưỡng cảnh báo: dispatch SendAttendanceWarningJob
  -> Mail worker gửi email
  -> Cập nhật warning_sent_at để tránh gửi lặp
  -> Có thể broadcast notification cho sinh viên
```

Email cảnh báo gồm:

- Tên sinh viên.
- Tên lớp.
- Tỷ lệ vắng hiện tại.
- Ngưỡng cảnh báo.
- Khuyến nghị liên hệ giảng viên.

## 7.18. Quên mật khẩu

```text
User nhập email tại /forgot-password
  -> Backend validate email
  -> Tạo token 64 ký tự
  -> Lưu password_reset_tokens trong 60 phút
  -> Gửi email reset password
  -> User click link
  -> Backend kiểm tra token
  -> User nhập password mới
  -> Backend hash password
  -> Xóa token sau khi dùng
```

Kiểu dữ liệu:

- Form POST từ frontend.
- Email từ backend.
- Token reset truyền qua URL.

## 7.19. Thanh toán nâng cấp gói Pro

```text
User chọn nâng cấp Pro
  -> Frontend gửi package_id/duration
  -> Backend tạo transaction pending
  -> Backend gọi PayOS tạo payment link
  -> Frontend redirect sang trang thanh toán
  -> User thanh toán
  -> PayOS gọi webhook về backend
  -> Backend xác thực chữ ký HMAC-SHA256
  -> Backend cập nhật transactions.status = success
  -> Backend cập nhật subscriptions plan PRO
  -> Backend ghi audit_logs
  -> User quay về returnUrl và thấy trạng thái Pro
```

Payload tạo thanh toán mẫu:

```json
{
  "orderCode": 100001,
  "amount": 99000,
  "description": "DD T159 PRO 1T",
  "buyerName": "Nguyễn Văn A",
  "buyerEmail": "user@example.com",
  "returnUrl": "https://domain.test/billing/success",
  "cancelUrl": "https://domain.test/billing/cancel"
}
```

Quy tắc mô tả giao dịch theo tài liệu:

- Tối đa 25 ký tự.
- Không dấu.
- Không ký tự đặc biệt.
- Mẫu: `DD T159 PRO 6T`.

## 7.20. Admin quản lý người dùng

```text
Admin đăng nhập
  -> GET /admin/users
  -> Backend kiểm tra role admin
  -> Backend lấy danh sách users, role, status, tenant/subscription
  -> Blade hiển thị bảng
  -> Admin khóa/mở user
  -> PATCH /admin/users/{id}/status
  -> Backend cập nhật users.status
  -> Ghi audit_logs
  -> Trả redirect hoặc JSON
```

Dữ liệu admin thường xem:

- Người dùng.
- Trạng thái tài khoản.
- Gói dịch vụ.
- Giao dịch.
- Log hệ thống.
- Cảnh báo queue/Redis/socket.

## 7.21. Admin gửi thông báo hàng loạt

```text
Admin soạn tiêu đề/nội dung/nhóm nhận
  -> POST /admin/notifications/broadcast
  -> Backend validate quyền admin
  -> Query danh sách người nhận
  -> Chunk 50 người/lần
  -> Dispatch BroadcastMailJob hoặc PushNotificationJob
  -> Worker gửi thông báo
  -> Ghi broadcast_logs
  -> Admin xem thống kê success/failed
```

## 8. Realtime architecture chi tiết

### 8.1. Channel/room mẫu

| Channel | Người nhận | Dữ liệu realtime |
|---|---|---|
| `class_{class_id}` | Giảng viên và sinh viên trong lớp | Mở/đóng điểm danh, cập nhật số lượng, trạng thái phiên |
| `teacher_{user_id}` | Giảng viên cụ thể | Cảnh báo gian lận, đơn nghỉ phép, export xong |
| `student_{user_id}` | Sinh viên cụ thể | Kết quả điểm danh, đơn nghỉ phép, cảnh báo vắng |
| `admin_dashboard` | Admin | Queue lỗi, Redis lỗi, socket lỗi, cảnh báo hệ thống |

### 8.2. Event realtime mẫu

| Event | Khi nào phát sinh | Frontend xử lý |
|---|---|---|
| `AttendanceUpdate` | Sinh viên điểm danh thành công/thay đổi trạng thái | Cập nhật danh sách, counter, toast |
| `SecurityAlert` | Phát hiện GPS/device/IP/token bất thường | Hiển thị cảnh báo đỏ cho giảng viên |
| `SessionControl` | Mở/đóng/khóa phiên điểm danh | Bật/tắt form điểm danh |
| `LeaveRequestCreated` | Sinh viên gửi đơn nghỉ phép | Giảng viên nhận notification |
| `LeaveRequestReviewed` | Giảng viên duyệt/từ chối đơn | Sinh viên nhận kết quả |
| `ReportReady` | Export báo cáo hoàn tất | Hiện link tải file |
| `SystemAlert` | Redis/queue/socket/mail gặp lỗi | Admin nhận cảnh báo |

### 8.3. Bảo mật realtime

- Kết nối realtime nên dùng `wss://`.
- Client phải xác thực bằng session/Sanctum/JWT trước khi join room.
- Middleware kiểm tra user có thuộc lớp không trước khi join `class_{class_id}`.
- Không gửi password, raw QR token, access token hoặc dữ liệu nhạy cảm qua event.
- Rate limit số lần connect/reconnect.
- Ghi log các kết nối bất thường.

## 9. Queue và job xử lý nền

Theo tài liệu, các queue mẫu nên tách theo mục đích:

| Queue | Job mẫu | Mục đích |
|---|---|---|
| `attendance_logs` | `SaveAuditLogChunkJob`, `SecurityLogJob` | Ghi audit/security log |
| `fraud_detection` | `FraudDetectionChunkJob` | Phân tích gian lận sau điểm danh |
| `attendance_calculation` | `RecalculateAttendanceChunkJob` | Tính tỷ lệ có mặt/vắng/cấm thi |
| `import_chunks` | `ImportStudentChunkJob` | Import Excel/CSV theo chunk |
| `excel_exports` | `ExportAttendanceChunkJob`, `MergeExportFileJob` | Xuất báo cáo Excel/PDF |

Worker command mẫu:

```bash
php artisan queue:work redis --queue=attendance_logs --tries=3 --timeout=60
php artisan queue:work redis --queue=fraud_detection --tries=3 --timeout=120
php artisan queue:work redis --queue=attendance_calculation --tries=3 --timeout=120
php artisan queue:work redis --queue=excel_exports --tries=3 --timeout=300
php artisan queue:work redis --queue=import_chunks --tries=3 --timeout=300
```

Luồng lỗi queue:

```text
Job chạy lỗi
  -> Retry theo tries
  -> Nếu vẫn lỗi: ghi failed_jobs
  -> Admin/giảng viên nhận cảnh báo nếu tác vụ ảnh hưởng người dùng
  -> Không cần chạy lại toàn bộ batch, chỉ retry chunk lỗi
```

## 10. Database liên quan đến các luồng dữ liệu

| Bảng | Vai trò trong luồng dữ liệu |
|---|---|
| `users` | Tài khoản admin, giảng viên, sinh viên |
| `roles`, `permissions` | RBAC, phân quyền truy cập |
| `tenants` | Không gian dữ liệu của user/chủ lớp nếu triển khai SaaS |
| `tenant_settings` | Cấu hình mặc định GPS, cảnh báo vắng |
| `classes` | Lớp học do giảng viên/chủ lớp tạo |
| `class_members` | Danh sách sinh viên trong lớp, có thể có hoặc chưa có tài khoản |
| `class_join_requests` | Yêu cầu tham gia lớp |
| `class_sessions` | Phiên điểm danh từng buổi |
| `attendance_methods` | Phương thức manual, QR, GPS, link |
| `attendance_records` | Kết quả điểm danh chính thức |
| `attendance_summaries` | Tổng hợp tỷ lệ điểm danh theo sinh viên/lớp |
| `check_in_scans` | Log mọi lần scan QR/link, kể cả thất bại |
| `audit_logs` | Nhật ký thao tác quan trọng |
| `attendance_rules` | Ngưỡng cảnh báo/cấm thi |
| `leave_requests` | Đơn xin nghỉ phép |
| `notifications` | Thông báo trong hệ thống |
| `user_devices` | FCM token/device nếu dùng push |
| `plans` | Gói Free/Pro |
| `subscriptions` | Gói đang sử dụng của tenant/user |
| `transactions` | Giao dịch thanh toán |

## 11. Bảng tổng hợp luồng dữ liệu theo màn hình

| Màn hình | Backend → Frontend | Frontend → Backend | Realtime/Queue |
|---|---|---|---|
| `/login` | Form đăng nhập, lỗi validate | Email/password | Không |
| `/register` | Form đăng ký | Name/email/password | Không |
| `/auth/google` | Redirect sang Google | Callback code/state | Không |
| `/teacher/classes` | Danh sách lớp | Tạo/sửa/xóa lớp | Audit log |
| `/teacher/classes/{id}/students` | Danh sách sinh viên | Import Excel, thêm/sửa sinh viên | Import queue nếu file lớn |
| `/teacher/classes/{id}/attendance/create` | Form tạo phiên, cấu hình QR/GPS | TTL, GPS gốc, radius | Broadcast mở phiên |
| `/teacher/live/{session_id}` | Realtime dashboard | Đóng phiên, sửa trạng thái | Socket.IO, fraud queue |
| `/attendance/{qr_token}` | Form điểm danh QR/link | MSSV, GPS, device | AttendanceUpdate, SecurityAlert |
| `/student/classes` | Lớp đã tham gia | Join class nếu có mã | Notification |
| `/student/attendance` | Lịch sử điểm danh | Gửi đơn nghỉ phép | Leave events |
| `/teacher/reports` | Biểu đồ/bảng báo cáo | Filter, export | Export queue |
| `/admin/dashboard` | Thống kê hệ thống | Filter dashboard | SystemAlert |
| `/admin/users` | Danh sách user | Khóa/mở user | Audit log |
| `/admin/subscriptions` | Gói, giao dịch | Cập nhật gói nếu có | Payment webhook |
| `/admin/logs` | Audit/security logs | Filter log | Queue/system alerts |

## 12. Quy tắc bảo mật khi truyền dữ liệu

| Nhóm bảo mật | Áp dụng |
|---|---|
| CSRF | Mọi form POST/PATCH/DELETE từ Blade/Livewire |
| Authentication | Dashboard admin, giảng viên, sinh viên |
| Authorization | Role admin/user, owner class, member class |
| Tenant isolation | Query phải lọc `tenant_id` nếu triển khai SaaS |
| Plan check | Tính năng Pro như export nâng cao, giới hạn lớp/sinh viên |
| GPS validation | Haversine distance, accuracy threshold |
| QR security | Token ngắn hạn, HMAC/signature, chống dùng lại |
| Duplicate check | Unique theo `class_session_id` + `class_member_id` |
| Device/IP logging | Ghi `check_in_scans` và phân tích gian lận |
| Upload security | Kiểm tra MIME thật, size, lưu ngoài public nếu cần |
| WebSocket auth | Chỉ join room khi đúng quyền |
| Audit log | Ghi lại tạo/sửa/xóa lớp, sửa điểm danh, thanh toán, khóa user |

## 13. Payload mẫu quan trọng

### 13.1. Tạo phiên QR/GPS

```json
{
  "class_id": 10,
  "name": "Buổi 3 - Điểm danh đầu giờ",
  "date": "2026-06-10",
  "start_time": "09:00",
  "end_time": "11:00",
  "method": "qr_gps",
  "ttl_minutes": 15,
  "gps_latitude": 10.762622,
  "gps_longitude": 106.660172,
  "gps_radius": 50
}
```

### 13.2. Sinh viên gửi điểm danh QR/GPS

```json
{
  "student_code": "SV001",
  "full_name": "Nguyễn Văn A",
  "qr_token": "signed-token",
  "latitude": 10.7627,
  "longitude": 106.6601,
  "accuracy": 22,
  "device_id": "fingerprint_hash",
  "user_agent": "Mozilla/5.0",
  "client_time": "2026-06-10T09:02:00+07:00"
}
```

### 13.3. Cập nhật điểm danh thủ công

```json
{
  "class_session_id": 15,
  "class_member_id": 101,
  "status": "late",
  "note": "Vào lớp sau 15 phút"
}
```

### 13.4. Import sinh viên

```text
POST multipart/form-data

file: students.xlsx
class_id: 10
mode: upsert
```

### 13.5. Duyệt đơn nghỉ phép

```json
{
  "leave_request_id": 30,
  "status": "approved",
  "review_note": "Đã xác nhận minh chứng"
}
```

### 13.6. Filter báo cáo

```json
{
  "class_id": 10,
  "semester": "2025-2026-1",
  "date_from": "2026-01-01",
  "date_to": "2026-06-30",
  "group_by": "student"
}
```

### 13.7. Event realtime điểm danh

```json
{
  "event": "AttendanceUpdate",
  "channel": "class_10",
  "data": {
    "student_code": "SV001",
    "full_name": "Nguyễn Văn A",
    "status": "present",
    "check_in_time": "2026-06-10T09:02:00+07:00",
    "method": "qr_gps"
  }
}
```

### 13.8. Webhook thanh toán

```json
{
  "orderCode": 100001,
  "amount": 99000,
  "description": "DD T159 PRO 1T",
  "status": "PAID",
  "signature": "hmac-signature"
}
```

## 14. Luồng dữ liệu end-to-end quan trọng nhất

### 14.1. Luồng điểm danh QR/GPS realtime đầy đủ

```text
1. Giảng viên tạo phiên QR/GPS
2. Backend lưu class_sessions
3. Backend trả QR/link xuống frontend
4. Sinh viên quét QR
5. Frontend lấy GPS + device fingerprint
6. Frontend gửi JSON check-in lên backend
7. Backend validate token + GPS + MSSV + duplicate
8. Backend ghi attendance_records
9. Backend ghi check_in_scans
10. Backend dispatch audit/fraud/statistic jobs
11. Backend fire AttendanceProcessed event
12. Redis Pub/Sub chuyển event cho Socket.IO
13. Socket.IO push AttendanceUpdate cho dashboard giảng viên
14. Frontend giảng viên cập nhật danh sách realtime
15. Frontend sinh viên hiển thị kết quả điểm danh
16. Fraud worker có thể gửi SecurityAlert sau đó
17. Khi giảng viên đóng phiên, backend khóa session và broadcast SessionControl
```

### 14.2. Luồng báo cáo đầy đủ

```text
1. Giảng viên chọn lớp/học kỳ/khoảng ngày
2. Frontend gửi filter bằng GET/Livewire
3. Backend query attendance_records
4. Backend aggregate số liệu
5. Backend trả bảng + dataset Chart.js
6. Frontend hiển thị biểu đồ
7. Nếu export: frontend gửi request export
8. Backend tạo file trực tiếp hoặc dispatch queue
9. Worker tạo file và lưu storage
10. Backend/worker broadcast ReportReady
11. Frontend hiển thị link tải
```

### 14.3. Luồng import danh sách sinh viên đầy đủ

```text
1. Giảng viên tải file Excel/CSV
2. Frontend gửi multipart/form-data
3. Backend kiểm tra owner lớp
4. Backend kiểm tra file thật và dung lượng
5. Backend đọc header
6. Backend validate từng dòng
7. Nếu lỗi: trả danh sách dòng lỗi
8. Nếu đúng: ghi class_members trong transaction
9. Với file lớn: xử lý theo chunk qua queue
10. Backend trả thống kê created/updated/skipped
11. Frontend hiển thị kết quả import
```

## 15. Gợi ý triển khai route theo tài liệu

> Đây là route mẫu để hiện thực các luồng trên, có thể điều chỉnh khi code thật.

```php
Route::get('/', HomeController::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
});

Route::get('/attendance/{qrToken}', [AttendanceController::class, 'showCheckInForm']);
Route::post('/attendance/{qrToken}', [AttendanceController::class, 'checkIn']);

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/notifications', [NotificationController::class, 'index']);
});

Route::middleware(['auth', 'role:user'])->prefix('teacher')->group(function () {
    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);
    Route::post('/classes/{class}/students/import', [ClassMemberController::class, 'import']);
    Route::post('/classes/{class}/sessions', [AttendanceSessionController::class, 'store']);
    Route::patch('/sessions/{session}/close', [AttendanceSessionController::class, 'close']);
    Route::get('/live/{session}', [AttendanceController::class, 'live']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::post('/reports/export', [ReportController::class, 'export']);
});

Route::middleware(['auth', 'role:user'])->prefix('student')->group(function () {
    Route::get('/classes', [StudentClassController::class, 'index']);
    Route::post('/classes/join', [StudentClassController::class, 'join']);
    Route::get('/attendance', [StudentAttendanceController::class, 'index']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);
    Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
    Route::get('/logs', [AdminLogController::class, 'index']);
    Route::get('/settings', [AdminSettingController::class, 'index']);
});

Route::post('/webhooks/payos', [PaymentWebhookController::class, 'payos']);
```

## 16. Gợi ý thứ tự ưu tiên khi code thật

1. Xây database migrations cho `users`, `classes`, `class_members`, `class_sessions`, `attendance_records`.
2. Làm authentication và phân quyền cơ bản.
3. Làm CRUD lớp học và import sinh viên.
4. Làm điểm danh thủ công.
5. Làm QR/GPS check-in.
6. Thêm realtime Socket.IO cho dashboard giảng viên.
7. Thêm queue cho log, fraud detection, tính thống kê.
8. Thêm báo cáo, biểu đồ, export Excel/PDF.
9. Thêm thanh toán Pro và giới hạn tính năng.
10. Hoàn thiện admin dashboard, audit log, notification.

## 17. Kết luận

Luồng dữ liệu của hệ thống điểm danh được thiết kế theo hướng:

- Backend Laravel chịu trách nhiệm validate, phân quyền, xử lý nghiệp vụ và lưu dữ liệu.
- Frontend Blade/Livewire/Alpine/Tailwind hiển thị dữ liệu và gửi thao tác người dùng.
- Realtime Socket.IO giúp giảng viên theo dõi điểm danh ngay khi sinh viên check-in.
- Redis Queue giúp các tác vụ nặng không làm chậm request chính.
- Audit log, GPS, QR token ngắn hạn, device fingerprint và RBAC giúp tăng tính an toàn cho hệ thống.

Tài liệu này có thể dùng làm nền để triển khai source code thật và làm phần mô tả luồng xử lý dữ liệu trong đồ án tốt nghiệp.
