# AGENTS.md — Hướng Dẫn Cho AI Assistant

> **AI đọc file này ĐẦU TIÊN** trước khi thực hiện bất kỳ tác vụ nào trong dự án.
> File này định nghĩa cách AI phải làm việc để nhất quán với nhóm.

---

## 1. THÔNG TIN DỰ ÁN

| Mục | Giá trị |
|-----|---------|
| Tên | Hệ Thống Quản Lý Điểm Danh Sinh Viên |
| Framework | Laravel 11, PHP 8.2+ |
| Frontend | Blade + Tailwind CSS + Vite |
| Database | MySQL 8.4 — `db_quan_ly_diem_danh` |
| User hiện tại | **Nguyễn Tuấn Khanh** — TV2 (Lớp học, Sinh viên, Import) |
| Nhánh của Khanh | `feature/khanh-class-student` |
| Laravel URL | http://127.0.0.1:8000 |
| DB Port | 3306 (Docker), user: root, pass: root |

---

## 2. TÀI LIỆU THAM CHIẾU

Trước khi code, AI phải đọc các file sau theo thứ tự:

1. **`claude.md`** — Toàn bộ ngữ cảnh dự án, schema DB, kiến trúc, phân công
2. **`HUONG_DAN_CODE.md`** — Quy ước code, checklist, cấu trúc file TV2
3. **`AGENTS.md`** — File này, quy tắc hành vi của AI

---

## 3. NGUYÊN TẮC HÀNH VI CỦA AI

### 3.1 Trước khi làm bất kỳ tác vụ nào:
- Đọc `claude.md` để nắm schema database và phân công
- Kiểm tra xem file/bảng đã tồn tại chưa (tránh tạo trùng)
- Hỏi nếu yêu cầu không rõ, đừng đoán mò

### 3.2 Thứ tự ưu tiên khi code:
Làm **đúng thứ tự** trong `claude.md` mục 10. Không bỏ bước, không làm ngược.

Thứ tự TV2 hiện tại:
```
✅ Bước 3:  Thêm lớp học
✅ Bước 4:  Xem danh sách lớp học
✅ Bước 5:  Xem chi tiết lớp học
✅ Bước 6:  Sửa lớp học
✅ Bước 7:  Xóa / lưu trữ lớp học
✅ Bước 8:  Thêm sinh viên vào lớp
✅ Bước 9:  Xem danh sách sinh viên
✅ Bước 10: Sửa thông tin sinh viên
✅ Bước 11: Xóa sinh viên khỏi lớp
✅ Bước 12: Import sinh viên từ Excel/CSV
```

### 3.3 Không được làm:
- Tạo migration khi bảng đã tồn tại
- Validate trong Controller (phải dùng Form Request)
- Dùng `Class` làm tên Model (từ khóa PHP) → dùng `ClassRoom`
- Commit file `.env` lên Git
- Code trực tiếp trên `main`, `master`, `develop`
- Cài thêm package mới khi chức năng đã có thể làm với package sẵn có

---

## 4. CÁC PACKAGE ĐÃ CÀI — DÙNG ĐÚNG

| Package | Dùng cho | Cách gọi |
|---------|----------|----------|
| `spatie/laravel-permission` | Phân quyền role | `$user->hasRole('giang_vien')` |
| `spatie/laravel-activitylog` | Ghi log hoạt động | `activity()->log(...)` |
| `maatwebsite/excel` | Import/Export Excel | `Excel::import(...)`, `Excel::download(...)` |
| `simplesoftwareio/simple-qrcode` | Tạo QR code | `QrCode::generate(...)` |
| `predis/predis` | Redis cache/queue | Cấu hình trong `.env` |
| `laravel/breeze` | Auth scaffolding | Đã có sẵn, không tạo lại |

---

## 5. CẤU TRÚC FILE TV2 (Khanh phụ trách)

```
app/Http/Controllers/
├── ClassController.php              # CRUD lớp học
├── ClassMemberController.php        # CRUD sinh viên
└── ImportStudentController.php      # Import Excel/CSV

app/Http/Requests/
├── StoreClassRequest.php
├── UpdateClassRequest.php
├── StoreClassMemberRequest.php
└── UpdateClassMemberRequest.php

app/Models/
├── ClassRoom.php                    # protected $table = 'classes'
├── ClassMember.php
└── ClassJoinRequest.php

database/migrations/
├── ..._create_classes_table.php
├── ..._create_class_members_table.php
└── ..._create_class_join_requests_table.php

resources/views/classes/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php

routes/web.php                       # Thêm route vào đây
```

---

## 6. SCHEMA DATABASE TV2

### Bảng `classes`
```php
Schema::create('classes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');                           // Tên lớp
    $table->string('code')->unique();                 // Mã môn học
    $table->string('class_code', 10)->unique();       // Mã lớp (sinh viên dùng)
    $table->string('semester')->nullable();           // Học kỳ
    $table->text('description')->nullable();
    $table->integer('total_sessions')->default(0);    // Tổng số buổi
    $table->integer('sessions_per_lesson')->default(1); // Số tiết/buổi
    $table->integer('gps_radius')->default(100);      // Bán kính GPS (mét)
    $table->integer('absence_threshold')->default(20); // Ngưỡng cảnh báo vắng (%)
    $table->boolean('require_join_approval')->default(false);
    $table->enum('status', ['active', 'archived'])->default('active');
    $table->timestamps();
    $table->softDeletes();
});
```

### Bảng `class_members`
```php
Schema::create('class_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
    $table->string('student_code', 50);              // MSSV
    $table->string('name');                          // Họ tên
    $table->string('email')->nullable();
    $table->enum('status', ['active', 'dropped'])->default('active');
    $table->foreignId('user_id')->nullable()->constrained(); // Tài khoản nếu có
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['class_id', 'student_code']);    // MSSV không trùng trong lớp
});
```

### Bảng `class_join_requests`
```php
Schema::create('class_join_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
    $table->timestamps();
});
```

---

## 7. QUY ƯỚC ROUTE TV2

```php
// routes/web.php — Nhóm route của TV2
Route::middleware(['auth', 'role:giang_vien|admin'])->group(function () {

    // Lớp học
    Route::resource('classes', ClassController::class);
    Route::patch('classes/{class}/archive', [ClassController::class, 'archive'])->name('classes.archive');
    Route::patch('classes/{class}/regenerate-code', [ClassController::class, 'regenerateCode'])->name('classes.regenerate-code');

    // Sinh viên trong lớp
    Route::resource('classes.members', ClassMemberController::class);

    // Import
    Route::get('classes/{class}/import', [ImportStudentController::class, 'form'])->name('classes.import.form');
    Route::post('classes/{class}/import', [ImportStudentController::class, 'store'])->name('classes.import.store');
    Route::get('import/template', [ImportStudentController::class, 'downloadTemplate'])->name('import.template');
});
```

---

## 8. QUY ƯỚC MODEL

```php
// Luôn dùng SoftDeletes cho ClassRoom, ClassMember
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassRoom extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';   // BẮT BUỘC vì 'class' là từ khóa PHP

    protected $fillable = [
        'user_id', 'name', 'code', 'class_code',
        'semester', 'description', 'total_sessions',
        'sessions_per_lesson', 'gps_radius',
        'absence_threshold', 'require_join_approval', 'status',
    ];
}
```

---

## 9. QUY TRÌNH THỰC HIỆN MỘT CHỨC NĂNG

Khi AI được yêu cầu làm một chức năng mới, thực hiện theo thứ tự:

```
1. Migration      → tạo/kiểm tra bảng DB
2. Model          → tạo Eloquent model + relationships
3. Form Request   → validation rules
4. Controller     → logic xử lý
5. Route          → đăng ký URL
6. View (Blade)   → giao diện người dùng
7. Test thủ công  → kiểm tra route:list, migrate status
```

---

## 10. GIT WORKFLOW

```powershell
# Luôn ở nhánh feature trước khi code
git checkout feature/khanh-class-student

# Lấy code mới nhất
git pull origin develop

# Sau khi xong 1 chức năng
git add .
git commit -m "feat: thêm CRUD lớp học"
git push origin feature/khanh-class-student
```

**Format commit message:**
```
feat:    thêm tính năng mới
fix:     sửa bug
chore:   migration, config
refactor: tái cấu trúc
docs:    cập nhật tài liệu
```

---

## 11. CÁCH AI XỬ LÝ KHI BỊ TỪ CHỐI LỆNH

Nếu user từ chối `run_command`, AI phải:
1. **Giải thích rõ** lệnh đó làm gì và tại sao cần
2. **Ghi code vào file** thay thế cho chạy lệnh artisan (nếu được)
3. **Hướng dẫn user tự chạy** với snippet copy-paste rõ ràng
4. **Tiếp tục phần khác** không bị block, quay lại sau

---

## 12. THÔNG TIN TRUY CẬP MÔI TRƯỜNG

| Dịch vụ | URL / Thông tin |
|---------|----------------|
| Web Laravel | http://127.0.0.1:8000 |
| phpMyAdmin | http://127.0.0.1:8080 |
| MySQL | Host: 127.0.0.1, Port: 3306, User: root, Pass: root |
| Redis | Host: 127.0.0.1, Port: 6379 |
| DB Name | `db_quan_ly_diem_danh` |

**Đăng nhập phpMyAdmin:**
- Server: `mysql`
- Username: `root`
- Password: `root`
- Chọn database: `db_quan_ly_diem_danh`

---

## 13. PHÂN CÔNG CHI TIẾT 4 THÀNH VIÊN

### TV1 — Xác thực, phân quyền, Admin, hồ sơ cá nhân

| Nhóm chức năng | Việc cần code | Giao diện |
|---------------|--------------|-----------|
| Đăng ký | Form đăng ký, kiểm tra email trùng, mã hóa mật khẩu, gán role mặc định | Trang đăng ký |
| Đăng nhập | Kiểm tra email/mật khẩu, kiểm tra bị khóa, chuyển hướng theo vai trò | Trang đăng nhập |
| Đăng xuất | Xóa session, về trang login | Nút đăng xuất |
| Quên mật khẩu | Form nhập email, gửi link reset | Trang quên mật khẩu |
| Đổi mật khẩu | Kiểm tra mật khẩu cũ, nhập mới, xác nhận | Trang đổi mật khẩu |
| Hồ sơ cá nhân | Xem họ tên, email, avatar, trạng thái | Trang profile |
| Sửa hồ sơ | Sửa họ tên, avatar, số điện thoại | Form sửa profile |
| Phân quyền | Middleware Admin / Giảng viên / Học viên | Không cần màn riêng |
| Admin - danh sách user | Hiển thị, tìm kiếm, lọc active/blocked | Trang quản lý user |
| Admin - chi tiết user | Xem thông tin, số lớp tạo, trạng thái, gói | Trang chi tiết user |
| Admin - khóa/mở user | Đổi trạng thái blocked/active | Nút khóa/mở |
| Admin - sửa user | Sửa tên, email, trạng thái | Form sửa user |
| Admin - quản lý gói | Xem Free/Pro, đổi gói thủ công | Trang gói dịch vụ |
| Admin - dashboard | Đếm tổng user, lớp, buổi điểm danh | Trang dashboard admin |

---

### TV2 — Lớp học, sinh viên, import danh sách (Nguyễn Tuấn Khanh)

| Nhóm chức năng | Việc cần code | Giao diện |
|---------------|--------------|-----------|
| Xem danh sách lớp | Tìm kiếm theo tên/mã, lọc active/archived | Trang danh sách lớp |
| Thêm lớp học | Form: tên, mã môn, học kỳ, mô tả, tổng buổi, số tiết/buổi | Trang tạo lớp |
| Sửa lớp học | Cập nhật tất cả thông tin lớp | Trang sửa lớp |
| Xóa lớp học | Soft delete hoặc chuyển archived | Nút xóa/lưu trữ |
| Xem chi tiết lớp | Thông tin, mã lớp, tổng sinh viên, số buổi | Trang chi tiết lớp |
| Cấu hình lớp | Bán kính GPS, ngưỡng vắng, yêu cầu duyệt | Trang cấu hình lớp |
| Tạo mã lớp | Sinh class_code để sinh viên tham gia | Hiển thị mã lớp |
| Đổi mã lớp | Tạo lại mã mới nếu bị lộ | Nút đổi mã |
| Xem danh sách sinh viên | MSSV, họ tên, trạng thái, email, tìm kiếm | Bảng sinh viên |
| Thêm sinh viên | Form: MSSV, họ tên, email, trạng thái | Form thêm sinh viên |
| Sửa sinh viên | Sửa họ tên, MSSV, email, trạng thái | Form sửa sinh viên |
| Xóa sinh viên | Soft delete hoặc đổi trạng thái dropped | Nút xóa |
| Xem chi tiết sinh viên | Thông tin + lịch sử điểm danh cơ bản | Trang chi tiết sinh viên |
| Import Excel/CSV | Upload file, validate, báo lỗi từng dòng, thêm mới | Trang import |
| Cập nhật khi trùng MSSV | Nếu MSSV tồn tại thì update, không tạo trùng | Kết quả import |
| Tải file mẫu | Cho tải Excel/CSV mẫu | Nút tải mẫu |
| Tham gia lớp bằng mã | User nhập mã lớp để tham gia | Trang nhập mã |

---

### TV3 — Điểm danh thủ công, QR/link, GPS, chống gian lận

| Nhóm chức năng | Việc cần code | Giao diện |
|---------------|--------------|-----------|
| Xem danh sách buổi | Ngày học, trạng thái pending/active/closed | Trang danh sách buổi |
| Thêm buổi | Tên buổi, ngày, giờ, hình thức điểm danh | Form tạo buổi |
| Sửa buổi | Sửa khi chưa chốt | Form sửa buổi |
| Xóa buổi | Xóa mềm nếu chưa chốt | Nút xóa buổi |
| Mở/chốt sổ buổi | Đổi trạng thái active/closed | Nút mở/chốt sổ |
| Điểm danh thủ công | Chọn Có mặt/Vắng/Muộn/Vắng phép từng sinh viên | Bảng điểm danh |
| Sửa kết quả | Đổi trạng thái sinh viên | Dropdown trạng thái |
| Ghi chú điểm danh | Lý do vắng, muộn | Ô ghi chú |
| Sinh QR/link | Token + thời gian hết hạn + URL checkin | Màn hình QR giảng viên |
| Làm mới QR | Tạo token mới | Nút làm mới QR |
| Sinh viên quét QR | Trang checkin mobile | Trang mobile check-in |
| Sinh viên nhập MSSV | Form điểm danh không cần tài khoản | Form điểm danh khách |
| Kiểm tra MSSV | Chỉ cho điểm danh nếu có trong class_members | Backend |
| Chống trùng | Nếu đã điểm danh thì báo lỗi | Thông báo |
| Lấy GPS | HTML5 Geolocation | Giao diện xin quyền vị trí |
| Kiểm tra GPS | Tính khoảng cách, chặn ngoài bán kính | Thông báo ngoài phạm vi |
| Lưu IP/thiết bị | IP, user agent, device fingerprint | Backend |
| Ghi log quét | check_in_scans: thành công/thất bại | Backend |
| Trang thành công | Môn/lớp, thời gian, trạng thái có mặt | Trang success |
| Trang thất bại | Lý do: hết hạn QR, sai MSSV, ngoài GPS, đã điểm danh | Trang fail |

---

### TV4 — Chuyên cần, cảnh báo, đơn nghỉ, thống kê, báo cáo

| Nhóm chức năng | Việc cần code | Giao diện |
|---------------|--------------|-----------|
| Chuyên cần toàn lớp | Tổng có mặt, vắng, muộn, tỷ lệ từng sinh viên | Trang chuyên cần lớp |
| Chuyên cần cá nhân | Sinh viên xem lịch sử và tỷ lệ của mình | Trang chuyên cần cá nhân |
| Tính tỷ lệ vắng/chuyên cần | Từ attendance_records hoặc summaries | Backend |
| Cảnh báo chuyên cần | Vượt 10%, 20% hoặc ngưỡng tùy chỉnh | Badge cảnh báo |
| Lọc theo cảnh báo | Bình thường / nhẹ / đỏ | Bộ lọc |
| Top sinh viên vắng nhiều | Sắp xếp giảm dần | Bảng top vắng |
| Sinh viên gửi đơn nghỉ | Chọn buổi, lý do, upload minh chứng | Form đơn nghỉ |
| Xem đơn đã gửi | pending/approved/rejected | Trang đơn nghỉ cá nhân |
| Giảng viên xem đơn | Lọc theo lớp, trạng thái, ngày | Trang quản lý đơn |
| Duyệt/từ chối đơn | Cập nhật điểm danh thành vắng có phép | Nút duyệt/từ chối |
| Biểu đồ Chart.js | Tỷ lệ có mặt/vắng/muộn theo buổi | Biểu đồ lớp |
| Thống kê tổng quan | Tổng sinh viên, buổi, tỷ lệ trung bình, số cảnh báo | Dashboard lớp |
| Xuất Excel điểm danh | Theo lớp/buổi/khoảng thời gian | Nút xuất Excel |
| Xuất Excel chuyên cần | Tổng hợp cuối kỳ | Nút xuất chuyên cần |
| Realtime (nếu kịp) | Dashboard tự cập nhật khi sinh viên điểm danh | Socket.io/Livewire |

---

## 14. KẾ HOẠCH THEO NGÀY

| Ngày | TV1 | TV2 (Khanh) | TV3 | TV4 |
|------|-----|-------------|-----|-----|
| 13/06 | Kiểm tra bảng users, roles, permissions. Tạo route auth/admin | **Kiểm tra bảng classes, class_members, class_join_requests. Tạo route lớp học và sinh viên** | Kiểm tra bảng sessions, attendance_records. Tạo route điểm danh | Kiểm tra bảng summaries, leave_requests. Tạo route thống kê |
| 14/06 | Tạo AuthController, Admin/UserController, ProfileController, middleware | **Tạo ClassController, ClassMemberController, ImportStudentController** | Tạo AttendanceSessionController, QRCheckinController, LocationService | Tạo SummaryController, ReportController, LeaveRequestController |
| 15/06 | Đăng ký, đăng nhập, đăng xuất, giao diện login/register | **Thêm lớp học, xem danh sách lớp, xem chi tiết lớp** | Thêm buổi điểm danh, xem danh sách buổi | Dashboard khung: tổng sinh viên, buổi, có mặt/vắng |
| 16/06 | Profile, sửa profile, đổi mật khẩu | **Sửa lớp, xóa/lưu trữ lớp, cấu hình tổng buổi và số tiết** | Sửa buổi, xóa buổi chưa chốt | Tính tỷ lệ chuyên cần cơ bản |
| 17/06 | Admin xem/tìm kiếm/lọc user | **Thêm sinh viên, xem danh sách, sửa, xóa sinh viên** | Điểm danh thủ công: chọn trạng thái từng sinh viên | Bảng chuyên cần toàn lớp |
| 18/06 | Admin xem chi tiết user, khóa/mở | **Import Excel/CSV, validate lỗi, cập nhật trùng MSSV** | Lưu điểm danh vào DB, sửa kết quả, ghi chú | Cảnh báo vắng |
| 19/06 | Hoàn thiện phân quyền route | **Mã lớp, đổi mã lớp, tham gia lớp bằng mã** | Sinh QR/link, token hết hạn, hiển thị QR | Lịch sử điểm danh cá nhân học viên |
| 20/06 | Test khóa user | **Kiểm tra MSSV trong class_members cho TV3** | QR hoạt động, sinh viên nhập MSSV, chống trùng | Cập nhật chuyên cần sau chốt buổi |
| 21/06 | Audit log cơ bản | **Cấu hình bán kính GPS cho lớp/buổi** | GPS sinh viên + tính khoảng cách + chặn ngoài bán kính | Hiển thị lý do cảnh báo |
| 22/06 | Admin dashboard: tổng user, lớp, buổi | **Sửa lỗi import, tìm kiếm/lọc sinh viên** | Lưu IP/device/fingerprint. Log check_in_scans | Form đơn xin nghỉ |
| 23/06 | Admin gói Free/Pro | **Tab chi tiết lớp: sinh viên, buổi học, cấu hình** | Chốt sổ: sau chốt không cho điểm danh tiếp | Giảng viên duyệt/từ chối đơn nghỉ |
| 24/06 | Test phân quyền toàn hệ thống | **Hỗ trợ TV4 lấy data lớp/sinh viên đúng class_id** | Hỗ trợ TV4 lấy attendance_records đúng trạng thái | Biểu đồ Chart.js: có mặt/vắng/muộn theo buổi |
| 25/06 | Phân quyền xuất báo cáo | **Bộ lọc lớp, học kỳ, môn học** | Dữ liệu đã chốt mới đưa vào báo cáo | Xuất Excel điểm danh và chuyên cần |
| 26/06 | **Merge lần 1 vào develop** — sửa conflict toàn nhóm | | | |
| 27/06 | Test admin: đăng nhập, user, khóa/mở | **Test: thêm lớp, sửa lớp, xóa lớp, import, sửa/xóa sinh viên** | Test: thủ công, QR, GPS, chốt sổ, chống trùng | Test: chuyên cần, cảnh báo, đơn nghỉ, biểu đồ, Excel |
| 28/06 | Responsive login/register/admin | **Responsive danh sách lớp, chi tiết lớp, sinh viên, import** | Responsive QR giảng viên và màn hình mobile sinh viên | Responsive dashboard, biểu đồ, báo cáo |
| 29/06 | Tạo tài khoản demo | **Tạo lớp demo, import sinh viên demo** | Tạo buổi/QR demo, dữ liệu điểm danh | Dữ liệu chuyên cần, cảnh báo, file Excel demo |
| 30/06 | Kiểm tra tài khoản, phân quyền, admin dashboard lần cuối | **Kiểm tra lớp học, sinh viên, import lần cuối** | Kiểm tra điểm danh, QR, GPS, chốt sổ lần cuối | Kiểm tra chuyên cần, đơn nghỉ, báo cáo lần cuối |

> **Mốc quan trọng:**
> - 16/06: Đăng nhập được → Tạo lớp được → Thêm buổi được
> - 20/06: QR/link hoạt động, sinh viên nhập MSSV điểm danh được
> - 26/06: Merge code toàn nhóm, chạy được luồng đầy đủ
> - 30/06: Chốt bản demo

---

## 15. THỨ TỰ CHỨC NĂNG PHẢI LÀM (BẮT BUỘC GIỮ NGUYÊN)

```
1.  Đăng ký, đăng nhập, đăng xuất
2.  Phân quyền Admin / Giảng viên / Học viên
3.  Thêm lớp học
4.  Xem danh sách lớp học
5.  Xem chi tiết lớp học
6.  Sửa lớp học
7.  Xóa / lưu trữ lớp học
8.  Thêm sinh viên vào lớp
9.  Xem danh sách sinh viên
10. Sửa thông tin sinh viên
11. Xóa sinh viên khỏi lớp
12. Import sinh viên từ Excel/CSV
13. Tạo buổi điểm danh
14. Xem danh sách buổi điểm danh
15. Sửa buổi điểm danh
16. Xóa buổi nếu chưa chốt
17. Điểm danh thủ công
18. Sửa kết quả điểm danh
19. Chốt sổ điểm danh
20. Sinh QR / link
21. Sinh viên quét QR
22. Sinh viên nhập MSSV / họ tên
23. Kiểm tra MSSV thuộc lớp
24. Kiểm tra token QR còn hạn
25. Chống điểm danh trùng
26. Lấy GPS
27. Kiểm tra khoảng cách GPS
28. Lưu IP / thiết bị / log
29. Tính chuyên cần
30. Cảnh báo vắng
31. Đơn xin nghỉ
32. Duyệt / từ chối đơn nghỉ
33. Thống kê biểu đồ
34. Xuất Excel
35. Admin quản lý user
36. Test, sửa lỗi, deploy demo
```

---

## 16. CHỨC NĂNG BẮT BUỘC VÀ ĐỂ SAU

### ✅ Bắt buộc xong trước 30/06

| Chức năng |
|-----------|
| Đăng nhập / đăng ký / đăng xuất |
| Phân quyền 3 vai trò |
| CRUD lớp học |
| CRUD sinh viên trong lớp |
| Import Excel/CSV |
| CRUD buổi điểm danh |
| Điểm danh thủ công |
| QR / Link điểm danh |
| Kiểm tra MSSV, token QR, chống trùng |
| GPS cơ bản |
| Chốt sổ điểm danh |
| Tính chuyên cần + cảnh báo vắng |
| Thống kê lớp |
| Xuất Excel |
| Admin: xem / khóa / mở user |

### ⏸ Để sau nếu không kịp

| Chức năng | Lý do |
|-----------|-------|
| Thanh toán PayOS thật | Không ảnh hưởng demo |
| Email hàng loạt | Trình bày trong tài liệu |
| Realtime Socket.io đầy đủ | Dùng reload/Livewire đơn giản trước |
| Queue nâng cao | Làm sau khi core ổn |
| PWA / push notification | Không cần bản demo cuối tháng 6 |
| Xuất PDF | Nếu kịp thì làm, không thì bỏ |

---

*Cập nhật: 14/06/2026 | Dự án điểm danh sinh viên — TV2 Nguyễn Tuấn Khanh*
