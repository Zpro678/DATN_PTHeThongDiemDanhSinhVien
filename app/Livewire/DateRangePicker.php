<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class DateRangePicker extends Component
{
    public $startDate;
    public $endDate;

    public function mount()
    {
        $now = Carbon::now();
    $this->startDate = $now->copy()->startOfMonth()->format('d/m/Y');
    $this->endDate = $now->copy()->endOfMonth()->format('d/m/Y');
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'startDate' || $propertyName === 'endDate') {
            $this->dispatch('dateRangeUpdated', [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate
            ]);
        }
    }

    public function render()
    {
        return view('livewire.date-range-picker');
    }
}
