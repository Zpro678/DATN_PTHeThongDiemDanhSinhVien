<x-admin-layout title="Cấu hình hệ thống">
    <div class="mx-auto max-w-[1200px]" x-data="{ activeTab: 'general' }">
        <div class="mb-6 p-5 lg:p-6">
            <div class="relative z-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-slate-400">Settings</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Cấu hình hệ thống</h1>
                <p class="mt-1 text-sm text-slate-500">Quản lý các thiết lập lõi, quy tắc nghiệp vụ và cổng kết nối của SAMS.</p>
            </div>
            </div>
        </div>

        <div class="flex flex-col gap-8 lg:flex-row">
            <div class="w-full shrink-0 lg:w-64">
                <nav class="admin-card flex flex-row gap-1 overflow-x-auto rounded-2xl border p-2 lg:flex-col lg:overflow-visible">



                    <button type="button" @click="activeTab = 'email'" :class="{ 'bg-blue-50 text-blue-700': activeTab === 'email', 'text-slate-600 hover:bg-slate-50': activeTab !== 'email' }" class="flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:whitespace-normal">
                        <x-user.icon name="mail" :size="20" class="opacity-70" />
                        Email & Thông báo
                    </button>

                    <button type="button" @click="activeTab = 'general'" :class="{ 'bg-blue-50 text-blue-700': activeTab === 'general', 'text-slate-600 hover:bg-slate-50': activeTab !== 'general' }" class="flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:whitespace-normal">
                        <x-user.icon name="settings" :size="20" class="opacity-70" />
                        Giao diện & Tải lên
                    </button>

                    <button type="button" @click="activeTab = 'maintenance'" :class="{ 'bg-rose-50 text-rose-700': activeTab === 'maintenance', 'text-slate-600 hover:bg-rose-50 hover:text-rose-600': activeTab !== 'maintenance' }" class="mt-0 flex items-center gap-3 whitespace-nowrap rounded-xl px-4 py-3 text-left text-sm font-bold transition-colors lg:mt-4 lg:whitespace-normal">
                        <x-user.icon name="alert-triangle" :size="20" class="opacity-70" />
                        Bảo trì hệ thống
                    </button>
                </nav>
            </div>

            <div class="admin-card min-h-[500px] flex-1 overflow-visible rounded-3xl border">


                <div x-cloak x-show="activeTab === 'email'" x-transition.opacity>
                    @livewire('admin.settings.email-settings')
                </div>

                <div x-cloak x-show="activeTab === 'general'" x-transition.opacity>
                    @livewire('admin.settings.general-settings')
                </div>

                <div x-cloak x-show="activeTab === 'maintenance'" class="space-y-8 p-6 md:p-8" x-transition.opacity>
                    @livewire('admin.settings.maintenance-settings')
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
