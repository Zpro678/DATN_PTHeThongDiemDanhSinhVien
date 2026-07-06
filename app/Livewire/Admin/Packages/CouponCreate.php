<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Coupon;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CouponCreate extends Component
{
    public $code = '';
    public $applicable_plan_id = '';
    public $type = 'PERCENT';
    public $value = '';
    public $usage_limit = '';
    public $valid_from = '';
    public $valid_until = '';
    public $is_active = true;

    protected function rules()
    {
        return [
            'code' => 'required|string|max:50|unique:coupons,code',
            'applicable_plan_id' => 'nullable|exists:plans,id',
            'type' => 'required|in:PERCENT,FIXED',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ];
    }

    protected function messages()
    {
        return [
            'code.required' => 'Vui lòng nhập mã giảm giá.',
            'code.unique' => 'Mã giảm giá này đã tồn tại trên hệ thống.',
            'code.max' => 'Mã giảm giá không được vượt quá 50 ký tự.',
            'type.required' => 'Vui lòng chọn loại giảm giá.',
            'value.required' => 'Vui lòng nhập giá trị giảm.',
            'value.numeric' => 'Giá trị giảm phải là một số.',
            'value.min' => 'Giá trị giảm không được nhỏ hơn 0.',
            'valid_until.after_or_equal' => 'Ngày kết thúc (Đến ngày) phải sau hoặc bằng ngày bắt đầu (Từ ngày).',
            'usage_limit.integer' => 'Giới hạn lượt dùng phải là một số nguyên.',
            'usage_limit.min' => 'Giới hạn lượt dùng tối thiểu là 1.',
            'valid_from.date' => 'Ngày bắt đầu không hợp lệ.',
            'valid_until.date' => 'Ngày kết thúc không hợp lệ.',
        ];
    }

    public function generateRandomCode()
    {
        $this->code = strtoupper(\Illuminate\Support\Str::random(10));
    }

    public function save()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'applicable_plan_id' => $this->applicable_plan_id ?: null,
            'type' => $this->type,
            'value' => $this->value,
            'usage_limit' => $this->usage_limit ?: null,
            'valid_from' => $this->valid_from ?: null,
            'valid_until' => $this->valid_until ?: null,
            'is_active' => $this->is_active,
            'created_at' => now(),
        ];

        Coupon::insert($data);

        return redirect()->route('admin.packages.coupons.index', ['ma_user' => auth()->user()->id])
                         ->with('success', 'Đã tạo mã giảm giá thành công!');
    }

    #[Layout('components.admin-layout')]
    public function render()
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        
        $plans = Plan::all();
        
        return view('livewire.admin.packages.coupon-create', [
            'plans' => $plans,
        ])->title('Thêm mới Mã giảm giá');
    }
}
