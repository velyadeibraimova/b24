<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Создаем новый Spreadsheet объект
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Записываем данные в ячейки
$sheet->setCellValue('A1', 'Hello');
$sheet->setCellValue('B1', 'World!');
$sheet->setCellValue('A2', 'Тест PHPSpreadsheet');
$sheet->setCellValue('B2', 'Успешно!');

// Устанавливаем стили (опционально)
$sheet->getStyle('A1:B1')->getFont()->setBold(true);
$sheet->getStyle('A1:B2')->getAlignment()->setHorizontal('center');

// Автоматическое выравнивание ширины столбцов
foreach (range('A', 'B') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Сохраняем файл
$writer = new Xlsx($spreadsheet);
$filename = 'test_excel_' . date('Y-m-d') . '.xlsx';
$writer->save($filename);

echo "Файл Excel создан: <a href='$filename'>$filename</a>";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>