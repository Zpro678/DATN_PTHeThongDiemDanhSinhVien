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
            'Mã SV',
            'Họ và tên',
            'Email'
        ];
    }

    public function array(): array
    {
        return [
            ['CT030101', 'Nguyễn Tuấn An', 'annt@gmail.com'],
            ['CT030102', 'Trần Thị Bích', 'bichttt@gmail.com'],
            ['CT030103', 'Lê Văn Cường', 'cuonglv@gmail.com'],
        ];
    }
}
