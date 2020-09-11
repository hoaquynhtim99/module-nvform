<?php

/**
 * @Project NUKEVIET 4.x
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2014 VINADES.,JSC. All rights reserved
 * @License GNU/GPL version 2 or any later version
 * @Createdate Tue, 08 Apr 2014 15:13:43 GMT
 */

if (!defined('NV_MAINFILE')) {
    die('Stop!!!');
}

$module_version = [
    'name' => 'NVForm',
    'modfuncs' => 'main, viewform, viewanalytics',
    'change_alias' => 'viewanalytics',
    'submenu' => 'main',
    'is_sysmod' => 0,
    'virtual' => 1,
    'version' => '4.3.02',
    'date' => 'Friday, September 11, 2020 19:21:55 GMT+07:00',
    'author' => 'hongoctrien <hongoctrien@2mit.org>',
    'uploads_dir' => [
        $module_name
    ],
    'note' => 'Module cho phép tạo form khảo sát'
];
