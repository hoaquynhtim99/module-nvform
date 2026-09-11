<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate 12/5/2012 11:29
 */

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

use NukeViet\Files\Upload as NvUpload;

foreach ($question_info as $row_f) {
    $old_value = '';
    $value = (isset($answer_info[$row_f['qid']])) ? $answer_info[$row_f['qid']] : '';

    if ($filled) {
        $old_value = (isset($old_answer_info[$row_f['qid']])) ? $old_answer_info[$row_f['qid']] : '';
    }

    if ($row_f['question_type'] == 'file') {
        $question_choices = unserialize($row_f['question_choices']);
        if (!is_array($question_choices) or !isset($question_choices['type'], $question_choices['ext'])) {
            $answer_info[$row_f['qid']] = $old_value;
            continue;
        }
        // Dữ liệu trước đây không có multi_num nên thêm vào = 1
        if (!isset($question_choices['multi_num'])) {
            $question_choices['multi_num'] = 1;
        }

        $input_files = [];
        if ($question_choices['multi_num'] > 1) {
            if (!empty($_FILES['question_file_' . $row_f['qid']]) and !empty($_FILES['question_file_' . $row_f['qid']]['tmp_name']) and is_array($_FILES['question_file_' . $row_f['qid']]['tmp_name'])) {
                foreach ($_FILES['question_file_' . $row_f['qid']]['tmp_name'] as $_key => $_tmp_name) {
                    if (is_uploaded_file($_tmp_name) and !empty($_FILES['question_file_' . $row_f['qid']]['name'][$_key]) and !empty($_FILES['question_file_' . $row_f['qid']]['size'][$_key])) {
                        $input_files[] = [
                            'name' => $_FILES['question_file_' . $row_f['qid']]['name'][$_key],
                            'full_path' => isset($_FILES['question_file_' . $row_f['qid']]['full_path'][$_key]) ? $_FILES['question_file_' . $row_f['qid']]['full_path'][$_key] : '',
                            'type' => isset($_FILES['question_file_' . $row_f['qid']]['type'][$_key]) ? $_FILES['question_file_' . $row_f['qid']]['type'][$_key] : '',
                            'tmp_name' => $_tmp_name,
                            'error' => isset($_FILES['question_file_' . $row_f['qid']]['error'][$_key]) ? $_FILES['question_file_' . $row_f['qid']]['error'][$_key] : 0,
                            'size' => $_FILES['question_file_' . $row_f['qid']]['size'][$_key],
                        ];
                    }
                }
            }
        } elseif (!empty($_FILES['question_file_' . $row_f['qid']]) and is_uploaded_file($_FILES['question_file_' . $row_f['qid']]['tmp_name'])) {
            $input_files = [$_FILES['question_file_' . $row_f['qid']]];
        }
        if (empty($input_files)) {
            $answer_info[$row_f['qid']] = $old_value;
            continue;
        }

        $folder = 'form_' . $row_f['fid'];
        if (!file_exists(NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $folder)) {
            nv_mkdir(NV_UPLOADS_REAL_DIR . '/' . $module_upload, $folder);
        }

        // Bắt lỗi từng file, ít nhất 1 file upload được thì lấy
        $_values = [];
        $_error = '';

        foreach ($input_files as $input_file) {
            $upload = new NvUpload(explode(',', $question_choices['type']), $question_choices['ext'], $global_config['forbid_mimes'], $row_f['max_length'], NV_MAX_WIDTH, NV_MAX_HEIGHT);
            $upload->setLanguage($lang_global);
            $upload_info = $upload->save_file($input_file, NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $folder, false);

            @unlink($input_file['tmp_name']);

            if (empty($upload_info['error'])) {
                mt_srand((float) microtime() * 1000000);
                $maxran = 1000000;
                $random_num = mt_rand(0, $maxran);
                $random_num = md5($random_num);
                $nv_pathinfo_filename = nv_pathinfo_filename($upload_info['name']);
                $new_name = NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $folder . '/' . $nv_pathinfo_filename . '.' . $random_num . '.' . $upload_info['ext'];

                $rename = nv_renamefile($upload_info['name'], $new_name);

                if ($rename[0] == 1) {
                    $_value = $new_name;
                } else {
                    $_value = $upload_info['name'];
                }

                @chmod($_value, 0644);
                $_value = str_replace(NV_ROOTDIR . '/' . NV_UPLOADS_DIR . '/' . $module_upload . '/', '', $_value);
                $_values[] = $_value;
            } else {
                $_error = $upload_info['error'];
            }
        }
        $value = empty($_values) ? $old_value : implode(',', $_values);
        if (empty($_values) and $_error) {
            $error = $_error;
        } else {
            // Upload thành công thì xóa file cũ nếu có
            // Chỗ này chưa tối ưu khi các input khác có lỗi thì lại xóa mất file cũ cần tối ưu thêm
            if (!empty($old_value)) {
                $old_values = explode(',', $old_value);
                foreach ($old_values as $_old_value) {
                    if (file_exists(NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $_old_value)) {
                        nv_deletefile(NV_UPLOADS_REAL_DIR . '/' . $module_upload . '/' . $_old_value);
                    }
                }
            }
        }

        $lang_module['field_match_type_required'] = $lang_module['field_file_required'];
    }

    if ($value != '') {
        if ($row_f['question_type'] == 'number') {
            $number_type = unserialize($row_f['question_choices']);
            $number_type = $number_type['number_type'];
            $pattern = ($number_type == 1) ? "/^[0-9]+$/" : "/^[0-9\.]+$/";

            if (!preg_match($pattern, $value)) {
                $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
            } else {
                $value = ($number_type == 1) ? intval($value) : floatval($value);

                if ($value < $row_f['min_length'] or $value > $row_f['max_length']) {
                    $error = sprintf($lang_module['field_min_max_value'], $row_f['title'], $row_f['min_length'], $row_f['max_length']);
                }
            }
        } elseif ($row_f['question_type'] == 'date') {
            if (preg_match("/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/", $value, $m)) {
                $value = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
                if ($value < $row_f['min_length'] or $value > $row_f['max_length']) {
                    $error = sprintf($lang_module['field_min_max_value'], $row_f['title'], date('d/m/Y', $row_f['min_length']), date('d/m/Y', $row_f['max_length']));
                }
            } else {
                $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
            }
        } elseif ($row_f['question_type'] == 'time') {
            if (preg_match("/^([0-9]{1,2})\:([0-9]{1,2})$/", $value, $m)) {
                $value = mktime($m[1], $m[2], 0, 0, 0, 0);
            } else {
                $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
            }
        } elseif ($row_f['question_type'] == 'textbox') {
            if ($row_f['match_type'] == 'alphanumeric') {
                if (!preg_match("/^[a-zA-Z0-9\_]+$/", $value)) {
                    $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                }
            } elseif ($row_f['match_type'] == 'email') {
                $error = nv_check_valid_email($value);
            } elseif ($row_f['match_type'] == 'url') {
                if (!nv_is_url($value)) {
                    $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                }
            } elseif ($row_f['match_type'] == 'regex') {
                if (!preg_match("/" . $row_f['match_regex'] . "/", $value)) {
                    $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                }
            } elseif ($row_f['match_type'] == 'callback') {
                if (function_exists($row_f['func_callback'])) {
                    if (!call_user_func($row_f['func_callback'], $value)) {
                        $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                    }
                } else {
                    $error = "error function not exists " . $row_f['func_callback'];
                }
            } else {
                $value = nv_htmlspecialchars($value);
            }

            $strlen = nv_strlen($value);

            if ($strlen < $row_f['min_length'] or $strlen > $row_f['max_length']) {
                $error = sprintf($lang_module['field_min_max_error'], $row_f['title'], $row_f['min_length'], $row_f['max_length']);
            }
        } elseif ($row_f['question_type'] == 'textarea' or $row_f['question_type'] == 'editor') {
            $allowed_html_tags = array_map("trim", explode(',', NV_ALLOWED_HTML_TAGS));
            $allowed_html_tags = "<" . implode("><", $allowed_html_tags) . ">";
            $value = strip_tags($value, $allowed_html_tags);
            $value = nv_nl2br($value, '<br />');

            if ($row_f['match_type'] == 'regex') {
                if (!preg_match("/" . $row_f['match_regex'] . "/", $value)) {
                    $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                }
            } elseif ($row_f['match_type'] == 'callback') {
                if (function_exists($row_f['func_callback'])) {
                    if (!call_user_func($row_f['func_callback'], $value)) {
                        $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
                    }
                } else {
                    $error = "error function not exists " . $row_f['func_callback'];
                }
            }

            $value = ($row_f['question_type'] == 'textarea') ? nv_nl2br($value, '<br />') : nv_editor_nl2br($value);
            $strlen = nv_strlen($value);

            if ($strlen < $row_f['min_length'] or $strlen > $row_f['max_length']) {
                $error = sprintf($lang_module['field_min_max_error'], $row_f['title'], $row_f['min_length'], $row_f['max_length']);
            }
        } elseif ($row_f['question_type'] == 'checkbox' or $row_f['question_type'] == 'multiselect') {
            $temp_value = array();
            $row_f['question_choices'] = unserialize($row_f['question_choices']);
            foreach ($value as $value_i) {
                if (isset($row_f['question_choices'][$value_i])) {
                    $temp_value[] = $value_i;
                }
            }

            $value = implode(',', $temp_value);
        } elseif ($row_f['question_type'] == 'select' or $row_f['question_type'] == 'radio') {
            $row_f['question_choices'] = unserialize($row_f['question_choices']);
            if (!isset($row_f['question_choices'][$value])) {
                $error = sprintf($lang_module['field_match_type_error'], $row_f['title']);
            }
        }
    }
    $answer_info[$row_f['qid']] = $value;

    $row_f['user_editable'] = $row_f['user_editable'] == -1 ? $form_info['user_editable'] : $row_f['user_editable'];
    if ($filled and !$row_f['user_editable'] and $value != $old_value) {
        $error = sprintf($lang_module['field_no_edit'], $row_f['title']);
    }

    if (empty($value) and $row_f['required']) {
        $error = sprintf($lang_module['field_match_type_required'], $row_f['title']);
    }
}
