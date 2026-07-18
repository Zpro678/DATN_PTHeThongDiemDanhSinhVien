# Giải thích Cấu trúc Cơ sở dữ liệu (Database Schema)

Tài liệu này giải thích chi tiết về **TẤT CẢ** các bảng (tables) và **TẤT CẢ** các thuộc tính (attributes) trong cơ sở dữ liệu của Hệ thống Điểm danh, bao gồm cả các bảng hệ thống và các trường thời gian tự động.

---

## 1. Người dùng & Xác thực (Users & Authentication)

### Bảng `users`
Lưu trữ thông tin tài khoản của tất cả người dùng trong hệ thống (Sinh viên, Giảng viên, Quản trị viên).
*   `id`: `bigint [pk, increment]` - Khóa chính tự tăng, định danh duy nhất của người dùng.
*   `role`: `varchar(50)` - Vai trò của người dùng trong hệ thống. Nhận các giá trị chuỗi (Enum `user_role`): 'USER' (Người dùng bình thường), 'ADMIN' (Quản trị viên), 'SUPER_ADMIN' (Quản trị viên tối cao). Mặc định là 'USER'.
*   `google_id`: `varchar [null, unique]` - ID duy nhất trả về từ Google khi người dùng đăng nhập bằng Google OAuth.
*   `name`: `varchar` - Họ và tên hiển thị của người dùng.
*   `email`: `varchar [unique, not null]` - Địa chỉ email duy nhất, dùng để đăng nhập.
*   `email_verified_at`: `timestamp [null]` - Thời điểm người dùng đã xác thực email qua link gửi tới mail.
*   `password`: `varchar [null]` - Mật khẩu đã được mã hóa (băm). Có thể null nếu người dùng chỉ đăng nhập bằng Google.
*   `avatar`: `varchar [null]` - Đường dẫn URL hoặc tên file ảnh đại diện.
*   `status`: `varchar(50)` - Trạng thái của tài khoản, mặc định là 'active'. (Có thể là 'banned', 'inactive').
*   `remember_token`: `varchar [null]` - Mã token tự sinh ra khi người dùng chọn "Ghi nhớ đăng nhập" (Remember me).
*   `created_at`: `timestamp` - Thời điểm bản ghi được tạo (Tài khoản được đăng ký).
*   `updated_at`: `timestamp` - Thời điểm bản ghi được cập nhật lần cuối.
*   `deleted_at`: `timestamp [null]` - Thời điểm tài khoản bị xóa mềm (Soft Delete). Nếu có giá trị, hệ thống coi như tài khoản đã bị xóa.

### Bảng `password_reset_tokens`
Lưu trữ mã token tạm thời khi người dùng yêu cầu khôi phục mật khẩu (Quên mật khẩu).
*   `email`: `varchar [pk]` - Khóa chính, email của người dùng yêu cầu khôi phục mật khẩu.
*   `token`: `varchar [not null]` - Mã chuỗi ký tự ngẫu nhiên dùng để xác thực quyền đổi mật khẩu.
*   `created_at`: `timestamp [null]` - Thời điểm tạo ra yêu cầu đặt lại mật khẩu. Dùng để tính toán thời gian hết hạn của token.

### Bảng `sessions`
Bảng quản lý phiên làm việc của người dùng (Session).
*   `id`: `varchar [pk]` - Khóa chính, mã định danh duy nhất của phiên (Session ID).
*   `user_id`: `bigint [null]` - ID của người dùng sở hữu phiên này (Tham chiếu bảng users, null nếu là phiên của khách vãng lai).
*   `ip_address`: `varchar(45) [null]` - Địa chỉ IP của thiết bị tạo phiên.
*   `user_agent`: `text [null]` - Chuỗi thông tin về Trình duyệt và Hệ điều hành (User Agent) đang sử dụng.
*   `payload`: `longtext [not null]` - Dữ liệu nội dung của phiên được mã hóa hoặc serialize.
*   `last_activity`: `int [not null]` - Cột mốc thời gian dạng UNIX timestamp chỉ ra lần cuối cùng phiên này có tương tác với hệ thống.

---

## 2. Lớp học & Đăng ký (Classes & Enrollment)

### Bảng `classes`
Lưu trữ thông tin chi tiết về các Lớp học/Môn học.
*   `id`: `uuid [pk]` - Khóa chính, sử dụng chuẩn UUID (Chuỗi mã định danh phổ quát) để khó đoán định.
*   `owner_user_id`: `bigint [not null]` - ID của người tạo/sở hữu chính của lớp (thường là Giảng viên). (Khóa ngoại tham chiếu `users.id`, cấm xóa user nếu còn lớp - `restrictOnDelete`).
*   `join_key`: `varchar(50) [unique, not null]` - Một đoạn mã ngắn, duy nhất sinh ra để sinh viên nhập vào và xin tham gia lớp.
*   `class_code`: `varchar(50) [null]` - Mã lớp do giảng viên tự định nghĩa (VD: INT3306 1, MAT1092).
*   `name`: `varchar [not null]` - Tên đầy đủ của môn học / lớp học (VD: Nhập môn Công nghệ Phần mềm).
*   `subject_code`: `varchar(50) [null]` - Mã số môn học của nhà trường.
*   `semester`: `varchar(50) [null]` - Tên hoặc mã của Học kỳ (VD: HK1 2024-2025).
*   `description`: `text [null]` - Thông tin mô tả chi tiết, nội quy, ghi chú về lớp học.
*   `deduct_excused_absence`: `boolean [not null, default: false]` - Cờ cấu hình: Nếu sinh viên vắng mặt có phép, có bị trừ điểm chuyên cần hay không (true/false).
*   `require_approval`: `boolean [not null, default: false]` - Cờ cấu hình: Khi sinh viên nhập `join_key` để vào lớp, giảng viên có cần phải duyệt tay hay không (true = phải duyệt, false = tự động vào).
*   `status`: `varchar(50) [not null, default: 'active']` - Trạng thái của lớp học (VD: 'active' - Đang diễn ra, 'archived' - Đã lưu trữ/Kết thúc).
*   `total_sessions`: `int [not null, default: 15]` - Tổng số buổi học hoặc phiên điểm danh dự kiến của môn này, dùng để tính toán % chuyên cần. Mặc định 15 buổi.
*   `absence_limit_percent`: `decimal(5,2) [not null, default: 20]` - Quỹ vắng cho phép, tính theo % tổng số buổi. Ngưỡng cấm thi KHÔNG có cột riêng mà luôn suy ra = `100 - absence_limit_percent`, nên hai con số không thể mâu thuẫn.
*   `warning_margin_percent`: `decimal(5,2) [not null, default: 5]` - Cảnh báo chuyên cần sớm hơn ngưỡng cấm thi bao nhiêu %. Mặc định 5 (cấm thi 80% → cảnh báo dưới 85%).
*   `near_absence_sessions`: `smallint [not null, default: 2]` - Khi quỹ vắng chỉ còn từng này buổi trở xuống thì gửi thông báo "sắp vượt ngưỡng vắng".
*   `created_at`: `timestamp` - Thời gian tạo lớp.
*   `updated_at`: `timestamp` - Thời gian cập nhật thông tin lớp lần cuối.
*   `deleted_at`: `timestamp [null]` - Thời gian xóa mềm lớp học.

### Bảng `class_owners`
Lưu trữ danh sách các giảng viên đồng quản lý (Trợ giảng, Giảng viên dạy cùng) cho một lớp học.
*   `id`: `bigint [pk, increment]` - Khóa chính tự tăng.
*   `class_id`: `uuid [not null]` - ID lớp học (Khóa ngoại tham chiếu `classes.id`).
*   `user_id`: `bigint [not null]` - ID của người dùng được thêm quyền (Khóa ngoại tham chiếu `users.id`).
*   `role`: `varchar(50) [not null, default: 'co_owner']` - Phân quyền trong lớp (VD: 'co_owner' - Đồng chủ sở hữu).
*   `invited_by`: `bigint [null]` - ID của người đã gửi lời mời này (Khóa ngoại tham chiếu `users.id`).
*   `accepted_at`: `timestamp [null]` - Thời điểm người dùng chấp nhận lời mời tham gia quản lý lớp.
*   `created_at`: `timestamp` - Thời điểm gửi lời mời/tạo bản ghi.
*   `updated_at`: `timestamp` - Thời điểm cập nhật bản ghi.

### Bảng `class_members`
Lưu trữ trạng thái thành viên tham gia lớp học. Sinh viên có thể đã tham gia hoặc bị đuổi.
*   `id`: `bigint [pk, increment]` - Khóa chính tự tăng.
*   `class_id`: `uuid [not null]` - ID lớp học (Khóa ngoại tham chiếu `classes.id`, xóa lớp sẽ xóa thành viên - `cascadeOnDelete`).
*   `user_id`: `bigint [null]` - ID tài khoản hệ thống của sinh viên. Có thể null (Late binding) nếu giảng viên import danh sách từ Excel mà sinh viên đó chưa tạo tài khoản. (Tham chiếu `users.id`, `nullOnDelete`).
*   `status`: `varchar` (Enum `member_status`) - Trạng thái thành viên: 'ACTIVE' (Đang học), 'LEFT' (Tự rời lớp), 'REMOVED' (Bị giảng viên đuổi ra khỏi lớp). Mặc định là 'ACTIVE'.
*   `status_changed_at`: `timestamp [null]` - Thời điểm bị thay đổi trạng thái (Lúc bị đuổi hoặc lúc tự thoát).
*   `created_at`: `timestamp` - Thời gian sinh viên bắt đầu được đưa vào lớp.
*   `updated_at`: `timestamp` - Thời gian cập nhật bản ghi.
*   `deleted_at`: `timestamp [null]` - Thời gian xóa mềm bản ghi tham gia lớp.

### Bảng `class_member_profiles`
Lưu trữ hồ sơ chi tiết của thành viên trong lớp (độc lập với bảng `users`). Thông tin này có thể đến từ file Excel do giảng viên cung cấp, đóng vai trò như "Danh sách lớp chính thức".
*   `id`: `bigint [pk, increment]` - Khóa chính tự tăng.
*   `class_member_id`: `bigint [unique, not null]` - Khóa ngoại 1-1 với bảng `class_members.id`. (Xóa member thì xóa profile - `cascadeOnDelete`).
*   `student_code`: `varchar(50) [null]` - Mã sinh viên thực tế trên danh sách lớp.
*   `full_name`: `varchar [null]` - Họ và tên đầy đủ hiển thị theo danh sách trường.
*   `email`: `varchar [null]` - Email liên hệ.
*   `created_at`: `timestamp` - Thời gian tạo hồ sơ.
*   `updated_at`: `timestamp` - Thời gian cập nhật hồ sơ.

### Bảng `class_join_requests`
Nếu lớp bật cờ `require_approval = true`, khi sinh viên xin vào lớp, yêu cầu sẽ nằm ở bảng này đợi duyệt.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_id`: `uuid [not null]` - Lớp học xin tham gia.
*   `user_id`: `bigint [not null]` - Sinh viên xin tham gia.
*   `status`: `varchar` (Enum `request_status`) - 'PENDING' (Chờ duyệt), 'APPROVED' (Đã duyệt - sẽ tạo bản ghi sang `class_members`), 'REJECTED' (Bị từ chối). Mặc định 'PENDING'.
*   `created_at`: `timestamp` - Thời gian gửi yêu cầu.
*   `updated_at`: `timestamp` - Thời gian duyệt/từ chối/cập nhật.

---

## 3. Cốt lõi Điểm danh (Attendance Core)

### Bảng `class_meetings`
Đại diện cho 1 "Buổi học" cố định trong lịch trình (Ví dụ: Buổi sáng ngày 20/10).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_id`: `uuid [not null]` - Lớp học (Khóa ngoại tham chiếu `classes.id`).
*   `user_Created`: `bigint [not null]` - ID giảng viên tạo buổi này.
*   `name`: `varchar [not null]` - Tên hiển thị buổi học (Ví dụ: "Buổi 1: Giới thiệu chung", "Buổi thực hành số 3").
*   `date`: `date [not null]` - Ngày học (Định dạng YYYY-MM-DD).
*   `start_time`: `time [null]` - Giờ bắt đầu học.
*   `end_time`: `time [null]` - Giờ kết thúc học.
*   `status`: `varchar(50) [not null, default: 'active']` - Trạng thái buổi học: 'active' (Hoạt động) hoặc 'closed' (Đã khóa sổ/Hủy).
*   `created_at`: `timestamp` - Tạo lúc.
*   `updated_at`: `timestamp` - Cập nhật lúc.
*   `deleted_at`: `timestamp [null]` - Xóa mềm.

### Bảng `class_sessions`
Đại diện cho 1 "Phiên điểm danh" trong 1 Buổi học (Ví dụ: Một buổi học 4 tiếng có thể có 2 phiên điểm danh ở đầu giờ và cuối giờ).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `meeting_id`: `bigint [null]` - Thuộc buổi học nào (Khóa ngoại tham chiếu `class_meetings.id`, xóa buổi thì xóa các phiên này - `cascadeOnDelete`).
*   `class_id`: `uuid [not null]` - ID lớp học.
*   `created_by`: `bigint [not null]` - Người mở phiên điểm danh.
*   `name`: `varchar [not null]` - Tên phiên (VD: Điểm danh đầu giờ).
*   `date`: `date [not null]` - Ngày điểm danh.
*   `start_time`, `end_time`: `time [null]` - Khung giờ diễn ra phiên (nếu có).
*   `qr_token`: `varchar [unique, null]` - Mã ký tự bí mật, chuỗi seed để sinh ra mã QR Code động hiện trên máy chiếu.
*   `token_expires_at`: `timestamp [null]` - Thời điểm mã QR hiện tại bị vô hiệu lực, yêu cầu tạo mã QR mới.
*   `qr_refresh_rate`: `int [not null, default: 10]` - Khoảng thời gian (giây) mã QR sẽ tự động xoay đổi 1 lần (Chống sinh viên chụp ảnh QR gửi cho bạn ở nhà). Mặc định 10s.
*   `gps_latitude`, `gps_longitude`: `decimal(10,8), decimal(11,8) [null]` - Tọa độ (Vĩ độ, Kinh độ) chính xác của phòng học mà giảng viên đứng.
*   `gps_radius`: `int [null]` - Bán kính hợp lệ (tính bằng mét). Sinh viên phải đứng trong bán kính này mới được tính.
*   `require_device_check`: `boolean [not null, default: false]` - Bật chế độ quét vân tay thiết bị để cấm 1 máy điểm danh hộ nhiều lần.
*   `status`: `varchar(50) [not null, default: 'pending']` - Trạng thái của phiên (VD: 'pending', 'active', 'closed').
*   `created_at`, `updated_at`, `deleted_at`: `timestamp` - Thời gian tạo, cập nhật, xóa mềm.

### Bảng `attendance_records`
Bảng ghi nhận từng lần check-in cụ thể của sinh viên tại một phiên điểm danh.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_session_id`: `bigint [not null]` - Check-in trong phiên nào.
*   `class_member_id`: `bigint [not null]` - Sinh viên nào check-in.
*   `status`: `varchar(50) [not null, default: 'pending']` - Trạng thái kết quả của bản ghi này: 'pending' (Chờ duyệt/chưa có), 'present' (Có mặt), 'late' (Đi muộn), 'absent' (Vắng mặt), 'excused' (Có phép), 'invalid' (Dữ liệu không hợp lệ).
*   `is_account`: `boolean [not null, default: false]` - Đánh dấu xem bản ghi này do Sinh viên đăng nhập tự quét (true) hay do điền Form khách (false).
*   `check_in_time`: `timestamp [null]` - Dấu thời gian chính xác lúc sinh viên thực hiện quét QR hoặc submit.
*   `ip_address`: `varchar(45) [null]` - Địa chỉ IP mạng lúc sinh viên quét.
*   `device_id`: `varchar [null]` - ID nhận diện định danh phần cứng (Nếu dùng app).
*   `device_fingerprint`: `varchar [null]` - Chuỗi hash vân tay trình duyệt sinh ra để xác định tính duy nhất của trình duyệt.
*   `distance_meters`: `decimal(8,2) [null]` - Khoảng cách bằng số mét từ điện thoại SV tới vị trí gốc của giảng viên lúc quét.
*   `gps_accuracy_meters`: `decimal(8,2) [null]` - Độ sai số của GPS báo về từ điện thoại (để biết định vị có bị nhiễu không).
*   `gps_latitude_recorded`, `gps_longitude_recorded`: `decimal(10,8), decimal(11,8) [null]` - Tọa độ thu được từ điện thoại sinh viên.
*   `gps_fraud_flag`: `varchar [null]` - Cờ lưu trữ lý do nếu hệ thống nghi ngờ gian lận (VD: 'FAKE_GPS_DETECTED', 'TOO_FAST').
*   `note`: `text [null]` - Ghi chú thêm do Giảng viên viết khi sửa kết quả.
*   `created_at`, `updated_at`, `deleted_at`: `timestamp` - Thời gian vòng đời của bản ghi.

### Bảng `gps_verifications`
Lưu trữ các vé cấp phép vị trí GPS dùng một lần. Khi quét mã QR, máy SV sẽ xin lấy vị trí và gửi lên lấy 1 token ở đây để chống phát lại.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `session_id`: `bigint [not null]` - Thuộc phiên điểm danh nào.
*   `member_id`: `bigint [not null]` - Cấp cho sinh viên nào.
*   `token`: `varchar(64) [unique, not null]` - Token sinh ra ban đầu.
*   `check_token`: `varchar(64) [unique, null]` - Token thứ 2 để đối chiếu vòng lặp bảo mật.
*   `ip_address`: `varchar(45) [not null]` - IP khi xin vé.
*   `lat`, `lng`: `decimal(10,8), decimal(11,8) [null]` - Tọa độ khi xin vé.
*   `accuracy`: `decimal(8,2) [null]` - Độ chính xác GPS.
*   `fraud_score`: `decimal(5,2) [not null, default: 0]` - Điểm nghi vấn (Càng cao càng dễ là fake GPS).
*   `is_used`: `boolean [not null, default: false]` - Vé đã được đem ra dùng để chốt điểm danh chưa (Chỉ dùng được 1 lần).
*   `expires_at`: `timestamp [not null]` - Hạn chót vé vị trí này phải dùng, quá thời gian phải lấy vị trí lại.
*   `created_at`, `updated_at`: `timestamp`

### Bảng `meeting_summaries`
Vì 1 Buổi học (`meeting`) có thể có nhiều Phiên điểm danh (`session`), bảng này sẽ tổng kết lại kết quả cuối cùng cho Buổi học đó.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `meeting_id`: `bigint [not null]` - Thuộc Buổi học nào.
*   `class_member_id`: `bigint [not null]` - Dành cho sinh viên nào.
*   `status`: `varchar(50) [not null, default: 'absent']` - Trạng thái tổng kết của cả buổi: 'present', 'late', 'absent', 'excused'.
*   `deduction`: `decimal(3,1) [not null, default: 0]` - Điểm chuyên cần bị trừ cho buổi này (VD: Vắng trừ 1 điểm, Trễ trừ 0.5 điểm).
*   `auto_status`: `varchar(50) [null]` - Trạng thái mà hệ thống tự động suy luận ra dựa trên các bản ghi, dùng để đối chiếu.
*   `is_overridden`: `boolean [not null, default: false]` - Cờ báo hiệu giảng viên đã sửa tay kết quả của buổi này, không còn phụ thuộc vào hệ thống tự tính nữa.
*   `is_notified`: `boolean [not null, default: false]` - Cờ báo hiệu đã gửi cảnh báo cho sinh viên nếu bị vắng/trễ chưa.
*   `note`: `text [null]` - Ghi chú cho buổi học.
*   `created_at`, `updated_at`: `timestamp`

### Bảng `attendance_summaries`
Tổng kết điểm chuyên cần của một Sinh viên cho TOÀN BỘ 1 Lớp học (dùng cho thống kê, cấp quyền thi cuối kỳ).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_id`: `uuid [not null]` - Thuộc lớp học nào.
*   `class_member_id`: `bigint [not null]` - Của sinh viên nào.
*   `total_present`: `int [not null, default: 0]` - Tổng số buổi Có mặt.
*   `total_late`: `int [not null, default: 0]` - Tổng số buổi Đi muộn.
*   `total_absent`: `int [not null, default: 0]` - Tổng số buổi Vắng không phép.
*   `total_excused`: `int [not null, default: 0]` - Tổng số buổi Vắng có phép.
*   `is_banned_from_exam`: `boolean [not null, default: false]` - Cờ đánh dấu tự động xem sinh viên có bị CẤM THI hay không (Nếu số buổi vắng vượt ngưỡng quy định).
*   `updated_at`: `timestamp [null]` - Thời điểm cập nhật bảng tóm tắt này lần cuối.

---

## 4. Kiểm toán & Lịch sử Quét (Audit & Scan)

### Bảng `audit_logs`
Sổ ghi chép không thể thay đổi (Immutable) về mọi hành động nhạy cảm trong hệ thống. Dành cho việc điều tra đối soát (VD: Ai đổi điểm danh từ Vắng sang Có mặt).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `user_id`: `bigint [null]` - Người dùng thực hiện thao tác đó.
*   `class_id`: `uuid [null]` - Thao tác này xảy ra ở lớp học nào.
*   `action`: `varchar [not null]` - Tên hành động (VD: 'USER_UPDATED_ATTENDANCE').
*   `table_name`: `varchar [null]` - Bảng trong CSDL bị ảnh hưởng (VD: 'attendance_records').
*   `row_id`: `bigint [null]` - Khóa chính của dòng dữ liệu bị ảnh hưởng.
*   `old_values`: `json [null]` - Một object JSON lưu trữ tình trạng dữ liệu TRƯỚC khi thay đổi.
*   `new_values`: `json [null]` - Một object JSON lưu trữ tình trạng dữ liệu SAU khi thay đổi.
*   `ip_address`: `varchar(45) [null]` - IP của người thực hiện.
*   `user_agent`: `text [null]` - Trình duyệt/Thiết bị của người thực hiện.
*   `created_at`: `timestamp [not null]` - Thời gian thực hiện (Cố định, không có deleted/updated_at).

### Bảng `check_in_scans`
Ghi lại TOÀN BỘ mọi lần tương tác gửi yêu cầu điểm danh của bất kỳ ai lên server, kể cả khi quét thành công hay thất bại.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_session_id`: `bigint [not null]` - Quét trong phiên nào.
*   `user_id`: `bigint [null]` - ID tài khoản của người quét (Nếu đã đăng nhập).
*   `student_code_attempt`: `varchar(50) [null]` - MSSV do người dùng tự nhập tay vào nếu họ chưa có tài khoản hệ thống.
*   `scan_type`: `varchar(50) [not null]` - Hình thức điểm danh: 'qr' (Quét mã), 'gps' (Bấm nút gửi vị trí), 'link' (Bấm link gửi qua nhóm).
*   `payload_signature`: `varchar [not null]` - Chữ ký HMAC của gói tin. Đảm bảo gói tin QR đó không bị copy dán gửi lại (Chống Replay Attack).
*   `is_valid`: `boolean [not null, default: true]` - Đánh dấu xem lần quét này server có chấp nhận hay từ chối.
*   `fail_reason`: `varchar(100) [null]` - Lý do tại sao bị từ chối (VD: 'timeout' - Hết hạn mã QR, 'invalid_signature' - Mã giả, 'out_of_range' - Ra khỏi vùng GPS, 'not_enrolled' - Sinh viên không có trong lớp).
*   `ip_address`, `device_id`, `device_fingerprint`: `varchar` - Thông tin dấu vết máy khách gửi lên.
*   `scanned_at`: `timestamp [not null]` - Thời điểm gửi log lên (Dùng thay created_at).

### Bảng `import_errors`
Lưu trữ danh sách lỗi xuất hiện trong tiến trình Upload và Đọc file Excel danh sách sinh viên.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `import_token`: `varchar(100) [not null]` - Mã ID của phiên import đó.
*   `row_index`: `int [not null]` - Dòng thứ mấy trong file Excel bị lỗi.
*   `error_message`: `text [not null]` - Lời giải thích lỗi cụ thể (VD: "Trùng MSSV", "Cột tên bỏ trống").
*   `created_at`, `updated_at`: `timestamp`

---

## 5. Kinh doanh & Thanh toán (Monetization - SaaS)

### Bảng `coupons`
Lưu trữ các mã khuyến mãi, giảm giá khi người dùng mua các Gói cao cấp.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `code`: `varchar(50) [unique, not null]` - Ký tự mã giảm giá (VD: TET2024).
*   `applicable_plan_id`: `bigint [null]` - Áp dụng riêng cho Gói dịch vụ nào (Nếu null là áp dụng mọi gói).
*   `type`: `varchar` (Enum `discount_type`) - Loại giảm giá: 'PERCENT' (Giảm theo %), 'FIXED' (Giảm trừ thẳng số tiền).
*   `value`: `decimal(10,2) [not null]` - Giá trị giảm giá (Ví dụ: 20 -> 20%, hoặc 50000 -> 50.000 VNĐ).
*   `usage_limit`: `int [null]` - Số lần sử dụng tối đa của mã này trên toàn hệ thống (VD: Chỉ 100 người nhanh tay).
*   `used_count`: `int [not null, default: 0]` - Số lần mã này đã được sử dụng.
*   `valid_from`, `valid_until`: `timestamp [null]` - Khung thời gian mã có hiệu lực.
*   `is_active`: `boolean [not null, default: true]` - Mã có đang được bật hay tạm khóa không.
*   `created_at`: `timestamp`

### Bảng `plans`
Danh mục các "Gói đăng ký" mà hệ thống cung cấp cho giảng viên/nhà trường.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `plan_tier`: `varchar` (Enum `plan_tier_enum`) - Cấp bậc gói: 'FREE' (Miễn phí), 'PRO' (Chuyên nghiệp), 'ENTERPRISE' (Dành cho cơ sở lớn).
*   `name`: `varchar [not null]` - Tên gói hiển thị ra ngoài (VD: Gói Cơ Bản 1 Tháng).
*   `description`: `text [null]` - Mô tả chi tiết tính năng gói.
*   `price`: `decimal(10,2) [not null]` - Giá gốc của gói đăng ký này.
*   `duration_days`: `int [not null, default: 30]` - Thời hạn hiệu lực của gói (Ví dụ: 30 ngày, 365 ngày).
*   `is_active`: `boolean [not null, default: true]` - Gói này có đang mở bán không.
*   `created_at`, `updated_at`, `deleted_at`: `timestamp`

### Bảng `plan_configs`
Cấu hình chi tiết các Giới hạn TÀI NGUYÊN đi kèm với từng Gói tương ứng.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `plan_id`: `bigint [unique, not null]` - Khóa ngoại 1-1 nối với bảng `plans.id`.
*   `max_classes`: `int [not null]` - Số lớp học TỐI ĐA người mua gói được phép tạo.
*   `max_students_per_class`: `int [not null, default: 50]` - Số lượng sinh viên TỐI ĐA được thêm vào mỗi lớp.
*   `max_gps_radius`: `int [not null, default: 100]` - Bán kính GPS LỚN NHẤT cho phép mở rộng (mét).
*   `can_export_excel`: `boolean [not null, default: false]` - Có cho phép tải file Báo cáo Điểm danh dạng Excel không (Gói PRO trở lên mới có).

### Bảng `subscriptions`
Bản ghi chi tiết các Gói Đăng Ký đang được sử dụng của người dùng.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `user_id`: `bigint [not null]` - Người dùng nào mua.
*   `plan_id`: `bigint [not null]` - Đang dùng Gói nào hiện tại.
*   `paid_plan_id`: `bigint [null]` - Gói thực sự đã thanh toán (để phân biệt với gói FREE hay trial).
*   `start_date`: `timestamp [not null]` - Thời gian bắt đầu hiệu lực gói.
*   `end_date`: `timestamp [null]` - Thời gian hết hạn gói. Hết hạn sẽ rớt về gói FREE.
*   `status`: `varchar(50) [not null, default: 'active']` - Trạng thái: 'active' (Đang dùng), 'expired' (Đã hết hạn), 'cancelled' (Đã hủy).
*   `created_at`, `updated_at`: `timestamp`

### Bảng `transactions`
Lịch sử Giao dịch thanh toán tiền của người dùng thông qua Cổng thanh toán (VNPay, Momo...).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `user_id`: `bigint [not null]` - Ai là người thanh toán.
*   `plan_id`: `bigint [null]` - Mua gói dịch vụ nào.
*   `amount`: `decimal(10,2) [not null]` - Số tiền thực tế phải trả.
*   `currency`: `varchar(10) [not null, default: 'VND']` - Đơn vị tiền tệ.
*   `payment_method`: `varchar(50) [not null]` - Hình thức thanh toán (VD: 'VNPAY', 'MOMO', 'BANK_TRANSFER').
*   `transaction_code`: `varchar(100) [unique, not null]` - Mã giao dịch nội bộ sinh ra từ hệ thống của ta để gọi sang cổng.
*   `reference_code`: `varchar(100) [unique, null]` - Mã đơn hàng gốc nếu thanh toán bổ sung cho đơn hàng khác.
*   `gateway_transaction_id`: `varchar(100) [null]` - Mã giao dịch do BÊN ĐỐI TÁC CỔNG THANH TOÁN (VNPAY) trả về.
*   `status`: `varchar(50) [not null, default: 'PENDING']` - Trạng thái: 'PENDING' (Chờ thanh toán), 'SUCCESS' (Thành công), 'FAILED' (Thất bại), 'CANCELLED' (Người dùng ấn hủy đơn).
*   `payment_url`: `text [null]` - Link chuyển hướng người dùng sang trang của VNPay/Momo để trả tiền.
*   `payment_response`: `json [null]` - Lưu lại toàn bộ cục dữ liệu JSON phản hồi về từ cổng thanh toán để đối soát sau này.
*   `failure_reason`: `text [null]` - Mã hoặc lý do chi tiết nếu giao dịch thất bại.
*   `paid_at`: `timestamp [null]` - Thời điểm thực tế tiền báo đã vào tài khoản thành công.
*   `expired_at`: `timestamp [null]` - Thời điểm đường link/giao dịch này sẽ bị hủy bỏ không cho trả nữa (VD: 15 phút sau khi tạo).
*   `created_at`, `updated_at`: `timestamp`

---

## 6. Các Bảng Tính Năng Hệ Thống (System Features)

### Bảng `user_devices`
Lưu thông tin về thiết bị (Điện thoại/Trình duyệt) của người dùng để phục vụ đẩy Thông báo thời gian thực (Push Notification).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `user_id`: `bigint [not null]` - Của người dùng nào.
*   `fcm_token`: `varchar [unique, not null]` - Firebase Cloud Messaging Token, là chìa khóa duy nhất để Server biết đẩy thông báo vào đúng chiếc điện thoại này.
*   `device_name`: `varchar [null]` - Tên thiết bị do điện thoại gửi lên (VD: "iPhone 13 Pro Max của Tuấn").
*   `last_active_at`: `timestamp [null]` - Thời điểm cuối cùng mở app (để xóa token chết).
*   `created_at`: `timestamp [null]` - Thời điểm tạo token thiết bị.

### Bảng `notifications`
Bảng lưu trữ Lịch sử các Thông báo đã được tạo và gửi đi trong hệ thống.
*   `id`: `uuid [pk]` - Khóa chính UUID.
*   `type`: `varchar [not null]` - Tên Class xử lý loại thông báo này trong code.
*   `notifiable_type`: `varchar [not null]` - Gửi đến Model nào (VD: `App\Models\User`).
*   `notifiable_id`: `bigint [not null]` - ID của người sẽ nhận thông báo.
*   `data`: `json [not null]` - Dữ liệu nội dung thông báo (Tiêu đề, Lời nhắn, URL chuyển hướng).
*   `read_at`: `timestamp [null]` - Thời gian người dùng click bấm "Đã đọc". Nếu null nghĩa là thông báo chưa đọc.
*   `created_at`, `updated_at`: `timestamp`

### Bảng `leave_requests`
Bảng quản lý quy trình gửi Đơn xin nghỉ phép của Sinh viên.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `class_member_id`: `bigint [not null]` - Đơn của sinh viên nào (trong lớp nào).
*   `class_meeting_id`: `bigint [not null]` - Đơn xin nghỉ ở Buổi học nào.
*   `reason`: `text [not null]` - Lời văn lý do xin nghỉ.
*   `proof_image`: `text [null]` - Đường dẫn tải về Ảnh chụp minh chứng (Giấy khám bệnh, Đơn đoàn thanh niên).
*   `status`: `varchar(50) [not null, default: 'pending']` - Trạng thái duyệt đơn: 'pending' (Chờ duyệt), 'approved' (Đồng ý cho nghỉ), 'rejected' (Bác bỏ).
*   `rejected_reason`: `text [null]` - Lời nhắn từ giảng viên giải thích lý do vì sao không duyệt đơn nghỉ.
*   `reviewed_by`: `bigint [null]` - ID của Giảng viên đã duyệt/từ chối cái đơn này.
*   `reviewed_at`: `timestamp [null]` - Thời điểm đơn được duyệt xong.
*   `created_at`, `deleted_at`: `timestamp` - Thời gian tạo đơn.

### Bảng `system_feedbacks`
Bảng lưu trữ các phản hồi, báo lỗi, hoặc góp ý tính năng từ phía người dùng gửi cho Admin.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `user_id`: `bigint [not null]` - ID của người dùng (Sinh viên/Giảng viên) gửi phản hồi.
*   `type`: `varchar(50) [not null, default: 'other']` - Loại phản hồi ('bug', 'feature', 'other').
*   `title`: `varchar [not null]` - Tiêu đề phản hồi.
*   `content`: `text [not null]` - Nội dung chi tiết của phản hồi.
*   `attachment_path`: `varchar [null]` - Đường dẫn file ảnh đính kèm minh họa (nếu có).
*   `status`: `varchar(50) [not null, default: 'pending']` - Trạng thái: 'pending' (Chờ xử lý), 'in_progress' (Đang xử lý), 'resolved' (Đã giải quyết).
*   `admin_reply`: `text [null]` - Lời phản hồi/trả lời từ Admin.
*   `replied_by`: `bigint [null]` - ID của Admin đã trả lời phản hồi này.
*   `replied_at`: `timestamp [null]` - Thời điểm Admin trả lời.
*   `created_at`, `updated_at`: `timestamp` - Thời gian tạo và cập nhật.

---

## 7. Các Bảng Hạ Tầng Khung Làm Việc (Infrastructure - Laravel Framework)

Đây là các bảng mà Hệ thống Code (Laravel) tự động tạo ra để quản lý nội bộ các tác vụ nền, không liên quan trực tiếp đến nghiệp vụ Điểm danh.

### Bảng `cache`
Bộ nhớ đệm tạm thời để tăng tốc ứng dụng.
*   `key`: `varchar [pk]` - Khóa tên biến đệm.
*   `value`: `mediumtext [not null]` - Giá trị dữ liệu đệm.
*   `expiration`: `int [not null]` - Thời gian hết hạn của bộ nhớ (Timestamp).

### Bảng `cache_locks`
Khóa chống xung đột đa luồng khi xử lý đồng thời.
*   `key`: `varchar [pk]` - Tên khóa.
*   `owner`: `varchar [not null]` - Tiến trình nào đang giữ khóa.
*   `expiration`: `int [not null]` - Thời gian tự mở khóa.

### Bảng `jobs`
Hàng đợi các tác vụ nền tốn thời gian cần xử lý (Ví dụ: Gửi email cho 1000 sinh viên, Import file Excel 500 dòng).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `queue`: `varchar [not null]` - Tên luồng đợi.
*   `payload`: `longtext [not null]` - Dữ liệu và Tên hàm thực thi tác vụ.
*   `attempts`: `tinyint [not null]` - Số lần đã chạy thất bại và phải thử lại.
*   `reserved_at`: `int [null]` - Thời điểm tác vụ bắt đầu được một worker bốc ra chạy.
*   `available_at`: `int [not null]` - Thời điểm tác vụ sẵn sàng để được chạy.
*   `created_at`: `int [not null]` - Thời điểm sinh ra tác vụ.

### Bảng `job_batches`
Quản lý một nhóm/Lô lớn các tác vụ nhỏ (Job Batch). VD: Tác vụ to Import 10,000 dòng Excel chia làm 100 batch.
*   `id`: `varchar [pk]` - Batch ID.
*   `name`: `varchar [not null]` - Tên lô tác vụ.
*   `total_jobs`, `pending_jobs`, `failed_jobs`: `int [not null]` - Thống kê số lượng tác vụ trong lô (Tổng, Đang chờ, Thất bại).
*   `failed_job_ids`: `longtext [not null]` - ID các tác vụ lỗi.
*   `options`: `mediumtext [null]` - Tùy chọn xử lý lô.
*   `cancelled_at`, `created_at`, `finished_at`: `int` - Lịch sử thời gian lô tác vụ.

### Bảng `failed_jobs`
Lưu trữ chi tiết lỗi của các tác vụ ngầm (Jobs) nếu nó chạy lỗi hoàn toàn (Quá số lần thử). Dành cho Coder vào debug.
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `uuid`: `varchar [unique, not null]` - ID lỗi.
*   `connection`, `queue`: `text [not null]` - Tên kết nối và hàng đợi.
*   `payload`: `longtext [not null]` - Dữ liệu tác vụ truyền vào.
*   `exception`: `longtext [not null]` - Cụm thông báo lỗi Stack Trace.
*   `failed_at`: `timestamp [not null]` - Thời điểm sập.

### Bảng `settings`
Lưu trữ các cấu hình chung của toàn hệ thống (Dành cho Super Admin thiết lập).
*   `id`: `bigint [pk, increment]` - Khóa chính.
*   `key`: `varchar(255) [unique, not null]` - Tên khóa cài đặt (VD: 'SITE_MAINTENANCE_MODE').
*   `value`: `text [null]` - Giá trị cài đặt tương ứng (VD: 'true' hoặc 'false').
*   `created_at`, `updated_at`: `timestamp`
