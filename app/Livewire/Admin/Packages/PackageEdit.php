<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PackageEdit extends Component
{
    public Plan $package;
    
    public $name = '';
    public $description = '';
    public $priceType = 'fixed';
    public $price = 0;
    public $duration_days = 365;
    
    public $isUnlimitedClasses = false;
    public $max_classes = 5;
    
    public $isUnlimitedStudents = false;
    public $max_students_per_class = 50;
    
    public $max_gps_radius = 100;
    
    public $hasGps = false;
    public $hasImport = false;
    public $hasReports = false;
    public $hasApi = false;

    public function mount(Plan $package)
    {
        abort_unless(Auth::user()?->is_admin, 403);
        
        $this->package = $package;
        $this->name = $package->name;
        $this->description = $package->description;
        $this->price = $package->price;
        $this->priceType = $package->price > 0 ? 'fixed' : 'free';
        $this->duration_days = $package->duration_days;
        
        $this->isUnlimitedClasses = $package->max_classes >= 99999;
        $this->max_classes = $this->isUnlimitedClasses ? 5 : $package->max_classes;
        
        $this->isUnlimitedStudents = $package->max_students_per_class >= 99999;
        $this->max_students_per_class = $this->isUnlimitedStudents ? 50 : $package->max_students_per_class;
        
        $this->hasGps = $package->max_gps_radius > 0;
        $this->hasImport = (bool) $package->can_export_excel;
        $this->hasReports = in_array($package->support_level, ['Nâng cao', 'Premium']);
        $this->hasApi = (bool) $package->api_access;
    }

    public function save()
    {
        abort_unless(Auth::user()?->is_admin, 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priceType' => 'required|in:fixed,free,contact',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'required|in:30,90,180,365,0',
            'max_classes' => 'nullable|numeric|min:1',
            'max_students_per_class' => 'nullable|numeric|min:1',
        ]);

        $finalPrice = $this->priceType === 'free' ? 0 : ($this->priceType === 'contact' ? 0 : $this->price);
        $finalMaxClasses = $this->isUnlimitedClasses ? 999999 : ($this->max_classes ?: 1);
        $finalMaxStudents = $this->isUnlimitedStudents ? 999999 : ($this->max_students_per_class ?: 1);
        
        $features = [];
        if ($this->hasGps) $features[] = 'Xác thực vị trí GPS';
        if ($this->hasImport) $features[] = 'Import học viên từ Excel/CSV';
        if ($this->hasReports) $features[] = 'Báo cáo Thống kê Nâng cao';
        if ($this->hasApi) $features[] = 'Tích hợp API (SSO, LMS)';

        $this->package->update([
            'name' => $this->name,
            'description' => $this->description,
            'price' => $finalPrice,
            'duration_days' => (int) $this->duration_days,
            'max_classes' => $finalMaxClasses,
            'max_students_per_class' => $finalMaxStudents,
            'max_gps_radius' => $this->hasGps ? 100 : 0,
            'can_export_excel' => $this->hasImport,
            'api_access' => $this->hasApi,
            'support_level' => $this->hasReports ? 'Nâng cao' : 'Cơ bản',
            'features' => $features,
        ]);

        session()->flash('success', 'Cập nhật gói dịch vụ thành công.');
        return redirect()->route('admin.packages.index');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.packages.package-edit')->title('Sửa gói dịch vụ');
    }
}
