<div class="mx-auto max-w-5xl p-4 sm:p-6 lg:p-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    
    {{-- Hero Section --}}
    <div class="mb-8 rounded-3xl bg-gradient-to-br from-primary to-primary/80 px-8 py-12 text-center text-white shadow-lg shadow-primary/20">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white/20 backdrop-blur-md">
            <x-user.icon name="headphones" :size="40" class="text-white" />
        </div>
        <h1 class="mb-4 text-3xl font-black md:text-4xl">Chúng tôi có thể giúp gì cho bạn?</h1>
        <p class="mx-auto max-w-2xl text-primary-100 md:text-lg">Tìm kiếm câu trả lời trong các câu hỏi thường gặp hoặc gửi trực tiếp yêu cầu hỗ trợ cho đội ngũ kỹ thuật của EduTrack.</p>
    </div>

    <div class="grid gap-8 md:grid-cols-3">
        
        {{-- Cột trái: Thông tin liên hệ & Hướng dẫn --}}
        <div class="flex flex-col gap-4 md:col-span-1">
            <h2 class="mb-2 text-lg font-bold text-on-surface">Kênh hỗ trợ</h2>
            
            <a href="#" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-primary/30">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                    <x-user.icon name="mail" :size="24" />
                </div>
                <div>
                    <p class="text-sm font-bold text-on-surface">Gửi Email</p>
                    <p class="text-xs text-on-surface-variant">support@edutrack.vn</p>
                </div>
            </a>

            <a href="#" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-green-500/30">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600 transition-colors group-hover:bg-green-500 group-hover:text-white">
                    <x-user.icon name="phone-call" :size="24" />
                </div>
                <div>
                    <p class="text-sm font-bold text-on-surface">Hotline / Zalo</p>
                    <p class="text-xs text-on-surface-variant">0123 456 789</p>
                </div>
            </a>

            <a href="#" class="group flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-outline-variant/20 transition-all hover:-translate-y-1 hover:shadow-md hover:ring-secondary/30">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary transition-colors group-hover:bg-secondary group-hover:text-white">
                    <x-user.icon name="book" :size="24" />
                </div>
                <div>
                    <p class="text-sm font-bold text-on-surface">Tài liệu HDSD</p>
                    <p class="text-xs text-on-surface-variant">Xem hướng dẫn chi tiết</p>
                </div>
            </a>
        </div>

        {{-- Cột phải: Form hỗ trợ & FAQ --}}
        <div class="flex flex-col gap-8 md:col-span-2">
            
            {{-- Form Gửi Yêu Cầu --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:p-8">
                <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-on-surface">
                    <x-user.icon name="message-square" :size="24" class="text-primary" />
                    Gửi yêu cầu hỗ trợ
                </h2>
                
                <form class="space-y-5" x-data="{ sending: false, sent: false }" @submit.prevent="sending = true; setTimeout(() => { sending = false; sent = true; }, 1500)">
                    <div x-show="sent" x-transition class="rounded-2xl bg-green-50 p-4 text-green-700 ring-1 ring-green-200">
                        <p class="flex items-center gap-2 font-bold">
                            <x-user.icon name="check-circle" :size="20" />
                            Gửi yêu cầu thành công!
                        </p>
                        <p class="mt-1 text-sm">Chúng tôi đã nhận được yêu cầu của bạn và sẽ phản hồi qua email trong thời gian sớm nhất.</p>
                    </div>

                    <div x-show="!sent">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-on-surface">Chủ đề <span class="text-error">*</span></label>
                                <select required class="w-full rounded-xl border-outline-variant/50 bg-surface-container-lowest px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                                    <option value="">Chọn chủ đề hỗ trợ</option>
                                    <option value="account">Lỗi tài khoản / Đăng nhập</option>
                                    <option value="attendance">Lỗi điểm danh / Quét QR</option>
                                    <option value="system">Lỗi hệ thống</option>
                                    <option value="other">Vấn đề khác</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-on-surface">Mã lớp (Nếu có)</label>
                                <input type="text" placeholder="Ví dụ: WEB-2026-01" class="w-full rounded-xl border-outline-variant/50 bg-surface-container-lowest px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20">
                            </div>
                        </div>

                        <div class="mt-5 space-y-1.5">
                            <label class="text-sm font-bold text-on-surface">Nội dung chi tiết <span class="text-error">*</span></label>
                            <textarea required rows="4" placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải..." class="w-full resize-none rounded-xl border-outline-variant/50 bg-surface-container-lowest px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"></textarea>
                        </div>

                        <button type="submit" :disabled="sending" class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-bold text-white shadow-lg shadow-primary/30 transition-all hover:bg-primary/90 disabled:opacity-70 sm:w-auto">
                            <template x-if="!sending">
                                <x-user.icon name="send" :size="18" />
                            </template>
                            <template x-if="sending">
                                <x-user.icon name="loader" :size="18" class="animate-spin" />
                            </template>
                            <span x-text="sending ? 'Đang gửi...' : 'Gửi yêu cầu'"></span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- FAQs --}}
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-outline-variant/20 sm:p-8">
                <h2 class="mb-6 flex items-center gap-3 text-xl font-bold text-on-surface">
                    <x-user.icon name="help-circle" :size="24" class="text-primary" />
                    Câu hỏi thường gặp
                </h2>

                <div class="space-y-4" x-data="{ active: null }">
                    {{-- FAQ 1 --}}
                    <div class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest transition-all hover:border-primary/30">
                        <button @click="active = active === 1 ? null : 1" class="flex w-full items-center justify-between p-4 text-left font-bold text-on-surface focus:outline-none">
                            <span>Tại sao tôi không thể điểm danh bằng mã QR?</span>
                            <x-user.icon name="chevron-down" :size="20" class="text-on-surface-variant transition-transform duration-200" x-bind:class="active === 1 ? 'rotate-180 text-primary' : ''" />
                        </button>
                        <div x-show="active === 1" x-collapse>
                            <div class="p-4 pt-0 text-sm leading-relaxed text-on-surface-variant border-t border-outline-variant/10">
                                Hãy đảm bảo rằng bạn đã cho phép trình duyệt truy cập Vị trí (Location) và thiết bị của bạn đang nằm trong bán kính cho phép của lớp học. Nếu vẫn lỗi, thử tải lại trang hoặc liên hệ Giảng viên để được điểm danh thủ công.
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 2 --}}
                    <div class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest transition-all hover:border-primary/30">
                        <button @click="active = active === 2 ? null : 2" class="flex w-full items-center justify-between p-4 text-left font-bold text-on-surface focus:outline-none">
                            <span>Làm sao để nộp đơn xin phép vắng mặt?</span>
                            <x-user.icon name="chevron-down" :size="20" class="text-on-surface-variant transition-transform duration-200" x-bind:class="active === 2 ? 'rotate-180 text-primary' : ''" />
                        </button>
                        <div x-show="active === 2" x-collapse>
                            <div class="p-4 pt-0 text-sm leading-relaxed text-on-surface-variant border-t border-outline-variant/10">
                                Bạn vào "Không gian Học viên > Xin nghỉ phép", chọn ngày vắng và đính kèm minh chứng hợp lệ (nếu có). Sau đó ấn Gửi đơn. Giảng viên sẽ duyệt đơn của bạn trên hệ thống.
                            </div>
                        </div>
                    </div>

                    {{-- FAQ 3 --}}
                    <div class="rounded-2xl border border-outline-variant/30 bg-surface-container-lowest transition-all hover:border-primary/30">
                        <button @click="active = active === 3 ? null : 3" class="flex w-full items-center justify-between p-4 text-left font-bold text-on-surface focus:outline-none">
                            <span>Tôi là giảng viên, làm sao để thêm sinh viên vào lớp?</span>
                            <x-user.icon name="chevron-down" :size="20" class="text-on-surface-variant transition-transform duration-200" x-bind:class="active === 3 ? 'rotate-180 text-primary' : ''" />
                        </button>
                        <div x-show="active === 3" x-collapse>
                            <div class="p-4 pt-0 text-sm leading-relaxed text-on-surface-variant border-t border-outline-variant/10">
                                Có hai cách: Bạn có thể đưa "Mã lớp" cho sinh viên tự vào tham gia, hoặc vào "Lớp học > Quản lý sinh viên > Import Excel" để thêm sinh viên hàng loạt vào lớp học của mình.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
