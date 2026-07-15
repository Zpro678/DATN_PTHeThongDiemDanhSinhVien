# Bảng Phân Công Nhiệm Vụ - Dự Án Hệ Thống Điểm Danh Sinh Viên

## Thông tin chung
- **Tên dự án:** Hệ thống điểm danh sinh viên (Student Attendance System)
- **Thời gian thực hiện:** [Ngày bắt đầu] - [Ngày kết thúc]
- **Thành viên:** 
  - Thành viên 1 (TV1): [Tên thành viên 1] - Vai trò: [Trưởng nhóm / Backend / Cơ sở dữ liệu]
  - Thành viên 2 (TV2): [Tên thành viên 2] - Vai trò: [Frontend / UI-UX / Tester]

*(Lưu ý: Bảng phân công dưới đây được thiết kế cho nhóm 2 thành viên. Bạn có thể điều chỉnh lại phần trăm công việc hoặc thêm/bớt người tùy theo thực tế nhóm của mình.)*

---

## 1. Giai đoạn Phân tích yêu cầu (Tuần 1 - 2)

| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 1.1 | Khảo sát hiện trạng | Tìm hiểu quy trình điểm danh thực tế tại trường (điểm danh giấy, gọi tên, vân tay...). | TV1, TV2 | | ⏳ Chưa bắt đầu |
| 1.2 | Thu thập yêu cầu | Xác định các tính năng cốt lõi cần có cho Giảng viên, Sinh viên, và Quản trị viên (Admin). | TV1, TV2 | | ⏳ Chưa bắt đầu |
| 1.3 | Viết tài liệu đặc tả yêu cầu (SRS) | Lập danh sách Use-case, mô tả chi tiết các luồng nghiệp vụ (đăng nhập, tạo lớp, quét QR điểm danh, xin nghỉ...). | TV1 | | ⏳ Chưa bắt đầu |
| 1.4 | Xác định công nghệ sử dụng | Thống nhất Stack công nghệ: Laravel, Livewire, MySQL, Docker, TailwindCSS... | TV1, TV2 | | ⏳ Chưa bắt đầu |
| 1.5 | Chốt kế hoạch và Báo cáo GVHD | Báo cáo tiến độ với Giảng viên hướng dẫn, chốt lại phạm vi (scope) dự án để tránh lan man. | TV1, TV2 | | ⏳ Chưa bắt đầu |

---

## 2. Giai đoạn Thiết kế hệ thống (Tuần 3 - 4)

| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 2.1 | Thiết kế cơ sở dữ liệu (ERD) | Thiết kế các bảng: Users, Roles, Classes, Attendance_Sessions, Attendance_Records, Leave_Requests... | TV1 | | ⏳ Chưa bắt đầu |
| 2.2 | Thiết kế kiến trúc hệ thống | Vẽ sơ đồ luồng dữ liệu (Data Flow Diagram), sơ đồ hoạt động (Activity Diagram). | TV2 | | ⏳ Chưa bắt đầu |
| 2.3 | Thiết kế giao diện (UI/UX) | Vẽ Wireframe/Mockup cho các màn hình: Dashboard, Danh sách lớp, Giao diện điểm danh bằng QR, Lịch sử vắng. | TV2 | | ⏳ Chưa bắt đầu |
| 2.4 | Thiết kế luồng tích hợp (nếu có) | Thiết kế luồng cho hệ thống QR động, luồng gửi email thông báo tự động. | TV1 | | ⏳ Chưa bắt đầu |

---

## 3. Giai đoạn Lập trình & Cài đặt (Tuần 5 - 10)

### 3.1. Phân hệ Quản trị viên (Admin)
| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 3.1.1 | Quản lý tài khoản & Phân quyền | CRUD (Thêm/Sửa/Xóa) Giảng viên, Sinh viên. Phân quyền hệ thống (Role/Permission). | TV1 | | ⏳ Chưa bắt đầu |
| 3.1.2 | Quản lý danh mục cốt lõi | Quản lý Khoa, Ngành, Niên khóa, Môn học. | TV2 | | ⏳ Chưa bắt đầu |
| 3.1.3 | Quản lý Lớp học | Tạo lớp học phần, gán giảng viên, thêm sinh viên vào lớp (Import bằng File Excel). | TV1 | | ⏳ Chưa bắt đầu |

### 3.2. Phân hệ Giảng viên (Lecturer)
| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 3.2.1 | Danh sách lớp & Lịch dạy | Giao diện hiển thị các lớp giảng viên đang phụ trách. | TV2 | | ⏳ Chưa bắt đầu |
| 3.2.2 | Quản lý phiên điểm danh | Tính năng Mở/Đóng phiên điểm danh (Tạo mã QR thời gian thực hoặc gọi tên thủ công). | TV1 | | ⏳ Chưa bắt đầu |
| 3.2.3 | Quản lý đơn xin nghỉ | Xem, duyệt hoặc từ chối đơn xin nghỉ phép của sinh viên kèm minh chứng. | TV2 | | ⏳ Chưa bắt đầu |
| 3.2.4 | Thống kê & Báo cáo điểm danh | Xem tỷ lệ chuyên cần của lớp, cảnh báo cấm thi, xuất báo cáo ra Excel/PDF. | TV1 | | ⏳ Chưa bắt đầu |

### 3.3. Phân hệ Sinh viên (Student)
| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 3.3.1 | Giao diện Lịch học | Xem danh sách môn học và lịch học trong tuần. | TV2 | | ⏳ Chưa bắt đầu |
| 3.3.2 | Thực hiện điểm danh | Tính năng quét mã QR do giảng viên hiển thị để xác nhận có mặt. | TV1 | | ⏳ Chưa bắt đầu |
| 3.3.3 | Nộp đơn xin nghỉ phép | Form điền thông tin xin nghỉ và upload minh chứng (giấy khám bệnh...). | TV2 | | ⏳ Chưa bắt đầu |
| 3.3.4 | Lịch sử & Thống kê cá nhân | Theo dõi số buổi đã vắng, xem lại lịch sử các buổi đã điểm danh. | TV1 | | ⏳ Chưa bắt đầu |

---

## 4. Giai đoạn Kiểm thử (Testing) (Tuần 11 - 12)

| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 4.1 | Lên kịch bản kiểm thử (Test Cases)| Liệt kê các Test-case quan trọng: Quét mã QR hết hạn, xin nghỉ trùng ngày, import excel sai format... | TV2 | | ⏳ Chưa bắt đầu |
| 4.2 | Kiểm thử chức năng & Tích hợp | Test trên môi trường local các luồng nghiệp vụ end-to-end. | TV1, TV2 | | ⏳ Chưa bắt đầu |
| 4.3 | Kiểm thử giao diện (Responsive) | Chạy thử trên Desktop, Tablet, Mobile (Đặc biệt phần sinh viên quét mã bằng điện thoại). | TV2 | | ⏳ Chưa bắt đầu |
| 4.4 | Sửa lỗi (Fix bug) | Xử lý và khắc phục các lỗi phát sinh trong quá trình kiểm thử. | TV1 | | ⏳ Chưa bắt đầu |

---

## 5. Giai đoạn Triển khai & Viết báo cáo đồ án (Tuần 13 - 15)

| STT | Tên công việc | Chi tiết công việc | Người phụ trách | Thời gian dự kiến | Trạng thái |
|---|---|---|---|---|---|
| 5.1 | Triển khai lên Server (Deploy) | Thuê VPS/Hosting, trỏ Domain, deploy ứng dụng (sử dụng Docker hoặc deploy trực tiếp). | TV1 | | ⏳ Chưa bắt đầu |
| 5.2 | Viết tài liệu / Quyển báo cáo | Chia nhau viết các chương: Mở đầu, Cơ sở lý thuyết, Phân tích thiết kế, Cài đặt và Kết quả. | TV1, TV2 | | ⏳ Chưa bắt đầu |
| 5.3 | Chuẩn bị Slide thuyết trình | Làm Slide tóm tắt các chức năng nổi bật, công nghệ áp dụng, kết quả đạt được. | TV2 | | ⏳ Chưa bắt đầu |
| 5.4 | Chuẩn bị kịch bản Demo | Lên kịch bản chi tiết từng bước sẽ demo trực tiếp nghiệm thu với hội đồng. | TV1, TV2 | | ⏳ Chưa bắt đầu |

---
**💡 Một số quy tắc làm việc nhóm:**
1. Code phải được đẩy (Push) lên **Github/Gitlab** mỗi ngày để quản lý version.
2. Khi làm xong 1 module cần báo cho nhóm trưởng để tiến hành review và gộp (Merge) code.
3. Cập nhật trạng thái file này thường xuyên (Chuyển "⏳ Chưa bắt đầu" thành "🚧 Đang làm" hoặc "✅ Hoàn thành").
