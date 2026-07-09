<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PackageEdit extends Component
{
    public Plan $package;
    
    public $plan_tier = '';
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
    
    public $hasImport = false;

    public function mount(Plan $package)
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        
        $this->package = $package;
        
        $currentTier = strtoupper($package->plan_tier);
        if (in_array($currentTier, ['FREE', 'PRO', 'PREMIUM'])) {
            $this->plan_tier = $currentTier;
        } else {
            if ($package->price <= 0) {
                $this->plan_tier = 'FREE';
            } elseif (stripos($package->name, 'enterprise') !== false || stripos($currentTier, 'enterprise') !== false) {
                $this->plan_tier = 'PREMIUM';
            } else {
                $this->plan_tier = 'PRO';
            }
        }
        
        $this->name = $package->name;
        $this->description = $package->description;
        $this->price = $package->price;
        $this->priceType = $package->price > 0 ? 'fixed' : 'free';
        $this->duration_days = $package->duration_days;
        
        $this->isUnlimitedClasses = $package->max_classes >= 99999;
        $this->max_classes = $this->isUnlimitedClasses ? 5 : $package->max_classes;
        
        $this->isUnlimitedStudents = $package->max_students_per_class >= 99999;
        $this->max_students_per_class = $this->isUnlimitedStudents ? 50 : $package->max_students_per_class;
        
        $this->max_gps_radius = $package->max_gps_radius > 0 ? $package->max_gps_radius : 100;
        $this->hasImport = (bool) $package->can_export_excel;
    }

    public function save()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $this->validate([
            'plan_tier' => ['required', 'string', 'max:50'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'priceType' => 'required|in:fixed,free,contact',
            'price' => 'nullable|numeric|min:0',
            'duration_days' => 'required|in:30,90,180,365,0',
            'max_classes' => 'nullable|numeric|min:1',
            'max_students_per_class' => 'nullable|numeric|min:1',
            'max_gps_radius' => 'required|numeric|min:10',
        ]);

        $finalPrice = $this->priceType === 'free' ? 0 : ($this->priceType === 'contact' ? 0 : $this->price);
        $finalMaxClasses = $this->isUnlimitedClasses ? 999999 : ($this->max_classes ?: 1);
        $finalMaxStudents = $this->isUnlimitedStudents ? 999999 : ($this->max_students_per_class ?: 1);
        $this->package->update([
            'plan_tier' => $this->plan_tier,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $finalPrice,
            'duration_days' => (int) $this->duration_days,
        ]);

        $this->package->config()->updateOrCreate(
            ['plan_id' => $this->package->id],
            [
                'max_classes' => $finalMaxClasses,
                'max_students_per_class' => $finalMaxStudents,
                'max_gps_radius' => $this->max_gps_radius,
                'can_export_excel' => $this->hasImport,
            ]
        );

        session()->flash('success', 'Cập nhật gói dịch vụ thành công.');
        return redirect()->route('admin.packages.index');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        return view('livewire.admin.packages.package-edit')->title('Sửa gói dịch vụ');
    }
}
