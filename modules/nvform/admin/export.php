<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES., JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES ., JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Dec 3, 2010 11:33:22 AM
 */

if (!defined('NV_IS_FILE_ADMIN')) {
    die('Stop!!!');
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use NukeViet\Files\Download;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
    $contents = nv_theme_alert($lang_module['report_required_phpexcel_title'], $lang_module['report_required_phpexcel_content'], 'danger');
    nv_htmlOutput($contents);
}

$fid = $nv_Request->get_int('fid', 'get,post', 0);
$form_info = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . ' WHERE id=' . $fid)->fetch();

if ($nv_Request->isset_request('export', 'post, get')) {
    $download = $nv_Request->get_int('download', 'get, post', 1);
    $type = $nv_Request->get_title('type', 'get, post', '');
    $is_zip = $nv_Request->get_int('is_zip', 'get, post', 0);

    if (empty($type)) {
        die('NO');
    }

    $question_data = [];
    $result = $db->query('SELECT * FROM ' . NV_PREFIXLANG . '_' . $module_data . '_question WHERE fid = ' . $fid . ' ORDER BY weight ASC');
    while ($row = $result->fetch()) {
        $question_data[$row['qid']] = $row;
    }

    $answer_data = [];
    $result = $db->query('SELECT t1.*, t2.username, t2.first_name, t2.last_name FROM ' . NV_PREFIXLANG . '_' . $module_data . '_answer t1 LEFT JOIN ' . NV_USERS_GLOBALTABLE . ' t2 ON t1.who_answer = t2.userid WHERE fid = ' . $fid);
    while ($row = $result->fetch()) {
        $answer_data[] = $row;
    }

    $array = [
        'objType' => '',
        'objExt' => ''
    ];
    switch ($type) {
        case 'xlsx':
            $array['objType'] = 'Xlsx';
            $array['objExt'] = 'xlsx';
            break;
        case 'ods':
            $array['objType'] = 'Ods';
            $array['objExt'] = 'ods';
            break;
        case 'pdf':
            $array['objType'] = 'Mpdf';
            $array['objExt'] = 'pdf';
            break;
        default:
            $array['objType'] = 'Csv';
            $array['objExt'] = 'csv';
    }

    $spreadsheet = new Spreadsheet();
    $spreadsheet->setActiveSheetIndex(0);

    // Set properties
    $spreadsheet->getProperties()
        ->setCreator($admin_info['username'])
        ->setLastModifiedBy($admin_info['username'])
        ->setTitle($form_info['title'])
        ->setSubject($form_info['title'])
        ->setDescription($form_info['title'])
        ->setCategory($module_name);

    $columnIndex = 5; // Cot bat dau ghi du lieu
    $rowIndex = 3; // Dong bat dau ghi du lieu

    // Định tiêu đề cột cố định
    $spreadsheet->getActiveSheet()
        ->setCellValue(Coordinate::stringFromColumnIndex(1) . $rowIndex, $lang_module['question_number'])
        ->setCellValue(Coordinate::stringFromColumnIndex(2) . $rowIndex, $lang_module['report_who_answer'])
        ->setCellValue(Coordinate::stringFromColumnIndex(3) . $rowIndex, $lang_module['report_answer_time'])
        ->setCellValue(Coordinate::stringFromColumnIndex(4) . $rowIndex, $lang_module['report_answer_edit_time']);

    // Định tiêu đề cột của các câu hỏi
    $_columnIndex = $columnIndex;
    foreach ($question_data as $question) {
        $TextColumnIndex = Coordinate::stringFromColumnIndex($_columnIndex);
        $spreadsheet->getActiveSheet()->setCellValue($TextColumnIndex . $rowIndex, nv_get_plaintext($question['title']));
        $_columnIndex++;
    }

    // Hien thi cau tra loi
    $i = $rowIndex + 1;
    $number = 1;
    foreach ($answer_data as $answer) {
        $j = $columnIndex;
        $answer['username'] = !$answer['username'] ? $lang_module['report_guest'] : nv_show_name_user($answer['first_name'], $answer['last_name'], $answer['username']);
        $answer['answer_time'] = nv_date('d/m/Y H:i', $answer['answer_time']);
        $answer['answer_edit_time'] = !$answer['answer_edit_time'] ? 'N/A' : nv_date('d/m/Y H:i', $answer['answer_edit_time']);

        $col = Coordinate::stringFromColumnIndex(1);
        $CellValue = $number;
        $spreadsheet->getActiveSheet()->setCellValue($col . $i, $CellValue);

        $col = Coordinate::stringFromColumnIndex(2);
        $CellValue = nv_unhtmlspecialchars($answer['username']);
        $spreadsheet->getActiveSheet()->setCellValue($col . $i, $CellValue);

        $col = Coordinate::stringFromColumnIndex(3);
        $CellValue = nv_unhtmlspecialchars($answer['answer_time']);
        $spreadsheet->getActiveSheet()->setCellValue($col . $i, $CellValue);

        $col = Coordinate::stringFromColumnIndex(4);
        $CellValue = nv_unhtmlspecialchars($answer['answer_edit_time']);
        $spreadsheet->getActiveSheet()->setCellValue($col . $i, $CellValue);

        $number++;

        $answer['answer'] = unserialize($answer['answer']);
        foreach ($answer['answer'] as $qid => $ans) {
            if (isset($question_data[$qid])) {
                $question_type = $question_data[$qid]['question_type'];
                $auto_datatype = true;

                if ($question_type == 'multiselect' or $question_type == 'select' or $question_type == 'radio' or $question_type == 'checkbox') {
                    $data = unserialize($question_data[$qid]['question_choices']);
                    if ($question_type == 'checkbox') {
                        $result = explode(',', $ans);
                        $ans = '';
                        foreach ($result as $key) {
                            if (isset($data[$key])) {
                                $ans .= $data[$key] . "\n";
                            }
                        }
                    } elseif (isset($data[$ans])) {
                        $ans = $data[$ans];
                    } else {
                        $ans = '';
                    }
                } elseif ($question_type == 'date' and !empty($ans)) {
                    $ans = nv_date('d/m/Y', $ans);
                } elseif ($question_type == 'time' and !empty($ans)) {
                    $ans = nv_date('H:i', $ans);
                } elseif ($question_type == 'grid') {
                    $data = unserialize($question_data[$qid]['question_choices']);
                    $result = explode('||', $ans);
                    foreach ($data['col'] as $col) {
                        if ($result[0] == $col['key']) {
                            $ans = $col['value'];
                            break;
                        }
                    }
                    foreach ($data['row'] as $row) {
                        if ($result[1] == $row['key']) {
                            $ans .= ' - ' . $col['value'];
                            break;
                        }
                    }
                } elseif ($question_type == 'grid_row') {
                    $data = unserialize($question_data[$qid]['question_choices']);
                    $result = explode('||', $ans);
                    foreach ($data['col'] as $col) {
                        if ($result[0] == $col['key']) {
                            $ans = $col['value'];
                        }
                    }
                    foreach ($data['row'] as $row) {
                        if ($result[1] == $row['key']) {
                            $ans .= ' - ' . $col['value'] . '<br />';
                        }
                    }
                } elseif ($question_type == 'file' and file_exists(NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $ans)) {
                    $ans = '';
                } elseif ($question_type == 'table') {
                    //
                    $ans = '';
                } else {
                    $auto_datatype = true;
                }
            } else {
                $ans = '';
            }

            $col = Coordinate::stringFromColumnIndex($j);
            if ($auto_datatype) {
                $spreadsheet->getActiveSheet()->setCellValue($col . $i, trim($ans));
            } else {
                $spreadsheet->getActiveSheet()->setCellValueExplicit($col . $i, trim($ans), DataType::TYPE_STRING);
            }
            $j++;
        }
        $i++;
    }

    $highestRow = $i - 1;
    $highestColumn = Coordinate::stringFromColumnIndex($j - 1);

    // Rename sheet
    $spreadsheet->getActiveSheet()->setTitle('Sheet 1');

    // Set page orientation and size
    $spreadsheet->getActiveSheet()
        ->getPageSetup()
        ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $spreadsheet->getActiveSheet()
        ->getPageSetup()
        ->setPaperSize(PageSetup::PAPERSIZE_A4);

    // Excel title
    $spreadsheet->getActiveSheet()->mergeCells('A2:' . $highestColumn . '2');
    $spreadsheet->getActiveSheet()->setCellValue('A2', nv_strtoupper($form_info['title']));
    $spreadsheet->getActiveSheet()
        ->getStyle('A2')
        ->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $spreadsheet->getActiveSheet()
        ->getStyle('A2')
        ->getAlignment()
        ->setVertical(Alignment::VERTICAL_CENTER);

    // Định kích thước chữ
    $spreadsheet->getActiveSheet()
        ->getStyle("A1:" . $highestColumn . $highestRow)
        ->getFont()
        ->setSize(12);

    // Tự động căn độ rộng các cột
    $numberCols = Coordinate::rangeDimension('A1:' . $highestColumn . '1')[0];
    for ($i = 1; $i <= $numberCols; $i++) {
        $spreadsheet->getActiveSheet()
        ->getColumnDimension(Coordinate::stringFromColumnIndex($i))
            ->setAutoSize(true);
    }

    // Kẻ viền xung quanh nội dung
    $styleArray = [
        'borders' => [
            'outline' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
    ];
    $spreadsheet->getActiveSheet()
        ->getStyle("A3:" . $highestColumn . $highestRow)
        ->applyFromArray($styleArray);

    $spreadsheet->getActiveSheet()
        ->getStyle("A4:" . $highestColumn . $highestRow)
        ->getAlignment()->setWrapText(true);

    // Cho in đậm các cột tiêu đề
    $spreadsheet->getActiveSheet()
        ->getStyle("A3:" . $highestColumn . '3')
        ->getFont()->setBold(true);

    $objWriter = IOFactory::createWriter($spreadsheet, $array['objType']);
    $file_src = NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . $form_info['alias'] . '.' . $array['objExt'];
    $objWriter->save($file_src);

    if (!$download and file_exists($file_src)) {
        die('OK_' . str_replace(NV_ROOTDIR . NV_BASE_SITEURL, '', $file_src));
    }

    if (!$is_zip) {
        $download = new Download($file_src, NV_ROOTDIR . '/' . NV_TEMP_DIR);
        $download->download_file();
        die('OK');
    } else {
        $arry_file_zip = [];
        if (file_exists($file_src)) {
            $arry_file_zip[] = $file_src;
        }

        $file_src = NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . NV_TEMPNAM_PREFIX . change_alias($lang_module['report_ex']) . '_' . md5(nv_genpass(10) . session_id()) . '.zip';
        $zip = new PclZip($file_src);
        $zip->create($arry_file_zip, PCLZIP_OPT_REMOVE_PATH, NV_ROOTDIR . "/" . NV_TEMP_DIR);
        $filesize = @filesize($file_src);

        foreach ($arry_file_zip as $file) {
            nv_deletefile($file);
        }

        // Download file
        $download = new Download($file_src, NV_ROOTDIR . "/" . NV_TEMP_DIR, $form_info['alias'] . ".zip");
        $download->download_file();
        exit();
    }
}

$xtpl = new XTemplate('export.tpl', NV_ROOTDIR . '/themes/' . $global_config['module_theme'] . '/modules/' . $module_file);
$xtpl->assign('LANG', $lang_module);
$xtpl->assign('MODULE_NAME', $module_name);
$xtpl->assign('OP', $op);
$xtpl->assign('FID', $fid);

$default = 'xlsx';
$array_type = [
    'xlsx' => 'Microsoft Excel (XLSX)',
    'csv' => 'Comma-separated values (CSV)',
    'ods' => 'LibreOffice Calc (ODS)',
    'pdf' => 'PDF'
];

foreach ($array_type as $key => $value) {
    $ck = $key == $default ? 'checked="checked"' : '';
    $xtpl->assign('TYPE', [
        'key' => $key,
        'value' => $value,
        'checked' => $ck
    ]);
    $xtpl->parse('main.export.type');
}
$xtpl->parse('main.export');

$xtpl->parse('main');
$contents = $xtpl->text('main');

include NV_ROOTDIR . '/includes/header.php';
echo $contents;
include NV_ROOTDIR . '/includes/footer.php';
