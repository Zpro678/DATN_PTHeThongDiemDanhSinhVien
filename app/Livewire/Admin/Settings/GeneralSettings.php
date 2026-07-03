<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $app_name = '';
    public $app_logo_path = '';
    public $new_logo;
    public $max_upload_size = 10;

    public function mount()
    {
        $this->app_name = Setting::get('app_name', 'Attendia Tech');
        $this->app_logo_path = Setting::get('app_logo', '');
        $this->max_upload_size = Setting::get('max_upload_size', 10);
    }

    protected $rules = [
        'app_name' => 'required|string|max:255',
        'new_logo' => 'nullable|image|max:2048', // 2MB Max
        'max_upload_size' => 'required|numeric|min:1|max:100',
    ];

    public function save()
    {
        $this->validate();

        if ($this->new_logo) {
            // Delete old logo if exists and not default
            if ($this->app_logo_path && Storage::disk('public')->exists($this->app_logo_path)) {
                Storage::disk('public')->delete($this->app_logo_path);
            }
            
            $path = $this->new_logo->store('settings', 'public');
            Setting::set('app_logo', $path);
            $this->app_logo_path = $path;
            
            // Clear the temporary file
            $this->new_logo = null;
        }

        Setting::set('app_name', $this->app_name);
        Setting::set('max_upload_size', $this->max_upload_size);

        session()->flash('general_success', 'Đã lưu cấu hình Giao diện & Tải lên.');
        
        // Refresh page to apply global changes (like app name in layout)
        return redirect()->route('admin.settings.index');
    }

    public function render()
    {
        return view('livewire.admin.settings.general-settings');
    }
}
