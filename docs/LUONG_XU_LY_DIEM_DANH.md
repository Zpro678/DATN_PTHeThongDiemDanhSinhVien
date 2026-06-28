# Luồng xử lý điểm danh — Tài liệu kỹ thuật

> Mô tả toàn bộ các file xử lý điểm danh, vai trò từng file, biến/hàm chính và LUỒNG ĐI dữ liệu.
> Đơn vị tính chuyên cần là **BUỔI**; một **buổi** gồm nhiều **phiên** (thủ công hoặc QR).

---

## 1. Mô hình dữ liệu (phân cấp)

```
CourseClass (lớp / học phần)               bảng: classes
  └── ClassMember (thành viên/sinh viên)   bảng: class_members
  └── ClassMeeting (BUỔI học)              bảng: class_meetings
        └── ClassSession (PHIÊN điểm danh) bảng: class_sessions   (có meeting_id, qr_token)
              └── AttendanceRecord         bảng: attendance_records (status từng SV/từng phiên)
        └── MeetingSummary (tổng kết buổi) bảng: meeting_summaries  (status + deduction mỗi SV/buổi)
```

- **Phiên thủ công**: `qr_token = NULL`. Giảng viên tự đánh dấu; chưa đánh dấu (`pending`) → **mặc định Có mặt**.
- **Phiên QR**: `qr_token != NULL`. Sinh viên quét mã/GPS; chưa quét (`pending`) → **coi như Vắng**.
- `attendance_records.status` ∈ `present | late | absent | excused | pending | invalid`.
- `meeting_summaries.status` (tổng kết) ∈ `present | late | partial | early_leave | absent | excused`.

---

## 2. Danh sách file & vai trò

### Lõi tính toán
| File | Vai trò |
|---|---|
| `app/Services/AttendanceCalculator.php` | **NGUỒN DUY NHẤT** tính điểm chuyên cần: quy tắc tổng kết 6 trạng thái, điểm trừ, % chuyên cần, đồng bộ `meeting_summaries`. |

### Models
| File | Vai trò |
|---|---|
| `app/Models/CourseClass.php` | Lớp học (`total_sessions`, `deduct_excused_absence`, GPS mặc định…). |
| `app/Models/ClassMeeting.php` | Buổi học. Có `endsAt()`, `isExpired()`, `canAddSession()`, `closeIfExpired()`, `createSession()`. |
| `app/Models/ClassSession.php` | Phiên điểm danh (thuộc 1 buổi qua `meeting_id`). |
| `app/Models/AttendanceRecord.php` | Trạng thái điểm danh của 1 SV trong 1 phiên. |
| `app/Models/MeetingSummary.php` | Kết quả tổng kết của 1 SV trong 1 buổi (status, deduction, is_overridden). |

### Livewire — Giảng viên (`app/Livewire/Lecturer/Attendance/`)
| File | Route | Vai trò |
|---|---|---|
| `AttendanceIndex.php` | `lecturer.attendance.index` | Dashboard điểm danh: liệt kê các BUỔI, tự chốt buổi hết giờ, thêm phiên. |
| `AttendanceCreate.php` | `lecturer.attendance.create` | **Tạo buổi** (chỉ nhập giờ kết thúc) + chọn phương thức (thủ công/QR). |
| `MeetingSessions.php` | `lecturer.attendance.meeting.sessions` | Danh sách các PHIÊN trong 1 buổi; thêm phiên; vào trang Tổng kết. |
| `ManualAttendanceSession.php` | `lecturer.attendance.manual.session` | Điểm danh **thủ công** 1 phiên (đánh dấu tạm → Lưu phiên). |
| `QrAttendanceCreate.php` | `lecturer.attendance.qr.create` | Cấu hình & tạo/thêm phiên **QR**. |
| `QrAttendanceSession.php` | `lecturer.attendance.qr.session` | **Trạm QR** realtime: phát mã, theo dõi SV check-in. |
| `MeetingSummary.php` | `lecturer.attendance.meeting.summary` | **Tổng kết buổi**: hiện trạng thái gộp, sửa tay, xuất Excel. |
| `app/Livewire/Lecturer/ClassAttendanceHistory.php` | `lecturer.classes.attendance` | **Lưới** SV × buổi của cả lớp. |

### Livewire — Sinh viên (`app/Livewire/Student/`)
| File | Route | Vai trò |
|---|---|---|
| `AttendanceCheckIn.php` | `attendance.check-in.guest` (`/attendance/check-in/{token}`) | Trang SV quét QR / xác minh GPS để điểm danh 1 phiên. |
| `AttendanceHistory.php` | `student.attendance.history` | Lịch sử điểm danh của SV. |
| `AttendanceStats.php` | `student.attendance.stats` | Thống kê chuyên cần của SV. |

### Controllers / Export / Command
| File | Vai trò |
|---|---|
| `app/Http/Controllers/GpsVerificationController.php` | Cấp & xác minh token GPS (`gps.token`, `gps.verify`) khi SV điểm danh QR có GPS. |
| `app/Exports/ClassSessionExport.php` | Xuất Excel 1 PHIÊN. |
| `app/Exports/MeetingSummaryExport.php` | Xuất Excel TỔNG KẾT 1 buổi. |
| `app/Console/Commands/CloseExpiredMeetings.php` | Lệnh nền `attendance:close-expired` tự chốt buổi quá giờ (chạy mỗi phút). |

### Services tiêu thụ AttendanceCalculator (thống kê/cảnh báo)
| File | Dùng để |
|---|---|
| `app/Services/LectureManageStudentService.php` | Bảng quản lý học viên (Có mặt/Muộn/Vắng/Phép, %, cấm thi). |
| `app/Services/StudentsService.php` | Cảnh báo & dashboard phía SV. |
| `app/Services/StatisticalService.php` | Thống kê theo môn/lớp. |
| `app/Services/DashboardStatisticService.php` | Số liệu tổng quan dashboard. |
| `app/Services/NotificationService.php` | Thông báo sắp vượt quỹ vắng. |

---

## 3. LUỒNG 1 — Tạo buổi điểm danh

```
GV mở "Tạo buổi điểm danh"  →  AttendanceCreate (route lecturer.attendance.create)
  mount(): date = hôm nay; meetingEndTime = now+90' (kẹp ≤ 23:55 nếu qua ngày)
  GV chỉ nhập GIỜ KẾT THÚC, chọn phương thức:
   ├─ "Điểm danh thủ công"  → createManualSession() → createBaseSession('active')
   │       ├─ validate: giờ kết thúc phải ≥ now + 10 phút
   │       ├─ tạo ClassMeeting (date=hôm nay, start=now, end=meetingEndTime, status=active)
   │       ├─ tạo ClassSession (status=active, qr_token=NULL)  ← phiên thủ công
   │       └─ tạo AttendanceRecord cho mọi SV active, status='present' (thủ công mặc định có mặt)
   │       → redirect ManualAttendanceSession
   └─ "Điểm danh QR"        → createQrSession() → createBaseSession('pending')
           ├─ tạo Meeting + Session(status=pending) + record 'pending'  ← phiên QR
           └─ step=3 (cấu hình QR) → setupQr() gắn qr_token/token_expires_at/GPS, status=active
           → redirect QrAttendanceSession
```

**Hàm chính:** `AttendanceCreate::createBaseSession($status)` — phân biệt thủ công/QR qua `$status` (`active`=thủ công→record `present`; `pending`=QR→record `pending`).

---

## 4. LUỒNG 2 — Điểm danh thủ công

```
ManualAttendanceSession (route lecturer.attendance.manual.session/{session})
  mount(): closeIfExpired() (chốt nếu buổi hết giờ) → initDrafts()
  initDrafts(): nạp record vào $draftStatuses/$draftNotes (TẠM, chưa ghi DB).
               Phiên thủ công: record 'pending' → hiển thị 'present' (mặc định có mặt).
  GV thao tác:
   ├─ setStatus(recordId, status)   → đổi $draftStatuses[recordId] (chỉ trong bộ nhớ)
   ├─ markAllPresent()              → mọi 'pending' → 'present' (tạm)
   └─ saveSession()                 → GHI $draftStatuses xuống attendance_records
                                       (set check_in_time cho present/late) → về MeetingSessions
  Khác: createDuplicateManualSession() (thêm phiên mới), exportExcel() (ClassSessionExport).
```

Lưu ý: **không chốt sổ** — phiên giữ `status='active'`, chuyên cần được gộp khi buổi kết thúc.

---

## 5. LUỒNG 3 — Điểm danh QR

```
(A) Giảng viên — QrAttendanceSession (route lecturer.attendance.qr.session/{session})
     - Phát mã QR động (qr_token + token_expires_at, làm mới theo qr_refresh_rate).
     - Theo dõi realtime SV check-in; có thể chốt phiên.

(B) Sinh viên — AttendanceCheckIn (route /attendance/check-in/{token})
     - SV mở link/quét QR → nhập MSSV (nếu khách) → (tuỳ chọn) xác minh GPS:
         GpsVerificationController::issueToken() → verify()  (Haversine so bán kính lớp)
     - Hợp lệ → set AttendanceRecord.status = 'present' / 'late' (theo giờ), ghi CheckInScan.
     - Không quét → record giữ 'pending' → khi tổng kết QR coi như VẮNG.
```

---

## 6. LUỒNG 4 — Tự động chốt buổi (hết giờ)

```
ClassMeeting::endsAt()      = date + end_time
ClassMeeting::isExpired()   = now > endsAt()
ClassMeeting::canAddSession()= ! isExpired()        ← hết giờ thì KHÔNG thêm phiên
ClassMeeting::closeIfExpired()= nếu hết giờ → set buổi + mọi phiên status='closed'

Gọi LAZY khi truy cập: AttendanceIndex (render), MeetingSessions/MeetingSummary (mount),
                       Manual/QrAttendanceSession (mount).
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
- `DEDUCTIONS` (điểm trừ — **TẠM**, sẽ chuyển vào cấu hình lớp): present 0 · late 0.5 · partial 0.5 · early_leave 1 · absent 1 · excused 0.
- `LABELS` (nhãn tiếng Việt 6 trạng thái).

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
