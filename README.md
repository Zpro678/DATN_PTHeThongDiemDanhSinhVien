# Hệ thống quản lý điểm danh

## 1. Giới thiệu dự án

Đây là dự án web được xây dựng trên Laravel 11. Tên package trong `composer.json` là `laravel/laravel`, `APP_NAME` trong `.env.example` đang là `Laravel`, trong khi tên thư mục, tên database Docker `db_quan_ly_diem_danh` và giao diện layout sử dụng ngữ cảnh `SAMS`/quản lý điểm danh.

Mục tiêu có thể xác định từ source code hiện tại: xây dựng nền tảng quản lý điểm danh với các giao diện chung cho Admin, Giảng viên và Sinh viên. Source hiện đã có hạ tầng Laravel, xác thực người dùng, quản lý hồ sơ, layout theo vai trò, Docker cho MySQL/Redis, queue/database table, RBAC bằng Spatie Permission và Socket.IO bridge qua Redis. Các nghiệp vụ điểm danh chuyên sâu chưa tìm thấy controller/model/migration triển khai trong source code.

README hiện tại được tạo lại từ việc phân tích trực tiếp các file: `composer.json`, `package.json`, `docker-compose.yml`, `.env.example`, `routes/*`, `app/*`, `resources/*`, `database/*`, `config/*`, `bootstrap/*`, `public/*`, `server.cjs`, script `.bat` và README Laravel mặc định trước đó.

## 2. Công nghệ sử dụng

### Backend

- PHP: yêu cầu `^8.2` theo `composer.json`; môi trường đã kiểm tra chạy PHP `8.2.31`.
- Laravel Framework: yêu cầu `^11.31`; `php artisan --version` trả về `Laravel Framework 11.54.0`.
- Composer packages chính:
  - `laravel/framework`: framework backend.
  - `laravel/sanctum`: CSRF/API token infrastructure; route `sanctum/csrf-cookie` đang xuất hiện trong route list.
  - `laravel/tinker`: tương tác console.
  - `maatwebsite/excel`: xử lý import/export Excel; chưa tìm thấy class import/export có nội dung trong source.
  - `predis/predis`: Redis client cho PHP.
  - `simplesoftwareio/simple-qrcode`: QR Code package; chưa tìm thấy controller/service sử dụng trong source.
  - `spatie/laravel-activitylog`: activity log package; chưa tìm thấy model/controller sử dụng trong source.
  - `spatie/laravel-permission`: RBAC role/permission.
- Composer dev packages:
  - `laravel/breeze`: auth scaffolding.
  - `laravel/pail`: log viewer CLI.
  - `laravel/pint`: code style formatter.
  - `laravel/sail`: Docker development toolkit; chưa có Sail compose app-level trong source.
  - `phpunit/phpunit`: test runner.

### Frontend

- Blade template engine.
- Tailwind CSS.
- Alpine.js.
- Vite + Laravel Vite Plugin.
- Axios.
- `@tailwindcss/forms`.
- Layout hiện có:
  - Auth layout Breeze.
  - App layout tự chọn biến thể `admin`, `lecturer`, `student`.
  - Preview layout để xem nhanh giao diện từng vai trò.

### JavaScript Libraries

- `alpinejs`: điều khiển menu, dropdown, dark mode trong Blade layout.
- `axios`: cấu hình header `X-Requested-With`.
- `socket.io-client` và `laravel-echo`: đã cài trong `package.json`, nhưng chưa tìm thấy cấu hình Echo trong `resources/js`.
- `express`, `socket.io`, `ioredis`: dùng trong `server.cjs`.

### Realtime

- Redis có trong `docker-compose.yml`, `config/database.php`, `.env.example`, `package.json` và `server.cjs`.
- Socket.IO server có trong `server.cjs`, lắng nghe cổng `3000`.
- Broadcasting Laravel:
  - `.env.example` có `BROADCAST_CONNECTION=log`.
  - Không tìm thấy file `config/broadcasting.php`.
  - Không tìm thấy class event implements `ShouldBroadcast`.
  - Không tìm thấy `routes/channels.php`.
  - Không tìm thấy cấu hình Laravel Echo trong `resources/js`.

### Database

- `.env.example` mặc định dùng SQLite: `DB_CONNECTION=sqlite`.
- `docker-compose.yml` cung cấp MySQL 8.4 với database `db_quan_ly_diem_danh`.
- Migration hiện có tạo bảng user/auth/session/cache/queue/RBAC.

### Infrastructure

- Docker Compose root-level có 2 service:
  - `mysql`: image `mysql:8.4`, container `attendance_mysql`, port `3306`.
  - `redis`: image `redis:7-alpine`, container `attendance_redis`, port `6379`.
- Không tìm thấy Dockerfile cấp dự án. Dockerfile chỉ tồn tại trong `vendor/laravel/sail/runtimes/*`.
- Có script Windows:
  - `start-dev.bat`: chạy Docker, Laravel server, queue worker Redis, Vite và Socket.IO server.
  - `stop-dev.bat`: dừng Docker và kill process `node.exe`, `php.exe`.

## 3. Kiến trúc hệ thống

Kiến trúc thực tế đang thể hiện trong source:

```text
Browser / Client
    |
    | HTTP
    v
Laravel 11 Web Application
    |
    | Blade + Tailwind CSS + Alpine.js
    v
Rendered Web UI

Laravel 11
    |
    | Eloquent / DB facade
    v
SQLite hoặc MySQL

Laravel 11
    |
    | Queue config
    v
Database Queue hoặc Redis Queue

Redis Pub/Sub
    |
    | server.cjs: psubscribe('*')
    v
Socket.IO Server :3000
    |
    | io.emit(channel, JSON.parse(message))
    v
Realtime Clients
```

Ghi chú quan trọng:

- Luồng Laravel Broadcasting đầy đủ chưa được triển khai trong source.
- `server.cjs` hoạt động như Redis-to-Socket.IO bridge độc lập.
- Queue infrastructure có sẵn, nhưng không tìm thấy class job nghiệp vụ trong `app/Jobs`.

## 4. Cấu trúc thư mục

```text
quan-ly-diem-danh/
├── app/
│   ├── Console/
│   ├── Entities/
│   ├── Enums/
│   ├── Events/
│   ├── Exceptions/
│   ├── Exports/
│   ├── Helpers/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   └── Requests/
│   │       └── Auth/
│   ├── Imports/
│   ├── Jobs/
│   ├── Listeners/
│   ├── Middleware/
│   ├── Models/
│   ├── Providers/
│   ├── Requests/
│   ├── Services/
│   └── View/
│       └── Components/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
├── routes/
├── storage/
├── tests/
├── docker-compose.yml
├── server.cjs
├── start-dev.bat
└── stop-dev.bat
```

### `app/`

- `app/Http/Controllers/Auth/`: controller xác thực do Laravel Breeze sinh ra: đăng ký, đăng nhập, đăng xuất, quên mật khẩu, đặt lại mật khẩu, xác thực email, xác nhận mật khẩu.
- `app/Http/Controllers/ProfileController.php`: xem, cập nhật và xóa hồ sơ người dùng.
- `app/Http/Requests/Auth/LoginRequest.php`: validate login và giới hạn số lần đăng nhập sai.
- `app/Http/Requests/ProfileUpdateRequest.php`: validate cập nhật tên/email.
- `app/Models/User.php`: model người dùng, có `HasFactory`, `Notifiable`, `HasRoles`.
- `app/Providers/AppServiceProvider.php`: provider mặc định, chưa có logic custom.
- `app/View/Components/AppLayout.php`: Blade component layout chính, nhận `variant` và `pageTitle`.
- `app/View/Components/GuestLayout.php`: Blade component cho trang guest/auth.
- Các thư mục `Console`, `Entities`, `Enums`, `Events`, `Exceptions`, `Exports`, `Helpers`, `Imports`, `Jobs`, `Listeners`, `Middleware`, `Requests`, `Services` đang tồn tại nhưng không có file triển khai trong source hiện tại.

### `database/`

- `migrations/`: migration tạo bảng users, password reset, sessions, cache, queue, failed jobs và Spatie permissions.
- `factories/UserFactory.php`: factory tạo user test với mật khẩu mặc định `password`.
- `seeders/DatabaseSeeder.php`: tạo một user test `test@example.com`.
- `database.sqlite`: file SQLite có trong source.

### `resources/`

- `resources/css/app.css`: Tailwind entrypoint, custom scrollbar, `x-cloak`, base body style.
- `resources/js/app.js`: import bootstrap và khởi động Alpine.js.
- `resources/js/bootstrap.js`: cấu hình Axios.
- `resources/views/auth/`: giao diện auth Breeze.
- `resources/views/profile/`: giao diện profile.
- `resources/views/layouts/app.blade.php`: layout chính, tự chọn shell theo `variant`, route hoặc role.
- `resources/views/layouts/partials/admin-shell.blade.php`: sidebar/header/footer admin.
- `resources/views/layouts/partials/lecturer-shell.blade.php`: sidebar/topbar/bottom tabbar giảng viên.
- `resources/views/layouts/partials/student-shell.blade.php`: portal desktop và app frame mobile cho sinh viên.
- `resources/views/components/sams/icon.blade.php`: icon SVG inline dùng chung.
- `resources/views/preview/layout.blade.php`: trang preview layout.

### `routes/`

- `routes/web.php`: route welcome, preview layout, dashboard, profile và import route auth.
- `routes/auth.php`: route đăng ký, đăng nhập, quên mật khẩu, reset mật khẩu, xác thực email, xác nhận mật khẩu, đổi mật khẩu và đăng xuất.
- `routes/console.php`: command `inspire`.

### `config/`

- `config/app.php`: cấu hình tên app, env, debug, URL, timezone, locale, key.
- `config/auth.php`: guard `web`, provider `users`, password broker.
- `config/cache.php`: mặc định cache theo `.env`, default trong `.env.example` là `database`.
- `config/database.php`: cấu hình SQLite/MySQL/MariaDB/PostgreSQL/SQL Server và Redis.
- `config/filesystems.php`: filesystem Laravel.
- `config/logging.php`: logging Laravel.
- `config/mail.php`: mailer; `.env.example` dùng `log`.
- `config/permission.php`: cấu hình Spatie Permission.
- `config/queue.php`: queue sync/database/beanstalkd/sqs/redis.
- `config/services.php`: Postmark, SES, Resend, Slack.
- `config/session.php`: session driver; `.env.example` dùng `database`.

### `public/`

- `public/index.php`: entrypoint HTTP của Laravel.
- `public/.htaccess`: rewrite rule cho Apache và chuyển tiếp Authorization header.
- `public/robots.txt`: robots policy.
- `public/favicon.ico`: file favicon đang có dung lượng `0`.
- `public/hot`: file Vite dev server hot reload; bị ignore trong `.gitignore`.

## 5. Chức năng hệ thống

### Chức năng đã có controller/route triển khai

- Đăng ký tài khoản.
- Đăng nhập.
- Đăng xuất.
- Quên mật khẩu.
- Gửi link reset mật khẩu.
- Đặt lại mật khẩu.
- Xác thực email.
- Gửi lại email xác thực.
- Xác nhận mật khẩu.
- Cập nhật mật khẩu.
- Xem/cập nhật/xóa hồ sơ cá nhân.
- Dashboard sau đăng nhập.
- Preview layout theo vai trò: `admin`, `lecturer`, `student`.

### Admin

Tìm thấy trong layout/menu Admin:

- Dashboard.
- Quản lý tài khoản.
- Nhật ký hệ thống.
- Quản lý khoa.
- Quản lý ngành & môn.
- Quản lý học kỳ.
- Quản lý lớp học.
- Quản lý giảng viên.
- Quản lý sinh viên.
- Quản lý điểm danh.
- Báo cáo & thống kê.
- Cấu hình hệ thống.
- Đăng xuất hệ thống.

Trạng thái triển khai: chỉ tìm thấy trong giao diện layout/menu. Không tìm thấy controller, model, migration hoặc route nghiệp vụ tương ứng cho các chức năng Admin trên, ngoại trừ route preview layout.

### Giảng viên

Tìm thấy trong layout/menu Giảng viên:

- Bảng điều khiển.
- Sinh viên.
- Điểm danh.
- Lớp học của tôi.
- Báo cáo.
- Cài đặt.
- Đăng xuất.

Trạng thái triển khai: dashboard, profile/auth có route thực tế. Các route nghiệp vụ `/students`, `/attendance`, `/courses`, `/analytics` chỉ xuất hiện trong layout/menu, chưa tìm thấy route/controller tương ứng trong source hiện tại.

### Sinh viên

Tìm thấy trong layout/menu Sinh viên:

- Bảng điều khiển.
- Lớp học của tôi.
- Lịch sử điểm danh.
- Thống kê chuyên cần.
- Thông báo.
- Hồ sơ cá nhân.
- Ghi danh/điểm danh bằng QR trên bottom navigation mobile.

Trạng thái triển khai: chỉ tìm thấy trong layout/menu và preview. Không tìm thấy controller, model, migration hoặc route nghiệp vụ tương ứng cho sinh viên trong source hiện tại.

## 6. Database

### Database sử dụng

- `.env.example` mặc định: SQLite.
- Docker Compose: MySQL 8.4 với database `db_quan_ly_diem_danh`.
- `config/database.php` hỗ trợ SQLite, MySQL, MariaDB, PostgreSQL, SQL Server và Redis.

### Danh sách bảng từ migration

| Bảng | Chức năng |
|---|---|
| `users` | Lưu tài khoản người dùng: tên, email, email verification, password, remember token, timestamps. |
| `password_reset_tokens` | Lưu token reset mật khẩu theo email. |
| `sessions` | Lưu session khi `SESSION_DRIVER=database`. |
| `cache` | Lưu cache khi `CACHE_STORE=database`. |
| `cache_locks` | Lưu lock cho cache database. |
| `jobs` | Lưu queue jobs khi `QUEUE_CONNECTION=database`. |
| `job_batches` | Lưu thông tin batch jobs. |
| `failed_jobs` | Lưu jobs thất bại. |
| `permissions` | Bảng quyền của Spatie Permission. |
| `roles` | Bảng vai trò của Spatie Permission. |
| `model_has_permissions` | Gán permission trực tiếp cho model qua polymorphic relation. |
| `model_has_roles` | Gán role cho model qua polymorphic relation. |
| `role_has_permissions` | Gán permission cho role. |

Laravel cũng sẽ tạo bảng `migrations` khi chạy migration để quản lý trạng thái migration.

### Quan hệ giữa các bảng

- `users.id` có liên hệ logic với `sessions.user_id`; migration tạo index nhưng không khai báo foreign key constraint.
- `password_reset_tokens.email` có liên hệ logic với `users.email`.
- `roles.id` liên kết với `model_has_roles.role_id`.
- `roles.id` liên kết với `role_has_permissions.role_id`.
- `permissions.id` liên kết với `model_has_permissions.permission_id`.
- `permissions.id` liên kết với `role_has_permissions.permission_id`.
- `model_has_roles.model_type` + `model_has_roles.model_id` liên kết polymorphic đến model, hiện có `App\Models\User`.
- `model_has_permissions.model_type` + `model_has_permissions.model_id` liên kết polymorphic đến model, hiện có `App\Models\User`.

### ERD dạng văn bản

```text
users
  | id
  | email
  | password
  |
  |--< sessions.user_id                       (logical/index only)
  |--< password_reset_tokens.email            (logical by email)
  |--< model_has_roles.model_id               (polymorphic: App\Models\User)
  |--< model_has_permissions.model_id         (polymorphic: App\Models\User)

roles
  | id
  | name
  | guard_name
  |
  |--< model_has_roles.role_id
  |--< role_has_permissions.role_id

permissions
  | id
  | name
  | guard_name
  |
  |--< model_has_permissions.permission_id
  |--< role_has_permissions.permission_id

cache
cache_locks

jobs
job_batches
failed_jobs
```

## 7. Hướng dẫn cài đặt

### Yêu cầu môi trường

- PHP `^8.2`.
- Composer.
- Node.js và npm.
- Docker Desktop nếu dùng MySQL/Redis theo `docker-compose.yml`.

### Cài đặt dependency

```bash
composer install
npm install
```

### Tạo file môi trường

```bash
wsl --update
```

Trên Windows PowerShell có thể dùng:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### Chạy MySQL và Redis bằng Docker

```bash
docker compose up -d
```

Nếu dùng MySQL từ Docker Compose, cập nhật `.env` theo thông tin có trong `docker-compose.yml`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_quan_ly_diem_danh
DB_USERNAME=root
DB_PASSWORD=root
```

Nếu dùng SQLite theo `.env.example`, đảm bảo file sau tồn tại:

```bash
database/database.sqlite
```

### Chạy migration và seeder

```bash
php artisan migrate
php artisan db:seed
```

Seeder hiện tạo user:

```text
Email: test@example.com
Password: password
```

Mật khẩu trên được suy ra từ `UserFactory.php`.

## 8. Hướng dẫn chạy hệ thống

### Chạy Laravel server

```bash
php artisan serve
```

Ứng dụng mặc định chạy tại:

```text
http://127.0.0.1:8000
```

### Chạy Vite frontend dev server

```bash
npm run dev
```

### Chạy Queue Worker

`.env.example` đang để `QUEUE_CONNECTION=database`, vì vậy lệnh phù hợp với cấu hình mặc định là:

```bash
php artisan queue:work database
```

`start-dev.bat` trong source lại chạy:

```bash
php artisan queue:work redis
```

Vì vậy nếu muốn chạy theo `start-dev.bat`, cần cấu hình `.env`:

```env
QUEUE_CONNECTION=redis
```

### Chạy Socket.IO server

```bash
node server.cjs
```

Socket.IO server lắng nghe tại:

```text
http://localhost:3000
```

### Build frontend production

```bash
npm run build
```

### Chạy toàn bộ bằng script Windows

```bat
start-dev.bat
```

Script này chạy:

docker compose up -d
php artisan serve
php artisan queue:work redis
npm run dev
node server.cjs

Dừng bằng:

```bat
stop-dev.bat
```

### URL kiểm tra giao diện hiện có

```text
http://127.0.0.1:8000/register
http://127.0.0.1:8000/login
http://127.0.0.1:8000/dashboard
http://127.0.0.1:8000/preview/admin
http://127.0.0.1:8000/preview/lecturer
http://127.0.0.1:8000/preview/student
```

## 9. Biến môi trường

Các biến dưới đây được đọc từ `.env.example`.

### `APP_*`

- `APP_NAME=Laravel`: tên ứng dụng.
- `APP_ENV=local`: môi trường chạy.
- `APP_KEY=`: khóa mã hóa Laravel; cần chạy `php artisan key:generate`.
- `APP_DEBUG=true`: bật debug.
- `APP_TIMEZONE=UTC`: timezone.
- `APP_URL=http://localhost`: URL ứng dụng.
- `APP_LOCALE=en`, `APP_FALLBACK_LOCALE=en`, `APP_FAKER_LOCALE=en_US`: cấu hình locale.
- `APP_MAINTENANCE_DRIVER=file`: maintenance mode lưu bằng file.

### `DB_*`

- `DB_CONNECTION=sqlite`: database mặc định theo `.env.example`.
- Các biến MySQL đang bị comment: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Nếu chạy Docker Compose, nên dùng thông tin MySQL từ `docker-compose.yml`: host `127.0.0.1`, port `3306`, database `db_quan_ly_diem_danh`, username `root`, password `root`.

### `SESSION_*`

- `SESSION_DRIVER=database`: session lưu trong bảng `sessions`.
- `SESSION_LIFETIME=120`: session timeout 120 phút.
- `SESSION_ENCRYPT=false`: session không mã hóa ở mức driver.
- `SESSION_PATH=/`, `SESSION_DOMAIN=null`: phạm vi cookie.

### `BROADCAST_*`

- `BROADCAST_CONNECTION=log`: broadcast mặc định ghi log, chưa dùng broadcast driver realtime trong `.env.example`.

### `QUEUE_*`

- `QUEUE_CONNECTION=database`: queue mặc định dùng database.
- `config/queue.php` cũng có cấu hình queue Redis.
- `start-dev.bat` dùng worker Redis, nên cần đổi `.env` sang `QUEUE_CONNECTION=redis` nếu chạy theo script này.

### `CACHE_*`

- `CACHE_STORE=database`: cache mặc định dùng bảng `cache`.
- `CACHE_PREFIX=`: prefix cache chưa cấu hình.

### `REDIS_*`

- `REDIS_CLIENT=phpredis`: Redis client trong `.env.example`.
- `REDIS_HOST=127.0.0.1`
- `REDIS_PASSWORD=null`
- `REDIS_PORT=6379`

Ghi chú: `composer.json` có `predis/predis`. Nếu môi trường không cài PHP extension `phpredis`, có thể dùng `REDIS_CLIENT=predis`.

### `MAIL_*`

- `MAIL_MAILER=log`: email được ghi log.
- `MAIL_HOST=127.0.0.1`
- `MAIL_PORT=2525`
- `MAIL_USERNAME=null`
- `MAIL_PASSWORD=null`
- `MAIL_FROM_ADDRESS="hello@example.com"`
- `MAIL_FROM_NAME="${APP_NAME}"`

### `AWS_*`

- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`.
- Source chưa tìm thấy logic sử dụng AWS ngoài cấu hình mặc định.

### `VITE_*`

- `VITE_APP_NAME="${APP_NAME}"`: truyền tên app sang frontend build.

## 10. Realtime Architecture

### Thành phần realtime tìm thấy

- `redis` service trong `docker-compose.yml`.
- `ioredis`, `socket.io`, `socket.io-client`, `laravel-echo` trong `package.json`.
- `server.cjs` tạo HTTP server và Socket.IO server.
- `server.cjs` kết nối Redis bằng `new Redis()`.
- `server.cjs` đăng ký `redis.psubscribe('*')`.
- Khi nhận Redis pub/sub message, server emit sang Socket.IO:

```js
io.emit(channel, JSON.parse(message));
```

### Luồng dữ liệu realtime thực tế trong source

```text
Publisher bất kỳ ghi JSON message lên Redis channel
    |
    v
Redis Pub/Sub
    |
    v
server.cjs nhận mọi channel bằng psubscribe('*')
    |
    v
Socket.IO emit event có tên bằng Redis channel
    |
    v
Socket.IO clients nhận payload JSON
```

### Event broadcast Laravel

- Không tìm thấy class `ShouldBroadcast`.
- Không tìm thấy method `broadcastOn`.
- Không tìm thấy `routes/channels.php`.
- Không tìm thấy Laravel Echo initialization trong `resources/js`.
- Vì vậy không thể liệt kê event/channel Laravel cụ thể. Thông tin này hiện không có trong source code.

## 11. Security

### Authentication

- Laravel Breeze cung cấp đăng ký, đăng nhập, đăng xuất, reset mật khẩu, xác thực email, xác nhận mật khẩu.
- Guard mặc định: `web`.
- User provider: Eloquent model `App\Models\User`.
- Password được hash bằng `Hash::make`.
- Model `User` ẩn `password` và `remember_token` khi serialize.

### Middleware

Middleware được xác định từ routes:

- `guest`: bảo vệ các trang chỉ dành cho khách như login/register/forgot-password.
- `auth`: bảo vệ dashboard, profile, verify email, confirm password, logout.
- `verified`: bảo vệ dashboard.
- `signed`: bảo vệ route xác thực email có chữ ký.
- `throttle:6,1`: giới hạn gửi xác thực email và route verify email.

Laravel 11 bootstrap middleware callback hiện chưa khai báo middleware custom.

### Rate Limiting

`LoginRequest` giới hạn đăng nhập sai:

- Tối đa 5 lần theo key `email|ip`.
- Khi quá giới hạn sẽ phát event `Lockout`.

### Authorization / RBAC

- `spatie/laravel-permission` đã được cài.
- Migration tạo bảng `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.
- `App\Models\User` dùng trait `HasRoles`.
- `config/permission.php` bật `register_permission_check_method=true`.
- Không tìm thấy route middleware `role`, `permission`, `can` áp dụng cho endpoint nghiệp vụ trong source hiện tại.
- Không tìm thấy seeder tạo role/permission mặc định.

### CSRF và Session

- Blade layout có `<meta name="csrf-token" content="{{ csrf_token() }}">`.
- Axios gửi header `X-Requested-With`.
- Route `sanctum/csrf-cookie` tồn tại do Laravel Sanctum.
- Session mặc định trong `.env.example` dùng database.
- `config/session.php` mặc định `http_only=true` và `same_site=lax`.

### Password Reset / Email Verification

- Password reset dùng bảng `password_reset_tokens`.
- Email verification dùng route signed và throttle.
- Mail mặc định ghi log theo `.env.example`.

## 12. Quy tắc phát triển

### Pattern tìm thấy

- Laravel MVC:
  - Controller xử lý request.
  - FormRequest validate dữ liệu.
  - Model Eloquent đại diện bảng `users`.
  - Blade render giao diện.
- Blade Component:
  - `x-app-layout`, `x-guest-layout`, `x-sams.icon`.
- Layout theo vai trò:
  - `variant="admin"`
  - `variant="lecturer"`
  - `variant="student"`
- RBAC infrastructure:
  - `spatie/laravel-permission`
  - `User::HasRoles`

### Pattern chưa tìm thấy triển khai

- Service Pattern: có thư mục `app/Services` nhưng không có file service.
- Repository Pattern: không tìm thấy thư mục hoặc class repository.
- Event/Listener nghiệp vụ: có thư mục `app/Events` và `app/Listeners` nhưng không có file triển khai.
- Job nghiệp vụ: có thư mục `app/Jobs` nhưng không có file triển khai.
- Import/Export nghiệp vụ: có thư mục `app/Imports`, `app/Exports` và package Excel, nhưng không có file triển khai.
- Middleware custom: có thư mục `app/Middleware` nhưng không có file triển khai.

## 13. API Endpoints

Các endpoint được lấy từ `php artisan route:list`.

| Method | URL | Tên route | Chức năng |
|---|---|---|---|
| GET/HEAD | `/` | Không có | Trang welcome mặc định. |
| GET/HEAD | `/register` | `register` | Hiển thị form đăng ký. |
| POST | `/register` | Không có | Tạo tài khoản mới và đăng nhập. |
| GET/HEAD | `/login` | `login` | Hiển thị form đăng nhập. |
| POST | `/login` | Không có | Xử lý đăng nhập. |
| POST | `/logout` | `logout` | Đăng xuất và hủy session. |
| GET/HEAD | `/forgot-password` | `password.request` | Hiển thị form yêu cầu reset mật khẩu. |
| POST | `/forgot-password` | `password.email` | Gửi link reset mật khẩu. |
| GET/HEAD | `/reset-password/{token}` | `password.reset` | Hiển thị form đặt lại mật khẩu. |
| POST | `/reset-password` | `password.store` | Cập nhật mật khẩu mới bằng token. |
| GET/HEAD | `/verify-email` | `verification.notice` | Hiển thị thông báo cần xác thực email. |
| GET/HEAD | `/verify-email/{id}/{hash}` | `verification.verify` | Xác thực email bằng signed URL. |
| POST | `/email/verification-notification` | `verification.send` | Gửi lại email xác thực. |
| GET/HEAD | `/confirm-password` | `password.confirm` | Hiển thị form xác nhận mật khẩu. |
| POST | `/confirm-password` | Không có | Xác nhận mật khẩu hiện tại. |
| PUT | `/password` | `password.update` | Cập nhật mật khẩu người dùng đã đăng nhập. |
| GET/HEAD | `/dashboard` | `dashboard` | Dashboard sau đăng nhập, yêu cầu `auth` và `verified`. |
| GET/HEAD | `/profile` | `profile.edit` | Hiển thị trang hồ sơ. |
| PATCH | `/profile` | `profile.update` | Cập nhật tên/email hồ sơ. |
| DELETE | `/profile` | `profile.destroy` | Xóa tài khoản người dùng. |
| GET/HEAD | `/preview/{variant}` | `preview.layout` | Preview layout `admin`, `lecturer`, `student`. |
| GET/HEAD | `/sanctum/csrf-cookie` | `sanctum.csrf-cookie` | Lấy CSRF cookie cho Sanctum. |
| GET/HEAD | `/storage/{path}` | `storage.local` | Phục vụ file storage public/local. |
| GET/HEAD | `/up` | Không có | Health check route của Laravel. |

## 14. Thành viên thực hiện

Không tìm thấy thông tin thành viên thực hiện, mã số sinh viên, giảng viên hướng dẫn hoặc tác giả dự án trong source code và README hiện có. Các thông tin tác giả xuất hiện trong `composer.lock` là tác giả package phụ thuộc, không phải thành viên thực hiện dự án.

## 15. Kiểm thử

Source có cấu trúc test:

- `tests/Unit/ExampleTest.php`
- `tests/Feature/ExampleTest.php`
- `tests/Feature/ProfileTest.php`
- `tests/Feature/Auth/*`

Chạy test:

```bash
php artisan test
```

Ghi chú từ `phpunit.xml`:

- `APP_ENV=testing`
- `CACHE_STORE=array`
- `MAIL_MAILER=array`
- `QUEUE_CONNECTION=sync`
- `SESSION_DRIVER=array`
- Cấu hình SQLite in-memory đang bị comment, vì vậy test sẽ dùng database theo `.env` nếu không cấu hình riêng.

## 16. Ghi chú trạng thái triển khai

- README này phản ánh source code tại thời điểm quét.
- Các package Excel, QR Code, Activity Log, Permission, Redis, Socket.IO đã có trong dependency/hạ tầng.
- Source hiện chưa có module nghiệp vụ điểm danh đầy đủ như lớp học, phiên điểm danh, QR attendance, báo cáo, import/export hoặc activity log implementation.
- Các mục menu Admin/Giảng viên/Sinh viên hiện là layout/navigation, chưa đồng nghĩa với việc endpoint nghiệp vụ đã triển khai.
