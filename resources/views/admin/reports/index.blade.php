<x-admin-layout title="Báo cáo">
    <div class="mx-auto max-w-[1500px] space-y-6">
        <section class="admin-card overflow-hidden rounded-3xl border p-6 lg:p-7">
            <div class="relative z-10 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Reports</p>
                <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">Báo cáo tổng hợp</h1>
                <p class="mt-1 text-sm font-medium text-slate-500">Số liệu hành chính được gom thành từng mảng rõ ràng.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.logs.index') }}" class="admin-soft-button rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50">
                    Nhật ký hệ thống
                </a>
                <a href="{{ route('admin.dashboard') }}" class="admin-soft-button rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-500/20">
                    Quay lại dashboard
                </a>
            </div>
            </div>
        </section>

        <section class="admin-grid-equal grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">
            <x-stats-card title="Doanh thu" :value="number_format($overview['revenue'], 0, ',', '.') . 'đ'" change="Tổng cộng" :isPositive="true" icon="dollar-sign" iconBg="bg-green-50 border-green-100 text-green-600" sparklineColor="stroke-green-500" sparklinePath="M 0,15 L 10,13 L 20,11 L 30,9 L 40,7 L 50,4" />
            <x-stats-card title="Giao dịch" :value="$overview['transactions']" change="Thành công" :isPositive="true" icon="credit-card" iconBg="bg-cyan-50 border-cyan-100 text-cyan-600" sparklineColor="stroke-cyan-500" sparklinePath="M 0,5 L 10,7 L 20,6 L 30,11 L 40,14 L 50,9" />
            <x-stats-card title="Người dùng" :value="$overview['users']" change="Tài khoản" :isPositive="true" icon="users" iconBg="bg-blue-50 border-blue-100 text-blue-600" sparklineColor="stroke-blue-500" sparklinePath="M 0,15 L 10,13 L 20,11 L 30,9 L 40,7 L 50,4" />
            <x-stats-card title="Lớp học" :value="$overview['classes']" change="Đang quản lý" :isPositive="true" icon="book-open" iconBg="bg-emerald-50 border-emerald-100 text-emerald-600" sparklineColor="stroke-emerald-500" sparklinePath="M 0,18 L 10,16 L 20,14 L 30,11 L 40,7 L 50,4" />
            <x-stats-card title="Gói dịch vụ" :value="$overview['plans']" change="Cấu hình" :isPositive="true" icon="package" iconBg="bg-amber-50 border-amber-100 text-amber-600" sparklineColor="stroke-amber-500" sparklinePath="M 0,6 L 10,8 L 20,7 L 30,12 L 40,15 L 50,10" />
        </section>

        <div class="admin-grid-equal grid grid-cols-1 gap-6 xl:grid-cols-[1fr_400px]">
            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border p-6">
                <div class="relative z-10">
                    <h2 class="text-xl font-black text-slate-900">Biểu đồ doanh thu 6 tháng gần nhất</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Thống kê số tiền thu được từ việc bán gói dịch vụ.</p>
                    
                    <div class="mt-6 flex h-[300px] items-end gap-2 sm:gap-4">
                        @php
                            $maxAmount = count($monthlyRevenue) > 0 ? max(array_column($monthlyRevenue, 'amount')) : 1;
                            $maxAmount = $maxAmount == 0 ? 1 : $maxAmount;
                        @endphp
                        @foreach($monthlyRevenue as $data)
                            @php
                                $heightPercentage = ($data['amount'] / $maxAmount) * 100;
                            @endphp
                            <div class="group relative flex h-full flex-1 flex-col items-center justify-end gap-2">
                                <div class="absolute -top-10 hidden whitespace-nowrap rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-bold text-white shadow-lg group-hover:block">
                                    {{ number_format($data['amount'], 0, ',', '.') }}đ
                                </div>
                                <div class="w-full rounded-t-xl bg-gradient-to-t from-blue-600 to-cyan-400 transition-all duration-300 hover:opacity-80" style="height: {{ $heightPercentage }}%;"></div>
                                <span class="text-xs font-bold text-slate-500">{{ $data['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="admin-card admin-card-hover overflow-hidden rounded-2xl border">
                <div class="relative z-10 border-b border-slate-100 p-6">
                    <h2 class="text-xl font-black text-slate-900">Giao dịch gần đây</h2>
                    <p class="mt-1 text-sm font-medium text-slate-500">Các giao dịch thanh toán gói dịch vụ mới nhất.</p>
                </div>

                <div class="relative z-10 divide-y divide-slate-100 h-[330px] overflow-y-auto custom-scrollbar">
                    @forelse ($recentTransactions as $transaction)
                        <div class="flex flex-col gap-2 p-5 transition hover:bg-blue-50/40">
                            <div class="flex min-w-0 items-center justify-between gap-3">
                                <p class="min-w-0 truncate text-sm font-black text-slate-900">+{{ number_format($transaction->amount, 0, ',', '.') }}đ</p>
                                <p class="shrink-0 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $transaction->created_at?->format('d/m H:i') }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                                <span class="rounded-lg border border-blue-100 bg-blue-50 px-2 py-0.5 text-blue-700">{{ $transaction->payment_method ?? 'MoMo' }}</span>
                                <span class="rounded-lg border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-emerald-700">Thành công</span>
                            </div>
                            <p class="truncate text-sm font-medium text-slate-500">
                                {{ $transaction->user?->name ?? 'Người dùng ẩn' }} mua gói <span class="font-bold text-slate-700">{{ $transaction->plan?->name ?? 'Không rõ' }}</span>
                            </p>
                        </div>
                    @empty
                        <div class="p-6 text-sm font-medium text-slate-500">Chưa có giao dịch nào.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
