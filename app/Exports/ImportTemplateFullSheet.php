<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ImportTemplateFullSheet implements FromArray, WithTitle, WithHeadings
{
    public function title(): string
    {
        return 'Đầy đủ';
    }

    public function headings(): array
    {
        return [
            'Họ và tên',
            'Email',
            '22/06',
            '23/06',
            '24/06',
            '',
            'Chú thích ký hiệu:',
            'c = Có mặt',
            'm = Đi muộn',
            'v = Vắng không phép',
            'p = Vắng có phép'
        ];
    }

    public function array(): array
    {
        return [
            ['Nguyễn Tuấn An', 'annt@gmail.com', 'c', 'm', 'c'],
            ['Trần Thị Bích', 'bichttt@gmail.com', 'v', 'c', 'v'],
            ['Lê Văn Cường', 'cuonglv@gmail.com', 'c', 'v', 'p'],
        ];
    }
}
