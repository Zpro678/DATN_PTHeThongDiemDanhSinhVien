<?php

namespace App\Livewire;

use Livewire\Component;

class DateRangePicker extends Component
{
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = '01/06/2026';
        $this->endDate = '15/06/2026';
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
