# Luồng xử lý điểm danh — Tài liệu kỹ thuật

> Mô tả toàn bộ các file xử lý điểm danh, vai trò từng file, biến/hàm chính và LUỒNG ĐI dữ liệu.
> Đơn vị tính chuyên cần là **BUỔI**; một **buổi** gồm nhiều **phiên** (thủ công hoặc QR).

---

## 1. Mô hình dữ liệu (phân cấp)

```
CourseClass (lớp / học phần)               bảng: classes
  └── ClassMember (thành viên/sinh viên)   bảng: class_members
  └── ClassMeeting (BUỔI học)              bảng: class_meetings
        └── ClassSession (PHIÊN điểm danh) bảng: class_sessions   (meeting_id, qr_token, share_token, gps_*)
              └── AttendanceRecord         bảng: attendance_records (status + dấu vết GPS/thiết bị từng SV/phiên)
              └── GpsVerification          bảng: gps_verifications  (vé GPS 1 lần: token/check_token/is_used)
              └── CheckInScan              bảng: check_in_scans     (nhật ký mỗi lần quét — bất biến)
        └── MeetingSummary (tổng kết buổi) bảng: meeting_summaries  (status + deduction mỗi SV/buổi)
```

- **Phiên thủ công**: `qr_token = NULL`. Giảng viên tự đánh dấu; khi tính chuyên cần, phiên thủ công `pending` được **hiểu là Có mặt** (`interpretStatus`).
- **Phiên QR**: `qr_token != NULL`. Sinh viên quét mã/GPS; chưa quét (`pending`) → khi chốt/tổng kết **coi như Vắng**.
- `attendance_records.status` ∈ `present | late | absent | excused | pending` *(giá trị `invalid` là **legacy** — luồng mới không dùng nữa; điểm danh bất thường nay vẫn `present` và gắn `gps_fraud_flag`)*.
- `meeting_summaries.status` (tổng kết) ∈ `present | late | partial | early_leave | absent | excused`.

**Hai loại token của phiên QR** (`class_sessions`):
- `qr_token` — mã nhúng trong **ảnh QR**, **XOAY** liên tục theo `qr_refresh_rate` (chống chụp màn hình gửi bạn vắng). Có `token_expires_at` ngắn.
- `share_token` — mã cho **link chia sẻ**, **CỐ ĐỊNH**, sống suốt lúc phiên mở (tự sinh trong `ClassSession::booted()`).

**Cột chống gian lận trên `attendance_records`:** `device_id` (khóa chính chống điểm danh hộ), `device_fingerprint` (md5 IP+UA — phụ), `distance_meters`, `gps_accuracy_meters`, `gps_latitude_recorded`, `gps_longitude_recorded`, `gps_fraud_flag`, `note`.

**Giá trị `gps_fraud_flag`:** `device_duplicate` (đỏ — cùng máy điểm danh cho ≥2 SV) · `out_of_radius` (vàng — ngoài bán kính nhưng VẪN có mặt) · `impossible_travel` (di chuyển bất khả thi) · `suspected_mock` (nghi giả lập vị trí / VPN).

---

## 2. Danh sách file & vai trò

### Lõi tính toán
| File | Vai trò |
|---|---|
| `app/Services/AttendanceCalculator.php` | **NGUỒN DUY NHẤT** tính điểm chuyên cần: quy tắc tổng kết 6 trạng thái, điểm trừ, % chuyên cần, đồng bộ `meeting_summaries`. |

### Models
| File | Vai trò |
|---|---|
| `app/Models/CourseClass.php` | Lớp học (`total_sessions`, `deduct_*`, GPS mặc định…). |
| `app/Models/ClassMeeting.php` | Buổi học. Có `endsAt()`, `isExpired()`, `canAddSession()`, `closeIfExpired()`, `createSession()`. |
| `app/Models/ClassSession.php` | Phiên điểm danh (thuộc 1 buổi qua `meeting_id`). Sinh/xoay token QR: `generateQrToken()`, `generateShareToken()`, `rotateQrToken()`, `qrTokenExpiryFor()`. |
| `app/Models/AttendanceRecord.php` | Trạng thái + dấu vết GPS/thiết bị của 1 SV trong 1 phiên. |
| `app/Models/GpsVerification.php` | **Vé GPS 1 lần** (`token`, `check_token`, `is_used`, `expires_at`, `fraud_score`). |
| `app/Models/CheckInScan.php` | Nhật ký mỗi lần quét (bất biến) — dựng lịch sử thiết bị xuyên phiên. |
| `app/Models/MeetingSummary.php` | Kết quả tổng kết của 1 SV trong 1 buổi (status, deduction, is_overridden). |

### Livewire — Giảng viên (`app/Livewire/Lecturer/Attendance/`)
| File | Route | Vai trò |
|---|---|---|
| `AttendanceIndex.php` | `lecturer.attendance.index` | Dashboard điểm danh: liệt kê các BUỔI, tự chốt buổi hết giờ, thêm phiên. |
| `AttendanceCreate.php` | `lecturer.attendance.create` | **Tạo buổi** (chỉ nhập giờ kết thúc) + chọn phương thức. Thủ công → tạo luôn phiên; QR → chỉ tạo BUỔI rồi chuyển sang trang cấu hình QR. |
| `MeetingSessions.php` | `lecturer.attendance.meeting.sessions` | Danh sách các PHIÊN trong 1 buổi; thêm phiên; vào trang Tổng kết. |
| `ManualAttendanceSession.php` | `lecturer.attendance.manual.session` | Điểm danh **thủ công** 1 phiên (đánh dấu tạm → Lưu phiên). |
| `QrAttendanceCreate.php` | `lecturer.attendance.qr.create` | **Cấu hình & tạo phiên QR** (GPS, bán kính, nhịp làm mới, kiểm tra thiết bị) — sinh phiên qua `meeting->createSession()`. |
| `QrAttendanceSession.php` | `lecturer.attendance.qr.session` | **Trạm QR** realtime: phát mã (xoay token), theo dõi check-in, bộ lọc gian lận, chốt phiên. |
| `MeetingSummary.php` | `lecturer.attendance.meeting.summary` | **Tổng kết buổi**: hiện trạng thái gộp, sửa tay, xuất Excel. |
| `app/Livewire/Lecturer/ClassAttendanceHistory.php` | `lecturer.classes.attendance` | **Lưới** SV × buổi của cả lớp. |

### Livewire — Sinh viên (`app/Livewire/Student/`)
| File | Route | Vai trò |
|---|---|---|
| `AttendanceCheckIn.php` | `attendance.check-in.guest` (`/attendance/check-in/{token}`) | Trang SV quét QR / xác minh GPS để điểm danh 1 phiên. Nhận **cả** `qr_token` lẫn `share_token`. |
| `AttendanceHistory.php` | `student.attendance.history` | Lịch sử điểm danh của SV. |
| `AttendanceStats.php` | `student.attendance.stats` | Thống kê chuyên cần của SV. |

### Controllers / Services / Export / Command
| File | Vai trò |
|---|---|
| `app/Http/Controllers/GpsVerificationController.php` | Endpoint `gps.token` (cấp vé) & `gps.verify` (xác minh toạ độ) khi điểm danh QR có GPS. |
| `app/Services/GpsValidationService.php` | Cấp/tiêu vé GPS, Haversine `calculateDistance()`, chấm điểm nghi fake GPS `computeFakeGpsScore()`, tra IP/VPN. |
| `app/Services/NotificationService.php` | Thông báo: tạo/chốt phiên, kết quả QR, **trùng thiết bị**, **ngoài bán kính**, **máy điểm danh hộ**, sắp vượt quỹ vắng. |
| `app/Exports/ClassSessionExport.php` | Xuất Excel 1 PHIÊN. |
| `app/Exports/MeetingSummaryExport.php` | Xuất Excel TỔNG KẾT 1 buổi. |
| `app/Console/Commands/CloseExpiredMeetings.php` | Lệnh nền `attendance:close-expired` tự chốt buổi quá giờ (chạy mỗi phút). |
| `app/Console/Commands/CleanupOldCheckInScans.php` | Dọn nhật ký `check_in_scans` cũ. |

### Services tiêu thụ AttendanceCalculator (thống kê/cảnh báo)
| File | Dùng để |
|---|---|
| `app/Services/LectureManageStudentService.php` | Bảng quản lý học viên (Có mặt/Muộn/Vắng/Phép, %, cấm thi). |
| `app/Services/StudentsService.php` | Cảnh báo & dashboard phía SV. |
| `app/Services/StatisticalService.php` | Thống kê theo môn/lớp. |
| `app/Services/DashboardStatisticService.php` | Số liệu tổng quan dashboard. |

---

## 3. LUỒNG 1 — Tạo buổi điểm danh

```
GV mở "Tạo buổi điểm danh"  →  AttendanceCreate (route lecturer.attendance.create)
  mount(): date = hôm nay; meetingEndTime = now+90' (kẹp ≤ 23:55 nếu qua ngày); GV CHỈ nhập GIỜ KẾT THÚC.

  ├─ "Điểm danh thủ công"  → createManualSession() → createBaseSession('active')
  │       ├─ createBaseMeeting(): validate (lớp/tên/giờ kết thúc ≥ now+10'), có SV active
  │       ├─ tạo ClassMeeting (date=hôm nay, start=now, end=meetingEndTime, status=active)
  │       ├─ tạo ClassSession (status=active, qr_token=NULL)             ← phiên thủ công
  │       ├─ tạo AttendanceRecord cho mọi SV active, status='present'    (thủ công mặc định có mặt)
  │       └─ audit 'session_created' → redirect lecturer.attendance.index
  │
  └─ "Điểm danh QR"        → createQrSession() → createBaseMeeting()      ← CHỈ tạo BUỔI, CHƯA có phiên
          ├─ tạo ClassMeeting (status=active) + audit
          └─ redirect  lecturer.attendance.qr.create?meeting=ID  (QrAttendanceCreate)
                 │
                 └─ QrAttendanceCreate.save(): validate (bán kính 10–2500m, refresh ∈ 5/10/15/30, GPS…)
                        ├─ qr_token = generateQrToken();  token_expires_at = qrTokenExpiryFor(refresh)
                        ├─ gps_latitude/longitude/radius, device_check theo cấu hình
                        ├─ meeting->createSession('active', {qr_token, gps..., device_check})
                        │      → share_token tự sinh (booted); tạo record 'pending' cho mọi SV active
                        └─ redirect  lecturer.attendance.qr.session
```

**Lưu ý về trạng thái record mặc định (2 nguồn tạo phiên):**
- `AttendanceCreate::createBaseSession('active')` (phiên thủ công đầu tiên): record mặc định `present`.
- `ClassMeeting::createSession()` (tạo phiên QR, hoặc **thêm phiên** từ `MeetingSessions`/`QrAttendanceCreate`): mặc định `pending` nếu có `qr_token` (QR), ngược lại `absent` (thủ công thêm sau).

---

## 4. LUỒNG 2 — Điểm danh thủ công

```
ManualAttendanceSession (route lecturer.attendance.manual.session/{session})
  mount(): closeIfExpired() (chốt nếu buổi hết giờ) → initDrafts()
  initDrafts(): nạp record vào $draftStatuses/$draftNotes (TẠM, chưa ghi DB).
               Record 'pending' được hiển thị mặc định là 'present' (thủ công mặc định có mặt).
  GV thao tác:
   ├─ setStatus(recordId, status)   → đổi $draftStatuses[recordId] (chỉ trong bộ nhớ)
   ├─ markAllPresent()              → mọi 'pending' → 'present' (tạm)
   └─ saveSession()                 → GHI $draftStatuses xuống attendance_records
                                       (set check_in_time cho present/late) → về MeetingSessions
  Khác: createDuplicateManualSession() (thêm phiên mới), exportExcel() (ClassSessionExport).
```

Lưu ý: **không chốt sổ** — phiên giữ `status='active'`, chuyên cần được gộp khi buổi kết thúc.

---

## 5. LUỒNG 3 — Điểm danh QR (đầy đủ, gồm chống gian lận)

### (A) Giảng viên — `QrAttendanceSession` (route `lecturer.attendance.qr.session/{session}`)

```
render():
  $qrLink        = route('attendance.check-in.guest', ['token' => qr_token])     ← ẢNH QR (xoay)
  $attendanceLink= route('attendance.check-in.guest', ['token' => share_token])  ← LINK chia sẻ (cố định)
  - Bảng SV realtime: sự kiện StudentCheckedIn → Redis → server.cjs → socket.io → $wire.$refresh()
  - Bộ lọc gian lận: 'same_device' (cùng device_id ≥2 SV) · 'out_of_radius' (cờ gps_fraud_flag)
  - Ưu tiên xếp lên đầu các bản ghi có cờ gian lận / ghi chú cảnh báo.

refreshToken()  (Alpine gọi mỗi qr_refresh_rate giây, và nút "Làm mới QR"):
  - buổi hết giờ → closeIfExpired() → dừng xoay
  - còn mở → rotateQrToken(): sinh qr_token MỚI + token_expires_at ngắn
            → ảnh chụp mã cũ gửi đi sẽ tra cứu thất bại (hết hiệu lực ~1 nhịp)

closeSession(): status='closed'; mọi record 'pending' → 'absent';
                thông báo kết quả QR cho từng SV; đồng bộ tổng kết; về MeetingSessions.
```

### (B) Sinh viên — `AttendanceCheckIn` (route `/attendance/check-in/{token}`, không cần auth)

**B1. `mount($token)` — chuỗi cửa kiểm tra:**
```
1. Tìm phiên: WHERE qr_token = token OR share_token = token   → không thấy: "Mã không hợp lệ".
2. status='closed'                                            → "Phiên đã kết thúc".
3. Hết hạn token: CHỈ áp cho qr_token (ảnh xoay); share_token BỎ QUA hạn.
                   qr_token quá token_expires_at             → "Mã QR đã hết hạn".
4. Danh tính:
     - Đã đăng nhập: phải là ClassMember STATUS_ACTIVE của lớp → không: "Không thuộc lớp".
                     → initializeRecord(); isAutoCheckIn=true (tự bấm điểm danh).
     - Khách: hiện form Họ tên+Email → submitGuestForm() dò SV theo email trong lớp.
```

**B2. `initializeRecord()` — chống điểm danh 2 lần:**
```
record = AttendanceRecord::firstOrCreate({session, member}, {status:'pending'})   ← 1 record / (phiên×SV)
if record.status ∈ [present, late, excused]:
     isSuccess=true; isAutoCheckIn=false;  → "Bạn đã điểm danh cho phiên này rồi."
```

**B3. `checkIn(check_token, deviceId)` — ghi điểm danh (client điều phối, xem blade `performCheckIn`):**

*Nếu phiên CÓ GPS, client chạy 3 bước con trước khi gọi checkIn:*
```
Bước 1  POST /gps/token   (gps.token, throttle 10/phút)
        → GpsValidationService::issueVerificationToken()
        → tạo gps_verifications { token(64), is_used=false, expires_at=+5' }

Bước 2  client lấy 3 mẫu GPS cách ~0.8s (GPS thật 'rung', fake đứng yên)
        POST /gps/verify   (gps.verify, throttle 5/phút)
        → verifyLocation():
            · vé phải: is_used=false, chưa hết hạn                  → sai: "Token không hợp lệ"
            · accuracy ≤ 150m                                       → cao hơn: yêu cầu ra chỗ thoáng
            · calculateDistance() tới lớp (Haversine)  ← KHÔNG chặn "quá xa" ở bước này
            · computeFakeGpsScore(): accuracy≤1m(+2), toạ độ tĩnh/thiếu độ cao(+1 yếu),
                                     VPN/proxy(+3), lệch IP↔GPS>150km(+3)
            · cấp check_token(64), lưu lat/lng/accuracy/fraud_score, expires_at=+2'
            · trả warnings (vd 'vpn' → client hỏi tắt VPN trước khi tiếp)

Bước 3  $wire.checkIn(check_token, deviceId)
```

*Server `checkIn()` — trình tự:*
```
1. Chống 2 lần (lặp lại): record.refresh(); nếu present/late/excused → "đã điểm danh rồi".
2. Phiên còn mở? closeIfExpired() theo giờ kết thúc buổi (KHÔNG phụ thuộc hạn QR ngắn — bấm chậm vẫn được).
3. status := 'present'   ← QR CHỈ ghi CÓ MẶT, KHÔNG tự tính 'late' ở mức phiên.
4. Trùng thiết bị (nếu device_check bật): tìm SV KHÁC cùng device_id đã check-in trong phiên
     → cờ 'device_duplicate' (đỏ) cho CẢ 2 record + ghi chú + báo chủ lớp.
5. Xác minh GPS (nếu phiên có toạ độ):
     · consumeCheckToken(): khớp check_token, đúng phiên, chưa dùng, chưa hết → ĐÁNH DẤU is_used=true.
     · effectiveDistance = distance − accuracy (trừ sai số đo, tránh "gần mà báo xa").
     · effectiveDistance > gps_radius:  VẪN 'present' + cờ VÀNG 'out_of_radius' + số mét vượt
                                        + báo chủ lớp + thẻ cảnh báo vàng cho SV.
     · detectImpossibleTravel(): so lần điểm danh trước cùng người > 300km/h → 'impossible_travel'.
     · fraud_score ≥ 2 (THRESHOLD): cờ 'suspected_mock' + thẻ ĐỎ "Sai GPS" cho SV + ghi chú GV.
6. record.update({status, check_in_time, distance, gps_*, gps_fraud_flag, device_id, note ghép lý do}).
7. logCheckInScan() → check_in_scans (nhật ký bất biến).
8. SaveAuditLogJob → audit 'attendance_check_in'.
9. escalateProxyDevice(): 1 device_id điểm danh cho ≥3 SV khác nhau trong lớp → báo "máy điểm danh hộ".
10. event(StudentCheckedIn) — best-effort, bọc try/catch (realtime hỏng không làm hỏng điểm danh).
```

*Nếu phiên KHÔNG có GPS:* client gọi thẳng `checkIn(null, deviceId)` → ghi `present`.

**Kết quả hiển thị cho SV** (`attendance-check-in.blade.php`):
- Xanh: "Điểm danh thành công!"
- Vàng (`out_of_radius`): đã ghi có mặt nhưng ngoài bán kính (kèm số mét vượt).
- Đỏ (`suspected_mock`): đã ghi có mặt nhưng tín hiệu GPS bất thường → yêu cầu tắt VPN/định vị giả.
- Xanh (quét lại): "Bạn đã điểm danh cho phiên này rồi."

**Ghi nhớ:** SV không quét → record giữ `pending` → khi chốt/tổng kết QR coi như VẮNG.

---

## 6. LUỒNG 4 — Tự động chốt buổi (hết giờ)

```
ClassMeeting::endsAt()       = date + end_time  (nếu ≤ start → +1 ngày: buổi qua đêm)
ClassMeeting::isExpired()    = now > endsAt()
ClassMeeting::canAddSession()= ! isExpired()  và  buổi không thuộc ngày hôm trước
ClassMeeting::closeIfExpired():
   nếu quá giờ → mọi phiên status='closed';
                 phiên QR: record 'pending' → 'absent' + notifyQrSessionResults();
                 syncSummaries() + notifyMeetingResults() + cảnh báo sắp vượt quỹ vắng.

Gọi LAZY khi truy cập: AttendanceIndex (render), MeetingSessions/MeetingSummary (mount),
                       Manual/QrAttendanceSession (mount + trước mỗi thao tác ghi).
Gọi NỀN: command attendance:close-expired (routes/console.php, everyMinute).
```

---

## 7. LUỒNG 5 — Tổng kết buổi (cốt lõi)

```
MeetingSummary (route lecturer.attendance.meeting.summary/{meeting})
  mount(): closeIfExpired() → AttendanceCalculator::syncSummaries($meeting) → loadDrafts()
  render():
     AttendanceCalculator::consolidateMeeting($meeting)
        → mỗi SV: chuỗi trạng thái từng phiên (interpretStatus theo qr_token)
        → consolidateStatuses() → {status, deduction, label}  (6 trạng thái)
  GV chỉnh tay: setStatus() → save() (đánh dấu is_overridden=true nếu khác auto_status)
  recompute(): bỏ override, tính lại từ phiên.
  exportExcel(): MeetingSummaryExport (cột từng phiên + Tổng kết + Điểm trừ).
```

**Bảng `meeting_summaries`:** `auto_status` (hệ thống tự tính) vs `status` (giá trị hiệu lực, có thể GV sửa) + `is_overridden` + `deduction`.

---

## 8. LUỒNG 6 — % chuyên cần / cảnh báo / cấm thi

```
Mỗi service (LectureManageStudent/Students/Statistical/DashboardStatistic/Notification):
  1) Truy vấn attendance_records của PHIÊN ĐÃ CHỐT (cs.status='closed'),
     SELECT kèm: meeting_id, class_session_id, qr_token, status.
  2) AttendanceCalculator::consolidateByMeeting($rows)
        → $counts = { present, late, partial, early_leave, excused, absent, total, deduction }
  3) % = AttendanceCalculator::percentOfPlanned($planned, $counts, $deduct)
        planned = max(total_sessions của lớp, số buổi đã diễn ra)
        counted = planned − excused (nếu lớp bật trừ vắng có phép)
        % = (counted − lostFromCounts($counts)) / counted × 100
  4) Quỹ vắng/cấm thi:
        allowedAbsent = allowedAbsentSessions(planned)   (= 20% planned)
        effectiveAbsence($counts) > allowedAbsent  HOẶC  % < 80%  → CẤM THI
        % < 85% (chưa cấm) → CẢNH BÁO
```

Hiển thị: trang Tổng kết/Lưới lớp/Chi tiết HV hiện **đủ 6 nhãn**; trang list/thẻ gộp
"vắng giữa giờ" vào nhóm *Đi muộn*, "về sớm" vào nhóm *Vắng* (gọn 4 cột), nhưng % vẫn tính chính xác.

---

## 9. Bảng tra cứu hàm `AttendanceCalculator`

| Nhóm | Hàm | Ý nghĩa |
|---|---|---|
| Quy tắc | `isPresent($status)` | Phiên có tính là "có mặt" không (present/late/excused). |
| | `interpretStatus($status,$isQr)` | `pending` → QR=absent / thủ công=present. |
| | `consolidateStatuses($statuses,$deductExcused)` | **Tổng kết 1 buổi** → {status, deduction, label}. |
| | `classifyPattern($bits)` *(private)* | Lõi: phân loại 6 trạng thái từ chuỗi 0/1. |
| | `deductionForStatus($status,$deductExcused)` | Điểm trừ của 1 trạng thái (tra `DEDUCTIONS`). |
| | `statusLabel($status)` | Nhãn tiếng Việt. |
| Gộp buổi | `consolidateByMeeting($rows,$deductExcused)` | Đếm số buổi theo trạng thái (cho thống kê). |
| % chuyên cần | `countedSessions($planned,$excused,$deduct)` | Mẫu số (đã trừ vắng có phép nếu cần). |
| | `allowedAbsentSessions($planned)` | Quỹ vắng = 20% planned. |
| | `lostFromCounts($counts)` | Tổng điểm trừ (bỏ có phép). |
| | `effectiveAbsence($counts)` | Vắng quy đổi để xét cấm thi. |
| | `percentOfPlanned($planned,$counts,$deduct)` | **% chuyên cần** (0..100). |
| | `attendedWeight($counted,$counts)` | Trọng số để cộng dồn nhiều SV. |
| DB-aware | `consolidateMeeting($meeting)` | Tổng kết mọi SV của 1 buổi (kèm trạng thái từng phiên). |
| | `syncSummaries($meeting)` | Ghi/đồng bộ vào `meeting_summaries` (giữ override). |

### Hằng số
- `MIN_ATTENDANCE_PERCENT = 80` (cấm thi) · `WARNING_PERCENT = 85` (cảnh báo) · `ABSENCE_LIMIT_RATIO = 0.2` (quỹ vắng).
- `DEDUCTIONS` (điểm trừ): present 0 · late 0.5 · partial 0.5 · early_leave 1 · absent 1 · excused 0 *(hoặc theo cấu hình lớp `getAttendanceRules()`)*.
- `LABELS` (nhãn tiếng Việt 6 trạng thái).
- `GpsValidationService::FAKE_GPS_SUSPICION_THRESHOLD = 2` · `IP_GPS_MAX_DISTANCE_KM = 150` · `IMPLAUSIBLE_ACCURACY_METERS = 1.0`.

---

## 10. Quy tắc tổng kết 6 trạng thái (tham chiếu nhanh — 4 phiên)

| Chuỗi | Kết quả | Điểm trừ |
|---|---|---|
| `1111` | Có mặt (present) | 0 |
| `0111`, `0011` | Đi muộn (late) | −0.5 |
| `1101`, `1011`, `1010` | Vắng giữa giờ (partial) | −0.5 |
| `1110`, `1100` | Về sớm (early_leave) | −1 |
| `0000`, `0001`, `0101`, `0110`, `0010` | Vắng (absent) | −1 |
| (mọi phiên excused) | Có phép (excused) | 0 (hoặc −1 nếu lớp bật trừ) |

> Quy ước: `1` = có mặt, `0` = vắng. Điều kiện late/early_leave cần "có mặt ≥ vắng" (vd `0001`/`1000` quá ít → Vắng).

---

## 11. Bảng chống gian lận điểm danh QR (tham chiếu nhanh)

| Cơ chế | Nơi xử lý | Cột/giá trị | Hành vi |
|---|---|---|---|
| Xoay mã QR | `ClassSession::rotateQrToken()` | `qr_token`, `token_expires_at` | Ảnh chụp mã cũ hết hiệu lực ~1 nhịp làm mới. |
| Link chia sẻ ổn định | `ClassSession::booted()` | `share_token` | Không xoay, sống suốt lúc phiên mở. |
| Chống điểm danh 2 lần | `initializeRecord()` + đầu `checkIn()` | `attendance_records.status` | Đã present/late/excused → chặn, báo "đã điểm danh rồi". |
| Vé GPS 1 lần | `GpsValidationService` | `gps_verifications.is_used` | `verify` cấp `check_token`; `consumeCheckToken` đặt `is_used=true` → không dùng lại. |
| Trùng thiết bị | `checkIn()` (device_check) | `device_id` → `device_duplicate` | 1 máy điểm danh cho ≥2 SV/phiên → cờ đỏ + báo chủ lớp. |
| Máy điểm danh hộ | `escalateProxyDevice()` | `device_id` | 1 máy điểm danh ≥3 SV/lớp (xuyên buổi) → báo chủ lớp. |
| Ngoài bán kính | `checkIn()` | `out_of_radius` | VẪN có mặt + cờ vàng + số mét vượt + báo chủ lớp. |
| Nghi giả lập GPS/VPN | `computeFakeGpsScore()` | `fraud_score` → `suspected_mock` | Điểm ≥2 → cờ đỏ "Sai GPS" + ghi chú GV (yêu cầu SV tắt VPN). |
| Di chuyển bất khả thi | `detectImpossibleTravel()` | `impossible_travel` | >300km/h giữa 2 lần điểm danh → gắn cờ nghi vấn. |
| Nhật ký quét | `logCheckInScan()` | `check_in_scans` | Bản ghi bất biến để dựng lịch sử thiết bị xuyên phiên. |
