<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportTemplateBasicSheet implements FromArray, WithTitle, WithHeadings
{
    public function title(): string
    {
        return 'Cơ bản';
    }

    public function headings(): array
    {
        return [
            'Họ và tên',
            'Email'
        ];
    }

    public function array(): array
    {
        return [
            ['Nguyễn Tuấn An', 'annt@gmail.com'],
            ['Trần Thị Bích', 'bichttt@gmail.com'],
            ['Lê Văn Cường', 'cuonglv@gmail.com'],
        ];
    }
}
