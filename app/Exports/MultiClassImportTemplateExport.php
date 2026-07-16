<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiClassImportTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ClassImportTemplateSheet('Lớp Lập trình Web', [
                ['Nguyễn Văn An', 'an.nv@gmail.com', 'c', 'm'],
                ['Trần Thị Bích', 'bich.tt@gmail.com', 'v', 'p'],
                ['Lê Văn Cường', 'cuong.lv@gmail.com', 'c', 'c'],
            ]),
            new ClassImportTemplateSheet('Lớp Cơ sở dữ liệu', [
                ['Phạm Minh Đức', 'duc.pm@gmail.com', 'c', 'c'],
                ['Hoàng Lan Anh', 'anh.hl@gmail.com', 'm', 'v'],
                ['Vũ Việt Bách', 'bach.vv@gmail.com', 'c', 'p'],
            ]),
        ];
    }
}
