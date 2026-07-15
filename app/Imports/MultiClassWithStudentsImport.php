<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MultiClassWithStudentsImport implements WithMultipleSheets
{
    protected int $authUserId;
    protected string $importToken;
    protected array $sheetNames = [];

    public function __construct(string $filePath, int $authUserId, string $importToken)
    {
        $this->authUserId = $authUserId;
        $this->importToken = $importToken;

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $this->sheetNames = $spreadsheet->getSheetNames();
        } catch (\Exception $e) {
            // Ghi nhận lỗi tải file nếu có
            \App\Models\ImportError::create([
                'import_token' => $this->importToken,
                'error_message' => "Không thể đọc các sheet trong file Excel: " . $e->getMessage()
            ]);
            $this->sheetNames = [];
        }
    }

    public function sheets(): array
    {
        $sheets = [];
        foreach ($this->sheetNames as $name) {
            // Bỏ qua các sheet chú thích/legend nếu có
            if (in_array(mb_strtolower(trim($name)), ['chú thích', 'chu thich', 'legend', 'readme', 'hướng dẫn', 'huong dan'])) {
                continue;
            }
            $sheets[$name] = new SingleClassWithStudentsImport($name, $this->authUserId, $this->importToken);
        }
        return $sheets;
    }
}
