@component('layouts.user', ['title' => 'Tài liệu Hướng dẫn sử dụng'])
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-10 border-b border-slate-200/80 pb-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 md:text-4xl">Tài liệu Hướng dẫn sử dụng</h1>
            <p class="mt-3 text-base text-slate-500">Khám phá cách sử dụng hệ thống điểm danh <span class="font-semibold text-primary">Attendia Tech</span> một cách hiệu quả nhất.</p>
        </div>

        <div class="flex flex-col gap-10 md:flex-row md:items-start">
            <!-- Sidebar Navigation -->
            <nav class="sticky top-24 w-full shrink-0 space-y-1 md:w-64 bg-white/50 backdrop-blur-sm rounded-2xl p-4 ring-1 ring-slate-100" x-data="{ activeSection: 'intro' }">
                <a href="#intro" @click="activeSection = 'intro'" 
                   :class="activeSection === 'intro' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" 
                   class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Giới thiệu chung</a>
                
                <h3 class="mt-6 px-4 text-[13px] font-extrabold uppercase tracking-widest text-slate-900">Dành cho Giảng viên</h3>
                <div class="mt-2 space-y-1">
                    <a href="#gv-tao-lop" @click="activeSection = 'gv-tao-lop'" :class="activeSection === 'gv-tao-lop' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Tạo và quản lý lớp</a>
                    <a href="#gv-diem-danh" @click="activeSection = 'gv-diem-danh'" :class="activeSection === 'gv-diem-danh' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Phiên điểm danh (QR)</a>
                    <a href="#gv-thong-ke" @click="activeSection = 'gv-thong-ke'" :class="activeSection === 'gv-thong-ke' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Thống kê & xuất báo cáo</a>
                </div>

                <h3 class="mt-6 px-4 text-[13px] font-extrabold uppercase tracking-widest text-slate-900">Dành cho Sinh viên</h3>
                <div class="mt-2 space-y-1">
                    <a href="#sv-tham-gia" @click="activeSection = 'sv-tham-gia'" :class="activeSection === 'sv-tham-gia' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Tham gia lớp học</a>
                    <a href="#sv-quet-qr" @click="activeSection = 'sv-quet-qr'" :class="activeSection === 'sv-quet-qr' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Quét mã QR điểm danh</a>
                    <a href="#sv-xin-nghi" @click="activeSection = 'sv-xin-nghi'" :class="activeSection === 'sv-xin-nghi' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Xin nghỉ phép có minh chứng</a>
                </div>

                <h3 class="mt-6 px-4 text-[13px] font-extrabold uppercase tracking-widest text-slate-900">Hỗ trợ & Góp ý</h3>
                <div class="mt-2 space-y-1">
                    <a href="#support" @click="activeSection = 'support'" :class="activeSection === 'support' ? 'bg-primary/10 text-primary font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'" class="block rounded-xl px-4 py-2.5 text-sm font-medium transition-all duration-200">Gửi phản hồi và báo lỗi</a>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="min-w-0 flex-1 w-full max-w-3xl space-y-14 pb-20">
                
                <section id="intro" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600"><x-user.icon name="info" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Giới thiệu chung</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Chào mừng bạn đến với tài liệu hướng dẫn sử dụng hệ thống điểm danh <strong>Attendia Tech</strong>. Hệ thống được thiết kế để giúp giảng viên và sinh viên thực hiện công tác điểm danh một cách nhanh chóng, minh bạch và chính xác thông qua công nghệ quét mã QR động và xác thực vị trí (GPS).</p>
                    <div class="flex items-start gap-4 rounded-2xl bg-blue-50/80 p-5 border border-blue-100/50 shadow-sm">
                        <x-user.icon name="alert-circle" :size="20" class="text-blue-500 shrink-0 mt-0.5" />
                        <div>
                            <p class="m-0 text-sm font-medium text-blue-900">Lưu ý quan trọng</p>
                            <p class="m-0 mt-1 text-sm text-blue-800/80 leading-relaxed">Để sử dụng đầy đủ các tính năng, vui lòng cho phép trình duyệt truy cập <strong>Camera</strong> (để quét QR) và <strong>Vị trí</strong> (để xác thực GPS khi điểm danh).</p>
                        </div>
                    </div>
                </section>

                <div class="flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Giảng viên</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <!-- Giảng viên -->
                <section id="gv-tao-lop" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600"><x-user.icon name="plus-circle" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Tạo và quản lý lớp học</h2>
                    </div>
                    <ol class="list-decimal pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400 marker:font-semibold">
                        <li>Từ thanh menu bên trái, chọn <strong>Giảng dạy > Lớp tôi quản lý</strong>.</li>
                        <li>Nhấn nút <strong>Tạo lớp mới</strong> (hoặc dấu cộng ở góc dưới màn hình trên điện thoại).</li>
                        <li>Nhập tên lớp, mã học phần, mô tả... Sau đó nhấn <strong>Lưu lại</strong>.</li>
                        <li>Sau khi tạo, hệ thống sẽ cấp một <strong class="text-primary">Mã tham gia</strong> (VD: <code>ABCD12</code>). Bạn hãy gửi mã này cho sinh viên để họ tự động tham gia vào lớp.</li>
                    </ol>
                </section>

                <section id="gv-diem-danh" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"><x-user.icon name="qr-code" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Mở phiên điểm danh (QR)</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Thay vì điểm danh gọi tên truyền thống, bạn có thể tạo mã QR để sinh viên tự quét:</p>
                    <ol class="list-decimal pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400 marker:font-semibold">
                        <li>Vào chi tiết lớp học đang quản lý, chuyển sang tab <strong>Điểm danh</strong>.</li>
                        <li>Nhấn nút <strong class="text-primary">Tạo phiên điểm danh QR</strong>.</li>
                        <li>Thiết lập thời gian hết hạn (VD: 5 phút hoặc 10 phút) và bật/tắt yêu cầu xác thực GPS.</li>
                        <li>Trình chiếu mã QR lên màn hình máy chiếu. Mã QR sẽ liên tục làm mới (Dynamic QR) mỗi 5-10 giây để chống gian lận (chụp ảnh màn hình gửi cho người khác).</li>
                    </ol>
                </section>

                <section id="gv-thong-ke" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600"><x-user.icon name="bar-chart-2" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Thống kê và xuất báo cáo</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Hệ thống tự động theo dõi và tính toán tỷ lệ chuyên cần của từng sinh viên:</p>
                    <ul class="list-disc pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400">
                        <li>Bạn có thể xem biểu đồ trực quan trong tab <strong>Thống kê</strong> của lớp học.</li>
                        <li>Nhấn <strong class="text-primary">Xuất Excel</strong> để tải danh sách điểm danh về máy, phục vụ cho việc nhập điểm vào hệ thống đào tạo của nhà trường.</li>
                    </ul>
                </section>

                <div class="flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Sinh viên</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <!-- Sinh viên -->
                <section id="sv-tham-gia" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600"><x-user.icon name="log-in" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Tham gia lớp học</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Để bắt đầu điểm danh, bạn cần phải có mặt trong danh sách lớp:</p>
                    <ol class="list-decimal pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400 marker:font-semibold">
                        <li>Nhấn nút <strong class="text-emerald-600">Tham gia</strong> ở màn hình Tổng quan.</li>
                        <li>Nhập mã lớp do giảng viên cung cấp (VD: <code>ABCD12</code>).</li>
                        <li>Chờ giảng viên duyệt (nếu lớp yêu cầu phê duyệt) hoặc bạn sẽ được thêm vào ngay lập tức.</li>
                    </ol>
                </section>

                <section id="sv-quet-qr" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-pink-100 text-pink-600"><x-user.icon name="camera" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Quét mã QR điểm danh</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Đây là thao tác bạn sẽ sử dụng thường xuyên nhất trong quá trình học tập:</p>
                    <ol class="list-decimal pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400 marker:font-semibold">
                        <li>Khi giảng viên mở mã QR trên bảng, hãy mở điện thoại, nhấn vào biểu tượng <strong>Quét QR</strong> (dấu cộng ở giữa thanh điều hướng dưới cùng).</li>
                        <li>Đưa camera điện thoại hướng về phía mã QR.</li>
                        <li>Nếu giảng viên yêu cầu GPS, hệ thống sẽ tự động kiểm tra xem bạn có đang ở gần lớp học (trong bán kính cho phép) hay không.</li>
                        <li>Sau khi hệ thống báo Điểm danh thành công, trạng thái của bạn sẽ lập tức chuyển thành <strong class="text-emerald-600">Có mặt</strong>.</li>
                    </ol>
                </section>

                <section id="sv-xin-nghi" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600"><x-user.icon name="file-text" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Xin nghỉ phép có minh chứng</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Nếu bạn bị ốm hoặc có việc bận chính đáng không thể đến lớp:</p>
                    <ol class="list-decimal pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400 marker:font-semibold">
                        <li>Vào mục <strong>Học tập > Xin nghỉ phép</strong>.</li>
                        <li>Chọn lớp học, ngày xin nghỉ và ghi rõ lý do.</li>
                        <li>Tải lên hình ảnh minh chứng (Giấy khám bệnh, đơn xin phép có chữ ký, v.v.).</li>
                        <li>Gửi yêu cầu và theo dõi trạng thái phê duyệt từ giảng viên. Trạng thái điểm danh ngày hôm đó sẽ tự động đổi thành <strong class="text-amber-600">Vắng có phép</strong> nếu đơn được duyệt.</li>
                    </ol>
                </section>

                <div class="flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200"></div>
                    <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Hỗ trợ</span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <!-- Hỗ trợ -->
                <section id="support" class="scroll-mt-28">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><x-user.icon name="help-circle" :size="20"/></span>
                        <h2 class="text-2xl font-bold text-slate-800">Gửi phản hồi và báo lỗi</h2>
                    </div>
                    <p class="mb-5 text-[15px] text-slate-600 leading-relaxed">Trong quá trình sử dụng, nếu gặp bất kỳ khó khăn hoặc lỗi kỹ thuật, bạn có thể gửi yêu cầu hỗ trợ:</p>
                    <ul class="list-disc pl-5 space-y-3 text-[15px] text-slate-600 leading-relaxed marker:text-slate-400">
                        <li>Truy cập mục <strong>Phản hồi</strong> từ thanh menu.</li>
                        <li>Chọn loại yêu cầu: <strong>Báo lỗi</strong> hoặc <strong>Góp ý tính năng</strong>.</li>
                        <li>Nhập tiêu đề, mô tả chi tiết vấn đề và đính kèm hình ảnh chụp màn hình (nếu có).</li>
                        <li>Quản trị viên hệ thống sẽ xem xét và phản hồi trực tiếp cho bạn (kết quả hiển thị ngay trong danh sách lịch sử phản hồi).</li>
                    </ul>
                </section>

            </div>
        </div>
    </div>
@endcomponent
