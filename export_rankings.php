<?php
// 自定义自动加载函数
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = __DIR__ . '/PhpSpreadsheet/src/';

    // 检查类名是否以指定的前缀开头
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) === 0) {
        // 获取相对类名
        $relative_class = substr($class, $len);
        // 替换命名空间分隔符为目录分隔符，并添加 .php 扩展名
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // 如果文件存在，则引入该文件
        if (file_exists($file)) {
            require $file;
        }
    }
});

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// 假设这里是从数据库获取排名数据的代码
// 这里简单模拟一些数据
$rankings = [
    ['排名', '姓名', '分数'],
    [1, '张三', 90],
    [2, '李四', 85],
    [3, '王五', 80]
];

// 创建一个新的 Spreadsheet 对象
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 设置表头样式
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFCCCCCC',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// 写入表头
foreach ($rankings[0] as $colIndex => $header) {
    $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
    $sheet->getStyleByColumnAndRow($colIndex + 1, 1)->applyFromArray($headerStyle);
}

// 写入数据
for ($i = 1; $i < count($rankings); $i++) {
    foreach ($rankings[$i] as $colIndex => $value) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, $i + 1, $value);
        $sheet->getStyleByColumnAndRow($colIndex + 1, $i + 1)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyleByColumnAndRow($colIndex + 1, $i + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

// 自动调整列宽
foreach (range('A', $sheet->getHighestDataColumn()) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 创建一个 Xlsx 写入器
$writer = new Xlsx($spreadsheet);

// 设置响应头，以便浏览器下载文件
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="rankings.xlsx"');
header('Cache-Control: max-age=0');

// 输出文件到浏览器
$writer->save('php://output');