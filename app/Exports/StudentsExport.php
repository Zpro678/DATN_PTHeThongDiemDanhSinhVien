<?php

namespace App\Exports;

use App\Models\CourseClass;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsExport implements WithMultipleSheets
{
    use Exportable;

    private string $classFilter;
    private string $statusFilter;
    private string $search;
    private int $ownerUserId;
    private string $formula;

    public function __construct(int $ownerUserId, string $classFilter, string $statusFilter, string $search, string $formula)
    {
        $this->ownerUserId  = $ownerUserId;
        $this->classFilter  = $classFilter;
        $this->statusFilter = $statusFilter;
        $this->search       = $search;
        $this->formula      = $formula;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->classFilter !== 'all') {
            $sheets[] = new StudentsSheet($this->ownerUserId, $this->classFilter, $this->statusFilter, $this->search, $this->formula);
        } else {
            $classes = CourseClass::managedBy($this->ownerUserId)->get();
            if ($classes->isEmpty()) {
                $sheets[] = new StudentsSheet($this->ownerUserId, 'all', $this->statusFilter, $this->search, $this->formula);
            } else {
                foreach ($classes as $class) {
                    $sheets[] = new StudentsSheet($this->ownerUserId, (string) $class->id, $this->statusFilter, $this->search, $this->formula);
                }
            }
        }

        // Thêm sheet chú thích ký hiệu (giống file mẫu)
        $sheets[] = new LegendSheet();

        return $sheets;
    }
}
