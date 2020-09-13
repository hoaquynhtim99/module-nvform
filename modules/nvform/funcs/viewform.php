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

use PhpOffice\PhpWord\IOFactory;
use NukeViet\Files\Download;

$form_info = $db->query("SELECT * FROM " . NV_PREFIXLANG . '_' . $module_data . " WHERE id = " . intval($fid))->fetch();
if (empty($form_info)) {
    nv_theme_nvform_alert($form_info['title'], $lang_module['error_form_not_found_detail']);
}

// Kiểm tra trạng thái biểu mẫu
// Trạng thái hoạt động
if ($form_info['status'] == 0 or ($form_info['status'] == 2 and !defined('NV_IS_MODADMIN'))) {
    nv_theme_nvform_alert($form_info['title'], $lang_module['error_form_not_status_detail']);
}

// Thời gian hoạt động
if ($form_info['start_time'] > NV_CURRENTTIME) {
    $start_time = date("d/m/Y H:i", $form_info['start_time']);
    nv_theme_nvform_alert($form_info['title'], sprintf($lang_module['error_form_not_start'], $start_time));
}

// Thời gian kết thúc
if (!empty($form_info['end_time']) and $form_info['end_time'] < NV_CURRENTTIME) {
    $end_time = date("d/m/Y H:i", $form_info['end_time']);
    nv_theme_nvform_alert($form_info['title'], sprintf($lang_module['error_form_closed'], $end_time));
}

// Kiểm tra quyền truy cập
if (!nv_user_in_groups($form_info['groups_view'])) {
    nv_theme_nvform_alert($form_info['title'], $lang_module['error_form_not_premission_detail'], 'warning');
}

// Lấy các câu hỏi
$question_info = $db->query("SELECT * FROM " . NV_PREFIXLANG . '_' . $module_data . "_question WHERE fid = " . $fid . " AND status = 1 ORDER BY weight")->fetchAll();

$info = '';
$filled = false;
$answer_info = $answer_info_extend = $old_answer_info = $old_answer_info_extend = $session_data = [];
$embed = $nv_Request->isset_request('embed', 'get');
$answer_id = 0;

// Trạng thái trả lời
$form_info['filled'] = false;
$form_info['link'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $form_info['alias'] . '-' . $form_info['id'] . $global_config['rewrite_exturl'];
$form_info['link_export'] = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $form_info['alias'] . '-' . $form_info['id'] . $global_config['rewrite_exturl'] . '&amp;export=1';

if (defined('NV_IS_USER')) {
    // Là thành viên thì lấy trực tiếp
    $sql = "SELECT * FROM " . NV_PREFIXLANG . '_' . $module_data . "_answer WHERE fid = " . $fid . " AND who_answer = " . $user_info['userid'];
    $_rows = $db->query($sql)->fetch();

    if ($_rows) {
        $filled = true;
        $form_info['filled'] = true;
        $answer_info = unserialize($_rows['answer']);
        $answer_info_extend = unserialize($_rows['answer_extend']);
        $answer_id = $_rows['id'];
    }
} else {
    // Không là thành viên thì kiểm tra session
    $session_data = $nv_Request->get_title($module_data . '_answer', 'session', '');
    $session_data = json_decode($crypt->decrypt($session_data), true);
    if (!is_array($session_data)) {
        $session_data = [];
    }
    if (isset($session_data[$form_info['id']]) and is_array($session_data[$form_info['id']]) and !empty($session_data[$form_info['id']]['keycode']) and !empty($session_data[$form_info['id']]['data'])) {
        $sql = "SELECT * FROM " . NV_PREFIXLANG . '_' . $module_data . "_answer
        WHERE fid = " . $fid . " AND answer_code = " . $db->quote($session_data[$form_info['id']]['keycode']);
        $_rows = $db->query($sql)->fetch();

        if (!empty($_rows)) {
            $check_data = json_decode($crypt->decrypt($session_data[$form_info['id']]['data'], $_rows['secret_code']), true);
            if (
                is_array($check_data) and !empty($check_data['time']) and
                !empty($check_data['ip']) and !empty($check_data['agent']) and
                $check_data['time'] == $_rows['answer_time'] and $check_data['ip'] == $_rows['answer_ip'] and
                $check_data['agent'] == $_rows['answer_agent']
            ) {
                $filled = true;
                $form_info['filled'] = true;
                $answer_info = unserialize($_rows['answer']);
                $answer_info_extend = unserialize($_rows['answer_extend']);
                $answer_id = $_rows['id'];
            }
        }
    }
}

// Xuất kết quả ra Word
if ($nv_Request->isset_request('export', 'get')) {
    // Chưa trả lời thì có gì đâu mà xuất
    if (!$form_info['filled']) {
        nv_redirect_location($form_info['link']);
    }
    // Chưa cài đặt thư viện thì không xuất được
    if (!class_exists('PhpOffice\PhpWord\PhpWord')) {
        nv_redirect_location($form_info['link']);
    }

    // Xác định trình xuất kết quả
    $exporter_file = NV_ROOTDIR . '/modules/' . $module_file . '/exporter/default.php';
    if (!empty($form_info['export_handler']) and $form_info['export_handler'] != 'default' and file_exists(NV_ROOTDIR . '/modules/' . $module_file . '/exporter/' . $form_info['export_handler'] . '.php')) {
        $exporter_file = NV_ROOTDIR . '/modules/' . $module_file . '/exporter/' . $form_info['export_handler'] . '.php';
    }
    require $exporter_file;

    $filepath = NV_ROOTDIR . '/' . NV_TEMP_DIR . '/' . NV_TEMPNAM_PREFIX . 'export' . $answer_id . '_' . NV_CHECK_SESSION;
    if (file_exists($filepath)) {
        nv_deletefile($filepath);
    }

    $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
    $objWriter->save($filepath);
    if (!file_exists($filepath)) {
        $contents = nv_theme_alert($lang_module['error'], $lang_module['error_savefile'], 'danger');
        include NV_ROOTDIR . '/includes/header.php';
        echo nv_admin_theme($contents);
        include NV_ROOTDIR . '/includes/footer.php';
    }

    $filename = substr(strtolower($form_info['alias']), 0, 100) . '.docx';
    $download = new Download($filepath, NV_ROOTDIR . '/' . NV_TEMP_DIR, $filename);
    $download->download_file();
    die();
}

// Gửi dữ liệu lên
if ($nv_Request->isset_request('submit', 'post')) {
    $error = '';

    if ($filled) {
        $old_answer_info = $answer_info;
        $old_answer_info_extend = $answer_info_extend;
    }

    $answer_info = $nv_Request->get_array('question', 'post');
    $answer_info_extend = $nv_Request->get_array('question_extend', 'post', []);

    require NV_ROOTDIR . '/modules/' . $module_file . '/form.check.php';

    if (empty($error)) {
        $userid = !defined('NV_IS_USER') ? 0 : $user_info['userid'];
        $answer_info['answer_time'] = $answer_info['answer_edit_time'] = NV_CURRENTTIME;

        if ($filled) {
            $sql = "UPDATE " . NV_PREFIXLANG . '_' . $module_data . "_answer SET
                answer = :answer, answer_extend = :answer_extend,
                answer_edit_time = " . $answer_info['answer_edit_time'] . "
            WHERE id=" . $answer_id;
        } else {
            if (empty($userid)) {
                // Tạo mã bí mật để set session cho câu trả lời khi khách trả lời
                $secret_code = nv_genpass(32);
                $answer_code = nv_genpass(32);
            } else {
                // Thành viên trả lời lần đầu không cần mã bí mật
                $secret_code = '';
                $answer_code = '';
            }

            $sql = "INSERT INTO " . NV_PREFIXLANG . '_' . $module_data . "_answer (
                fid, answer, answer_extend, who_answer, answer_time, answer_ip, answer_agent, secret_code, answer_code
            ) VALUES (
                " . $fid . ", :answer, :answer_extend, " . $userid . ", " . $answer_info['answer_time'] . ",
                " . $db->quote(NV_CLIENT_IP) . ", " . $db->quote(NV_USER_AGENT) . ", " . $db->quote($secret_code) . ", " . $db->quote($answer_code) . "
            )";
        }

        $_answer_info = serialize($answer_info);
        $_answer_info_extend = serialize($answer_info_extend);
        $sth = $db->prepare($sql);
        $sth->bindParam(':answer', $_answer_info, PDO::PARAM_STR);
        $sth->bindParam(':answer_extend', $_answer_info_extend, PDO::PARAM_STR);

        if ($sth->execute()) {
            if (!$filled and empty($userid)) {
                // Khách trả lời lần đầu tạo session kết quả để thao tác trong phiên làm việc
                $session_data[$form_info['id']] = [
                    'keycode' => $answer_code,
                    'data' => $crypt->encrypt(json_encode([
                        'time' => $answer_info['answer_time'],
                        'ip' => NV_CLIENT_IP,
                        'agent' => NV_USER_AGENT
                    ]), $secret_code)
                ];
                $nv_Request->set_Session($module_data . '_answer', $crypt->encrypt(json_encode($session_data)));
            }

            // Báo cáo kết qủa qua email
            if (($form_info['form_report_type'] == 1) and !$filled) {
                $form_report_type_email = unserialize($form_info['form_report_type_email']);
                $subject = $lang_module['reply'] . ': ' . $form_info['title'];
                $listmail = [];

                // Lấy danh sách email
                if ($form_report_type_email['form_report_type_email'] == 0 and !empty($form_report_type_email['group_email'])) {
                    $result = $db->query('SELECT userid FROM ' . NV_GROUPS_GLOBALTABLE . '_users WHERE group_id IN (' . implode(',', $form_report_type_email['group_email']) . ')');
                    while (list ($userid) = $result->fetch(3)) {
                        $listmail[] = $db->query('SELECT email FROM ' . NV_USERS_GLOBALTABLE . ' WHERE userid=' . $userid)->fetchColumn();
                    }
                } elseif ($form_report_type_email['form_report_type_email'] == 1 and !empty($form_report_type_email['listmail'])) {
                    $listmail = explode(';', $form_report_type_email['listmail']);
                    $listmail = array_map('trim', $listmail);
                }

                if (!empty($listmail)) {
                    $listmail = array_unique($listmail);

                    // Nội dung email
                    $answer_info['username'] = !defined('NV_IS_USER') ? $lang_module['report_guest'] : $user_info['full_name'];

                    $xtpl = new XTemplate('sendmail.tpl', NV_ROOTDIR . '/themes/' . $module_info['template'] . '/modules/' . $module_file);
                    $xtpl->assign('FORM_DATA', nv_form_result($question_info, $answer_info));

                    $xtpl->parse('main');
                    $message = $xtpl->text('main');
                    $message = nv_site_theme($message, false);

                    nv_sendmail($global_config['site_email'], $listmail, $subject, $message);
                }
            }

            if ($form_info['question_report']) {
                $link_report = NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&amp;' . NV_NAME_VARIABLE . '=' . $module_name . '&amp;' . NV_OP_VARIABLE . '=' . $module_info['alias']['viewanalytics'] . '/' . $form_info['alias'] . '-' . $form_info['id'] . $global_config['rewrite_exturl'];
                $info .= '<br />' . sprintf($lang_module['success_user_info_report'], $form_info['link'], $link_report);
            } else {
                $info .= '<br />' . sprintf($lang_module['success_user_info'], $form_info['link']);
            }
            nv_theme_nvform_alert($lang_module['success'], $info, 'success', '', 0, $embed);
        }
    } else {
        $info = $error;
    }
}

$page_title = $form_info['title'];
if (!empty($form_info['description'])) {
    $description = $form_info['description'];
}
if (!empty($form_info['image'])) {
    $meta_property['og:image'] = NV_MY_DOMAIN . NV_BASE_SITEURL . NV_UPLOADS_DIR . '/' . $module_upload . '/' . $form_info['image'];
}

$contents = nv_theme_nvform_viewform($form_info, $question_info, $answer_info, $answer_info_extend, $info);

include NV_ROOTDIR . '/includes/header.php';
echo nv_site_theme($contents, !$embed);
include NV_ROOTDIR . '/includes/footer.php';
