<?php

require 'autoload.php';

// *rbl.phpには、RealtimeBlackListサーバに問い合わせてスパム判定するcheck_spam関数がある
require_once "app/rbl.php";

date_default_timezone_set('Asia/Tokyo');

session_start();

header('X-FRAME-OPTIONS: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

$settings = require __DIR__.'/app/settings.php';
$config_file_name = $settings['config']['path'];

if (!is_readable($config_file_name)) {
    echo "設定ファイル(" . $config_file_name . ")が読み込めません。";
    exit;
}

// 禁止ホスト
$config = new Config();
$no_hosts = $config->get('no_host');
$remote_addr = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
if (Security::existHost($remote_addr, $no_hosts)) {
    header("Status: 204\n\n"); //空白ページ
    exit;
}

/*=====================
    メイン
======================*/
$mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');

$route = new Router();
try {
    $route->index($mode);
} catch (Exception $e) {
    echo $e->getMessage();
}
