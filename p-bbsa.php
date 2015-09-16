<?php

require 'autoload.php';

// *rbl.phpには、RealtimeBlackListサーバに問い合わせてスパム判定するcheck_spam関数がある
require_once "rbl.php";

date_default_timezone_set('Asia/Tokyo');

// 禁止ホスト
$no_hosts = Config::get('no_host');
$remote_addr = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
if (Security::existHost($remote_addr, $no_hosts)) {
    header("Status: 204\n\n"); //空白ページ
    exit;
}

/*=====================
    メイン
======================*/
$mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');

$main = new Main();
try {
    $main->index($mode);
} catch (Exception $e) {
    echo $e->getMessage();
}
