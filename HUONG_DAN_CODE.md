# 📋 HƯỚNG DẪN CODE — HỆ THỐNG ĐIỂM DANH SINH VIÊN

> **Đọc file này TRƯỚC KHI bắt đầu code mỗi ngày.**
> Thực hiện đúng thứ tự để đảm bảo nhất quán với cả nhóm.

---

## 🚀 BƯỚC 1 — KHỞI ĐỘNG MÔI TRƯỜNG

### 1.1 Khởi động Docker (MySQL + Redis)
```powershell
# Mở Docker Desktop trước, sau đó chạy:
docker compose up -d

# Kiểm tra đang chạy chưa:
docker compose ps
```
✅ Phải thấy `attendance_mysql` và `attendance_redis` đều **Up**.

### 1.2 Khởi động Laravel
```powershell
php artisan serve
```
✅ Web chạy tại: **http://127.0.0.1:8000**

### 1.3 Khởi động Vite (frontend assets)
```powershell
# Mở terminal mới, chạy song song với Laravel:
npm run dev
```

> **Tắt tất cả:** chạy `stop-dev.bat` hoặc `Ctrl+C` từng terminal.

---

## 🌿 BƯỚC 2 — KIỂM TRA NHÁNH GIT

```powershell
# Xem đang ở nhánh nào
git branch

# Xem thay đổi chưa commit
git status
```

### Nhánh đúng theo từng người:

| Thành viên | Nhánh làm việc |
|------------|----------------|
| Nguyễn Tuấn Khanh (TV2) | `feature/khanh-class-student` |
| TV1 | `feature/tv1-auth-admin` |
| TV3 | `feature/tv3-attendance` |
| TV4 | `feature/tv4-report-summary` |

> ⚠️ **KHÔNG code trực tiếp trên `main`, `master`, hoặc `develop`!**

### Chuyển sang nhánh của mình:
```powershell
git checkout feature/khanh-class-student

# Lấy code mới nhất từ develop về:
git pull origin develop
```

---

## 🗄️ BƯỚC 3 — KIỂM TRA DATABASE

### Kết nối MySQL:
| Thông số | Giá trị |
|----------|---------|
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `db_quan_ly_diem_danh` |
| Username | `root` |
| Password | `root` |

### Xem trạng thái migrations:
```powershell
php artisan migrate:status
```

### Chạy migration mới (nếu có):
```powershell
php artisan migrate
```

### Reset DB (⚠️ chỉ dùng khi phát triển, XÓA TOÀN BỘ DỮ LIỆU):
```powershell
php artisan migrate:fresh --seed
```

---

## ⚙️ CẤU HÌNH `.env` — PHẢI CÓ

File `.env` phải có đúng các giá trị sau (copy từ `.env.example` và sửa):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_quan_ly_diem_danh
DB_USERNAME=root
DB_PASSWORD=root

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

> ℹ️ File `.env` **không được commit** lên Git (đã có trong `.gitignore`).

---

## 📁 CẤU TRÚC CODE — TV2 (Khánh)

Khánh phụ trách các file sau:

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ClassController.php          ← CRUD lớp học
│   │   ├── ClassMemberController.php    ← CRUD sinh viên
│   │   └── ImportStudentController.php  ← Import Excel/CSV
│   └── Requests/
│       ├── StoreClassRequest.php
│       ├── UpdateClassRequest.php
│       ├── StoreClassMemberRequest.php
│       └── UpdateClassMemberRequest.php
├── Models/
│   ├── ClassRoom.php                    ← Model lớp học
│   ├── ClassMember.php                  ← Model sinh viên
│   └── ClassJoinRequest.php             ← Model đơn xin vào lớp
database/
└── migrations/
    ├── ..._create_classes_table.php
    ├── ..._create_class_members_table.php
    └── ..._create_class_join_requests_table.php
resources/views/
└── classes/
    ├── index.blade.php     ← Danh sách lớp
    ├── create.blade.php    ← Tạo lớp
    ├── edit.blade.php      ← Sửa lớp
    └── show.blade.php      ← Chi tiết lớp (tab: sinh viên, buổi học, cấu hình)
routes/
└── web.php                 ← Route lớp học và sinh viên
```

---

## 📝 QUY ƯỚC CODE

### Đặt tên:
| Loại | Quy ước | Ví dụ |
|------|---------|-------|
| Model | PascalCase, **số ít** | `ClassMember` |
| Bảng DB | snake_case, **số nhiều** | `class_members` |
| Controller | PascalCase + Controller | `ClassController` |
| Route | snake_case dấu chấm | `classes.index` |
| View | kebab-case | `classes/show.blade.php` |

### Model phải có:
```php
// Lớp học, sinh viên → bắt buộc có SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassRoom extends Model
{
    use SoftDeletes;

    protected $table = 'classes'; // vì 'class' là từ khóa PHP

    protected $fillable = [...];
}
```

### Validate bằng Form Request (KHÔNG validate trong Controller):
```php
// Sai ❌
public function store(Request $request) {
    $request->validate([...]);
}

// Đúng ✅
public function store(StoreClassRequest $request) {
    // $request đã được validate tự động
}
```

---

## 💾 QUY TRÌNH COMMIT MỖI NGÀY

```powershell
# 1. Xem thay đổi
git status

# 2. Thêm file vào staging
git add .

# 3. Commit với message đúng format
git commit -m "feat: thêm CRUD lớp học"

# 4. Push lên nhánh của mình
git push origin feature/khanh-class-student
```

### Format commit message:
```
feat: thêm tính năng mới
fix: sửa bug
chore: cấu hình, migration (không ảnh hưởng logic)
refactor: tái cấu trúc code
docs: cập nhật tài liệu
```

---

## ✅ CHECKLIST TRƯỚC KHI CODE MỖI NGÀY

```
□ Docker đang chạy (docker compose ps → 2 container Up)
□ Đang ở đúng nhánh feature của mình (git branch)
□ Đã pull code mới nhất (git pull origin develop)
□ File .env có DB_CONNECTION=mysql và đúng thông tin
□ php artisan serve đang chạy tại :8000
□ npm run dev đang chạy (nếu sửa frontend)
```

---

## ✅ CHECKLIST TRƯỚC KHI PUSH CODE

```
□ Code chạy không có lỗi PHP
□ Các route đã test thủ công qua trình duyệt
□ Migration đã chạy thành công (php artisan migrate)
□ Không có dd(), var_dump(), print_r() còn sót lại
□ Commit message đúng format
□ Chỉ commit file của mình, không commit .env
```

---

## 🔧 LỆNH HAY DÙNG

```powershell
# Tạo file mới
php artisan make:model ClassRoom -m          # Model + migration
php artisan make:controller ClassController --resource
php artisan make:request StoreClassRequest

# Xóa cache khi có lỗi lạ
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Xem tất cả route
php artisan route:list

# Xem tất cả migration
php artisan migrate:status

# Spatie permission
php artisan permission:cache-reset
```

---

## 📞 KHI GẶP LỖI

1. **Lỗi DB connection** → Kiểm tra Docker đang chạy + `.env` đúng chưa
2. **Lỗi "Class not found"** → Chạy `composer dump-autoload`
3. **Lỗi migration** → Chạy `php artisan config:clear` rồi thử lại
4. **Lỗi permission** → Chạy `php artisan permission:cache-reset`
5. **Lỗi Vite assets** → Đảm bảo `npm run dev` đang chạy

---

*Cập nhật: 14/06/2026 | TV2 — Nguyễn Tuấn Khánh*
