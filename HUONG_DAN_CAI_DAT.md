# Hướng dẫn Cài đặt, Cấu hình & Sử dụng

Hệ thống **Quản lý Điểm danh & Chuyên cần Sinh viên** — Laravel 11 · Livewire 4 · MySQL 8 · Redis 7 · Socket.IO.

> Bản đầy đủ dạng Word: [HUONG_DAN_CAI_DAT_VA_SU_DUNG.docx](HUONG_DAN_CAI_DAT_VA_SU_DUNG.docx)
> File `README.md` là README mặc định của Laravel, **không phản ánh dự án này**.

---

## 0. Chọn đúng bộ cấu hình

Dự án có **hai bộ cấu hình tách biệt**. Dùng nhầm là hỏng.

| | LOCAL (máy dev) | PRODUCTION (VPS) |
|---|---|---|
| Compose | `docker-compose.local.yml` | `docker-compose.yml` |
| `.env` mẫu | `.env.local.example` | `.env.production.example` |
| Docker chạy gì | Chỉ hạ tầng: MySQL, Redis, phpMyAdmin, Mailpit | Toàn bộ: + PHP-FPM, Queue, Scheduler, Socket.IO, Caddy |
| PHP / Vite | Chạy trực tiếp trên máy (hot reload) | Trong container `app` |
| HTTPS | Không | Có (Caddy tự xin Let's Encrypt) |
| `DB_HOST` / `REDIS_HOST` | `127.0.0.1` | `mysql` / `redis` (tên service) |

---

## 1. Yêu cầu môi trường

| Thành phần | Phiên bản | Ghi chú |
|---|---|---|
| PHP | >= 8.2 | Ext: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `intl`, `curl`, `fileinfo`, `bcmath` |
| Composer | 2.x | |
| Node.js | >= 20 | Vite + `server.cjs` |
| MySQL | 8.x | utf8mb4 |
| Redis | 7.x | **Bắt buộc** — cache, session, queue, broadcast |
| Docker Desktop | mới nhất | |
| ngrok | tuỳ chọn | Chỉ khi test webhook thanh toán/Telegram ở local |

> **Không có Redis thì ứng dụng không chạy.** `.env` đặt `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION`, `BROADCAST_CONNECTION` đều là `redis`.

---

## 2. Cài đặt LOCAL

```bash
cd quan-ly-diem-danh
composer install
npm install

copy .env.local.example .env      # Windows;  Linux/macOS: cp
php artisan key:generate

docker compose -f docker-compose.local.yml up -d

php artisan migrate --seed
php artisan storage:link
```

Khởi động toàn bộ dịch vụ:

```bat
start-dev.bat          :: mở 5 cửa sổ: Laravel, Queue, Scheduler, Vite, Socket.IO
start-dev.bat fresh    :: dựng lại CSDL từ đầu (XOÁ SẠCH dữ liệu)
stop-dev.bat
```

Linux/macOS — chạy thủ công 4 tiến trình:

```bash
php artisan serve
php artisan queue:work redis --queue=imports,mails,default --tries=3
php artisan schedule:work
node server.cjs
```

Truy cập:

| Dịch vụ | URL |
|---|---|
| Ứng dụng | http://127.0.0.1:8000 |
| phpMyAdmin | http://localhost:8080 (`root` / `root`) |
| Mailpit — xem mọi email hệ thống gửi | http://localhost:8025 |

> ⚠️ **Đừng chạy `docker compose up -d` trần ở máy dev** — đó là cấu hình production (build image PHP, chạy Caddy, xin chứng chỉ cho domain thật). Luôn thêm `-f docker-compose.local.yml`.

---

## 3. Cài đặt PRODUCTION

Toàn bộ chạy trong Docker. Chỉ Caddy mở cổng 80/443; MySQL, Redis, Socket.IO nằm trong mạng nội bộ.

```
Internet → caddy (:80/:443) ┬→ app (php-fpm:9000)  ┬→ mysql
                            └→ socketio (:3000)     └→ redis
```

Container `app` chạy đồng thời qua Supervisor: php-fpm + 4 worker `imports` + 2 worker `mails` + 2 worker `default` + scheduler.

### Các bước

1. Trỏ domain về IP VPS (bản ghi A), mở cổng 80 và 443. Caddy cần cổng 80 để xác thực chứng chỉ.

2. Lấy mã nguồn và tạo `.env`:
   ```bash
   git clone <repo> /opt/attendia && cd /opt/attendia
   cp .env.production.example .env
   nano .env
   ```

3. Bắt buộc sửa trong `.env`:

   | Biến | Giá trị |
   |---|---|
   | `APP_DOMAIN` | Domain thật, **không** kèm `https://` |
   | `APP_URL` | `https://` + domain thật |
   | `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD` | Mật khẩu mạnh — `openssl rand -base64 24` |
   | `MAIL_*` | SMTP thật (Gmail: bật 2FA rồi tạo App password) |
   | `TRUSTED_PROXIES` | `*` — **bắt buộc**, xem mục 4.1 |
   | `SOCKET_CORS_ORIGIN` | Domain thật |
   | `VITE_SOCKET_URL` | **Để trống** — Caddy đã proxy cùng origin |

4. Khởi động:
   ```bash
   docker compose up -d --build
   docker compose exec app php artisan key:generate --force
   docker compose restart app
   ```
   Entrypoint tự chờ MySQL, chạy migration, seed `PlanSeeder`, nạp cache cấu hình.

5. Nếu dùng Telegram: `docker compose exec app php artisan telegram:set-webhook`

### Cập nhật phiên bản mới

```bash
cd /opt/attendia && git pull
docker compose up -d --build app
```

### phpMyAdmin trên production

Chỉ lắng nghe `127.0.0.1` của VPS. Truy cập qua SSH tunnel:

```bash
ssh -L 8080:127.0.0.1:8080 user@vps-ip   # rồi mở http://localhost:8080
```

---

## 4. Cấu hình `.env` — các mục dễ sai

### 4.1. Chống gian lận GPS ⚠️

```env
TRUSTED_PROXIES=*      # production, khi Caddy/Nginx là lớp ngoài cùng
GPS_IP_CHECK=true
```

Đứng sau proxy mà quên `TRUSTED_PROXIES` → Laravel thấy **mọi sinh viên có cùng một IP** (IP của proxy). Hậu quả: cả lớp bị chấm gian lận, hoặc kiểm tra IP/VPN bị vô hiệu hoàn toàn. Có Cloudflare đứng trước thì liệt kê dải CIDR của Cloudflare thay vì `*`.

Để trống `GPS_IP_CHECK` = tự bật khi có `TRUSTED_PROXIES`, tự tắt khi chưa (an toàn cho máy dev).

### 4.2. Realtime — khác biệt local/production ⚠️

```
Laravel broadcast → Redis pub/sub → server.cjs → io.emit → trình duyệt → Livewire $refresh
```

| | Giá trị |
|---|---|
| Local | `VITE_SOCKET_URL=http://127.0.0.1:3000` |
| Production | `VITE_SOCKET_URL=` (để trống) |

Lý do: production có Caddy proxy `/socket.io` nên web và socket **cùng origin**. Local thì web ở cổng 8000, socket ở cổng 3000, mà `php artisan serve` không proxy được. Đổi biến này phải chạy lại `npm run dev` / `npm run build`.

### 4.3. Đăng nhập Google

`GOOGLE_REDIRECT_URI` phải khớp **chính xác** với Authorized redirect URI khai ở Google Cloud Console (kể cả http/https và dấu `/` cuối).

### 4.4. Thanh toán

PayOS (my.payos.vn) và MoMo (developers.momo.vn). Webhook cần URL public — local dùng `ngrok http 8000` rồi cập nhật `MOMO_IPN_URL`.

> **Không commit `.env` chứa credentials thật.** Key đã từng bị đẩy lên repo phải coi như đã lộ: cấp lại ở phía nhà cung cấp và chạy `php artisan key:generate`.

### 4.5. Telegram

Bot Token **không** đặt trong `.env` mà nhập ở `/admin/{id}/settings`. Sau đó:

```bash
php artisan telegram:set-webhook    # chạy lại sau mỗi lần deploy/đổi domain
php artisan telegram:test
```

Sinh viên phải nhắn `/start` cho bot một lần để hệ thống tự liên kết Chat ID.

---

## 5. Hàng đợi & tác vụ định kỳ

**Queue** — 3 hàng theo ưu tiên `imports` > `mails` > `default`.

**Scheduler** (`php artisan schedule:work`):

| Lệnh | Tần suất | Chức năng |
|---|---|---|
| `attendance:close-expired` | mỗi phút | Tự chốt buổi quá giờ |
| `import:flush-notifications --limit=200` | mỗi phút | Gửi mail kết quả import có tiết chế |
| `transactions:sync-status` | 5 phút | Đồng bộ trạng thái giao dịch |
| `attendance:cleanup-expired-sessions` | mỗi giờ | Dọn phiên của buổi kết thúc > 48h |
| `db:backup` | 00:00 | Sao lưu CSDL |
| `attendance:cleanup-scans --days=90` | 01:00 | Xoá log quét cũ > 90 ngày |

---

## 6. Hướng dẫn sử dụng

### 6.1. Tài khoản demo

Mật khẩu chung: **`password`**. Chi tiết: [demo_account.md](demo_account.md).

| Vai trò | Email |
|---|---|
| Admin | `nguyenkhoi020705@gmail.com` |
| Chủ lớp (gói PRO) | `minhhieut947@gmail.com` |
| Đồng chủ | `thaobee2407@gmail.com` |
| SV — cấm thi (~73%) | `0306231156@caothang.edu.vn` |
| SV — cảnh báo (~83%) | `0306231120@caothang.edu.vn` |

Lớp mẫu: `WEB2026` (đầy đủ dữ liệu) · `DB2026` (trống, để demo tạo lớp/import) · `SE2026` (bật duyệt, có 2 yêu cầu chờ).

### 6.2. Vai trò

Hệ thống **không phân vai cứng** giảng viên/sinh viên — một tài khoản vừa tạo lớp (thành chủ lớp) vừa tham gia lớp khác (thành sinh viên). Chỉ ADMIN là vai trò riêng.

| Vai trò | Quyền |
|---|---|
| Admin | Toàn hệ thống |
| Chủ lớp | Toàn quyền trên lớp mình tạo, kể cả xoá lớp và quản lý đồng chủ |
| Đồng chủ | Quản lý lớp, sửa cài đặt — **không** xoá lớp, **không** thêm/bớt đồng chủ |
| Sinh viên | Điểm danh, xin nghỉ, xem chuyên cần của mình |

### 6.3. Chủ lớp / giảng viên

1. **Tạo lớp** — `Lớp quản lý` → *Tạo lớp*. Hệ thống sinh mã tham gia (join key) + QR vào lớp.
2. **Thêm sinh viên** — SV tự vào bằng mã/QR, import Excel, hoặc gửi link `/join/{mã lớp}`.
   > Import **chỉ đọc sheet đầu tiên** — template phải đơn sheet, nếu không lệch cột. Dùng mẫu `Mau_Import_Nhieu_Lop_Hoc.xlsx`. Chạy nền qua queue, kết quả gửi email.
3. **Duyệt yêu cầu** — nếu bật chế độ duyệt: *Thành viên chờ duyệt*.
4. **Cài đặt lớp** — vị trí + bán kính GPS · điểm trừ (trễ/vắng/có phép) · ngưỡng chuyên cần (quỹ vắng %, mức cảnh báo; cấm thi = 100 − quỹ vắng) · thêm đồng chủ.
5. **Điểm danh** — `Điểm danh` → *Tạo buổi*. Một **buổi** (meeting) gồm nhiều **phiên** (session); bản ghi gắn ở phiên.
   - *QR*: mở phiên → chiếu QR (có nút phóng to) → SV quét → danh sách cập nhật realtime.
   - *Thủ công*: tick trực tiếp. Dùng khi mất mạng hoặc điểm danh bù.
6. **Đơn xin nghỉ** — `Sinh viên` → *Xin nghỉ*: xem minh chứng, duyệt/từ chối. Đơn duyệt tính là *có phép*.
7. **Cảnh báo chuyên cần** — lọc *Nguy cơ cấm thi* → gửi cảnh báo qua 3 kênh: in-app, email, Telegram.
8. **Thống kê & báo cáo** — biểu đồ theo buổi/sinh viên. **Xuất Excel yêu cầu gói PRO.**

### 6.4. Sinh viên

1. **Vào lớp** — nhập mã ở Dashboard, quét QR vào lớp, hoặc mở link `/join/{mã}`.
2. **Điểm danh** — quét QR giảng viên chiếu. Bộ quét nhận **cả QR điểm danh lẫn QR vào lớp**.
   > Trình duyệt hỏi quyền **Camera** và **Vị trí** — phải cho phép **cả hai**. Từ chối quyền vị trí thì không ghi nhận được điểm danh. Đứng ngoài bán kính cho phép cũng bị từ chối.
3. **Xin nghỉ** — tạo đơn, đính kèm minh chứng, theo dõi trạng thái.
4. **Theo dõi chuyên cần** — `Thống kê`: % chuyên cần, số buổi còn được vắng. `Cảnh báo`: các cảnh báo đã nhận.
5. **Nâng cấp gói** — chọn gói → thanh toán PayOS/MoMo → `Lịch sử giao dịch`.

### 6.5. Admin (`/admin/{id}`)

Người dùng · Gói dịch vụ & mã giảm giá · Giao dịch · Báo cáo · Thông báo hàng loạt · Nhật ký (audit + hệ thống) · Cấu hình (chung, email, Telegram Bot Token, bảo trì) · Phản hồi.

---

## 7. Xử lý sự cố

| Hiện tượng | Nguyên nhân / cách xử lý |
|---|---|
| Màn hình giảng viên không tự cập nhật khi SV quét QR | Kiểm tra theo thứ tự: Redis chạy? · `server.cjs` chạy ở cổng 3000? · local đã đặt `VITE_SOCKET_URL=http://127.0.0.1:3000` và chạy lại `npm run dev`? · `BROADCAST_CONNECTION=redis`? |
| Composer báo `Access is denied` ở `bootstrap/cache/packages.php` (Windows) | `queue:work`/`schedule:work` đang giữ file. Đóng các cửa sổ đó rồi chạy lại — **không phải lỗi quyền thư mục**. |
| `docker compose up -d` lỗi build ở máy dev | Dùng nhầm file production → `docker compose -f docker-compose.local.yml up -d` |
| Email không gửi | Local: mở Mailpit http://localhost:8025. Nếu `MAIL_MAILER=log` thì chỉ ghi vào `storage/logs`. Cảnh báo chuyên cần mặc định tắt kênh mail — bật trong cài đặt lớp. |
| Import Excel lệch cột | Template phải **đơn sheet**. |
| Telegram không gửi | Thiếu Bot Token ở `/admin/settings` · chưa bật thông báo Telegram · SV chưa `/start` bot · chưa chạy `telegram:set-webhook` sau deploy. |
| Sửa `.env` không có tác dụng | `php artisan optimize:clear` (production: `docker compose exec app php artisan optimize:clear` rồi `config:cache`) |
| SV bị chấm gian lận GPS oan | Kiểm tra `TRUSTED_PROXIES` (mục 4.1), hoặc nới bán kính trong cài đặt lớp. |
| Migration lỗi sau khi sửa migration cũ | `start-dev.bat fresh` (⚠️ xoá sạch dữ liệu) |
| Caddy không xin được chứng chỉ | Domain phải trỏ đúng IP VPS và **cổng 80 phải mở** (Let's Encrypt cần). `docker compose logs caddy` |
| Trắng trang / lỗi 500 trên production | `docker compose logs app` và `docker compose exec app tail -50 storage/logs/laravel.log`. Thường do thiếu `APP_KEY` hoặc sai thông tin CSDL. |

### Kiểm thử

```bash
php artisan test
```

> Có ~20 test fail sẵn do tham số route mặc định `ma_user` trong harness test — **không phải lỗi runtime**.

---

## 8. Danh sách file cấu hình

| File | Môi trường | Vai trò |
|---|---|---|
| `docker-compose.local.yml` | Local | MySQL, Redis, phpMyAdmin, Mailpit |
| `.env.local.example` | Local | Mẫu cấu hình máy dev |
| `start-dev.bat` / `stop-dev.bat` | Local | Bật/tắt dịch vụ trên Windows |
| `docker-compose.yml` | Production | Full stack: app, mysql, redis, socketio, caddy, phpmyadmin |
| `.env.production.example` | Production | Mẫu cấu hình VPS |
| `Dockerfile` | Production | Build image PHP-FPM kèm asset frontend |
| `docker/caddy/Caddyfile` | Production | Reverse proxy, HTTPS, proxy `/socket.io` |
| `docker/php/supervisord.conf` | Production | php-fpm + queue worker + scheduler |
| `docker/php/entrypoint.sh` | Production | Chờ MySQL, migrate, seed, nạp cache |
| `scripts/supervisor-queues.conf.example` | Production | Khi cài trực tiếp trên VPS, không qua Docker |

---

## 9. Tài liệu liên quan

- [HUONG_DAN_CAI_DAT_VA_SU_DUNG.docx](HUONG_DAN_CAI_DAT_VA_SU_DUNG.docx) — bản Word đầy đủ
- [demo_account.md](demo_account.md) — tài khoản & kịch bản demo theo module
- [docs/LUONG_XU_LY_DIEM_DANH.md](docs/LUONG_XU_LY_DIEM_DANH.md) — luồng xử lý điểm danh
- [database_schema_explanation.md](database_schema_explanation.md), [schema_dump.md](schema_dump.md) — cấu trúc CSDL
