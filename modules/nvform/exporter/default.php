<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Tue, 08 Apr 2014 15:13:43 GMT
 */

if (!defined('NV_IS_MOD_NVFORM')) {
    die('Stop!!!');
}

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Section;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\VerticalJc;

$phpWord = new PhpWord();

// Tạo trang dạng khổ dọc
$sectionStyle = [
    'orientation' => Section::ORIENTATION_PORTRAIT,
    'marginTop' => Converter::cmToTwip(2),
    'marginRight' => Converter::cmToTwip(2),
    'marginBottom' => Converter::cmToTwip(2),
    'marginLeft' => Converter::cmToTwip(3),
    'headerHeight' => Converter::cmToTwip(2),
    'footerHeight' => Converter::cmToTwip(2)
];
$section = $phpWord->addSection($sectionStyle);

// Định nghĩa kiểu font, đoạn mặc định
$fontNormal = [
    'name' => 'Times New Roman',
    'size' => 12,
];
$paragraphNormal = [
    'alignment' => Jc::START,
    'lineHeight' => '1.5',
    'spaceBefore' => Converter::pointToTwip(0),
    'spaceAfter' => Converter::pointToTwip(0)
];

// Đoạn trong bảng
$paragraphTableStyle = [
    'alignment' => Jc::START,
    'lineHeight' => '1.2',
    'spaceBefore' => Converter::pointToTwip(0),
    'spaceAfter' => Converter::pointToTwip(0)
];

// Kiểu bảng
$tableStyle = [
    'cellMarginTop' => Converter::pixelToTwip(5),
    'cellMarginRight' => Converter::pixelToTwip(5),
    'cellMarginBottom' => Converter::pixelToTwip(0),
    'cellMarginLeft' => Converter::pixelToTwip(5),
];
$tableCellStyle = [
    'borderColor' => '000000',
    'borderSize' => 1,
    'valign' => VerticalJc::TOP
];

/*
$text = 'Đơn vị';
$font = array_merge($fontNormal, ['bold' => true]);
$section->addText($text, $font, $paragraphNormal);

$text = 'Ngày tháng năm';
$font = array_merge($fontNormal, ['italic' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::END]);
$section->addText($text, $font, $paragraph);

$text = 'Tiêu đề file';
$font = array_merge($fontNormal, ['bold' => true, 'size' => 14]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(0)]);
$section->addText($text, $font, $paragraph);

$text = 'Mô tả dưới tiêu đề';
$font = array_merge($fontNormal, ['italic' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER, 'spaceAfter' => Converter::pointToTwip(12)]);
$section->addText($text, $font, $paragraph);
*/

// Nội dung khảo sát
$table = $section->addTable($tableStyle);

$stt = 0;
foreach ($question_info as $row) {
    $stt++;

    $table->addRow();
    $cellTitle = $table->addCell(Converter::cmToTwip(6), $tableCellStyle);
    $cellData = $table->addCell(Converter::cmToTwip(10), $tableCellStyle);

    $title = standardizeLineBreaks(htmlspecialchars(strip_tags($row['title'])));
    $title = preg_replace("/\n/iu", ' ', $title);
    $title = preg_replace("/\s[\s]+/iu", ' ', $title);

    $cellTitle->addText(number_format($stt, 0, ',', '.') . '. ' . $title, $fontNormal, $paragraphTableStyle);

    $row['value'] = isset($answer_info[$row['qid']]) ? $answer_info[$row['qid']] : $row['default_value'];

    if ($row['question_type'] == 'date') {
        $row['value'] = (empty($row['value'])) ? '' : date('d/m/Y', $row['value']);
    } elseif ($row['question_type'] == 'time') {
        $row['value'] = (empty($row['value'])) ? '' : date('H:i', $row['value']);
    } elseif ($row['question_type'] == 'textarea' or $row['question_type'] == 'editor') {
        $row['value'] = standardizeLineBreaks(htmlspecialchars(strip_tags($row['value'])));
        $row['value'] = preg_replace("/\n/iu", ' ', $row['value']);
        $row['value'] = preg_replace("/\s[\s]+/iu", ' ', $row['value']);
    } else {
        $row['value'] = htmlspecialchars($row['value']);
    }

    $cellData->addText($row['value'], $fontNormal, $paragraphTableStyle);
}

/*
// Phần cuối
$section->addTextBreak();
$table = $section->addTable();
$table->addRow();

$cellLeft = $table->addCell(Converter::cmToTwip(6));

$text = 'LÃNH ĐẠO ĐƠN VỊ';
$font = array_merge($fontNormal, ['bold' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER]);
$cellLeft->addText($text, $font, $paragraph);

$text = '(Ký, ghi rõ họ tên)';
$font = array_merge($fontNormal, ['italic' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER]);
$cellLeft->addText($text, $font, $paragraph);

$cellRight = $table->addCell(Converter::cmToTwip(10));

$text = 'CÔNG CHỨC LẬP PHIẾU';
$font = array_merge($fontNormal, ['bold' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER]);
$cellRight->addText($text, $font, $paragraph);

$text = '(Ký, ghi rõ họ tên)';
$font = array_merge($fontNormal, ['italic' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER]);
$cellRight->addText($text, $font, $paragraph);

$cellRight->addTextBreak(3);

$text = 'Nguyễn Văn A';
$font = array_merge($fontNormal, ['bold' => true]);
$paragraph = array_merge($paragraphNormal, ['alignment' => Jc::CENTER]);
$cellRight->addText($text, $font, $paragraph);
*/
