<?php

require_once 'validation.php';
require_once 'config.php';
require_once 'template.php';
require_once 'log.php';
require_once 'viewmodel.php';
require_once 'security.php';

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

function validationPost($name, $sub, $com)
{
    $error_msg = '';

    if (preg_match("/(<a\b[^>]*?>|\[url(?:\s?=|\]))|href=/i", $com)) {
        $error_msg .= "禁止ワードエラー！！" . PHP_EOL;
    }

    // フォームが空かチェック
    if (Validation::isEmpty($name)) {
        $error_msg .= "名前が書き込まれていません" . PHP_EOL;
    }
    if (Validation::isEmpty($com)) {
        $error_msg .= "本文が書き込まれていません" . PHP_EOL;
    }
    if (Validation::isEmpty($sub)) {
        $sub = Config::get('mudai');
    }

    // 最大長チェック
    if (Validation::overLength($name, Config::get('maxn'))) {
        $error_msg .= "名前が長すぎますっ！" . PHP_EOL;
    }
    if (Validation::overLength($sub, Config::get('maxs'))) {
        $error_msg .= "タイトルが長すぎますっ！" . PHP_EOL;
    }
    if (Validation::overLength($com, Config::get('maxv'))) {
        $error_msg .= "本文が長すぎますっ！" . PHP_EOL;
    }

    // 禁止ワード
    $no_word = Config::get('maxn');
    if (is_array($no_word)) {
        foreach ($no_word as $fuck) {
            if (preg_match("/$fuck/", $com)) {
                $error_msg .= "使用できない言葉が含まれています！" . PHP_EOL;
            }

            if (preg_match("/$fuck/", $sub)) {
                $error_msg .= "使用できない言葉が含まれています！" . PHP_EOL;
            }

            if (preg_match("/$fuck/", $name)) {
                $error_msg .= "使用できない言葉が含まれています！" . PHP_EOL;
            }

        }
    }
}

function regist()
{
    $name = filter_input(INPUT_POST, 'name');
    $email = filter_input(INPUT_POST, 'email');
    $sub = filter_input(INPUT_POST, 'sub');
    $url = filter_input(INPUT_POST, 'url');
    $com = filter_input(INPUT_POST, 'com');
    $password = filter_input(INPUT_POST, 'password');

    //ログ書き込み
    $logfile = Config::get('logfile');

    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        error("不正な投稿をしないで下さい");
    }

    if (Config::get('GAIBU') && !preg_match("/" . $_SERVER['SCRIPT_NAME'] . "/i", getenv("HTTP_REFERER"))) {
        error("外部から書き込みできません");
    }

    $error_msg = validationPost($name, $sub, $com);
    if (mb_strlen($error_msg) > 0) {
        error($error_msg);
    }

    $times = time();

    $check = file($logfile);
    $tail = sizeof($check);

    list($tno, $tdate, $tname, $tmail, $tsub, $tcom, , , $tpw, $ttime) = explode("<>", $check[0]);
    if ($name == $tname && $com == $tcom) {
        error("二重投稿は禁止です");
    }

    $w_regist = Config::get('w_regist');
    if ($w_regist && $times - $ttime < $w_regist) {
        error("連続投稿はもうしばらく時間を置いてからお願い致します");
    }

    // 記事Noを採番
    $no = $tno + 1;

    // ホスト名を取得
    $host = getenv("REMOTE_HOST");
    $addr = $_SERVER['REMOTE_ADDR'];
    if ($host == "" || $host == $addr) {
        //gethostbyddrが使えるか
        $host = @gethostbyaddr($addr);
    }

    // 削除キーを暗号化
    if ($password) {
        $PW = password_hash($password, PASSWORD_DEFAULT);
    }

    $now = gmdate("Y/m/d(D) H:i", time() + 9 * 60 * 60);
    $url = preg_replace("/^http:\/\//", "", $url);

    // ログの区切り文字である<>を参照文字に置換
    $com = str_replace("<>", "&lt;&gt;", $com);
    $sub = str_replace("<>", "&lt;&gt;", $sub);
    $name = str_replace("<>", "&lt;&gt;", $name);
    $email = str_replace("<>", "&lt;&gt;", $email);
    $url = str_replace("<>", "&lt;&gt;", $url);

    //改行文字の統一。
    $com = str_replace("\r\n", "\r", $com);
    $com = str_replace("\r", "\n", $com);
    /* \n数える（substr_countの代わり）*/
    $temp = str_replace("\n", "\n" . "a", $com);
    $str_cnt = strlen($temp) - strlen($com);
    if ($str_cnt > Config::get('maxline')) {
        error("行数が長すぎますっ！");
    }

    $com = preg_replace("/\n((　| |\t)*\n){3,}/", "\n", $com); //連続する空行を一行
    $com = nl2br($com); //改行文字の前に<br>を代入する。
    $com = preg_replace("/\n/", "", $com); //\nを文字列から消す。

    $new_msg = "$no<>$now<>$name<>$email<>$sub<>$com<>$url<>$host<>$PW<>$times\n";

    //クッキー保存
    $cookvalue = implode(",", array($name, $email));
    setcookie("p_bbs", $cookvalue, time() + 14 * 24 * 3600); /* 2週間で期限切れ */

    $max = Config::get('max');
    $old_log = file($logfile);
    $line = sizeof($old_log);
    $new_log[0] = $new_msg; //先頭に新記事
    if (Config::get('past_key') && $line >= $max) {
        //はみ出した記事を過去ログへ
        for ($s = $max; $s <= $line; $s++) { //念の為複数行対応
            pastLog($old_log[$s - 1]);
        }
    }

    for ($i = 1; $i < $max; $i++) {
        //最大記事数処理
        $new_log[$i] = $old_log[$i - 1];
    }
    Log::renewlog($logfile, $new_log); //ログ更新
}

function usrdel()
{
    $pwd = filter_input(INPUT_POST, 'pwd');
    $no = filter_input(INPUT_POST, 'no');
    $logfile = Config::get('logfile');

    //ユーザー削除
    if (!isset($no) || empty($no) || !isset($pwd) || empty($pwd)) {
        error("削除Noまたは削除キーが入力モレです");
    }

    $pass = Log::searchDelPass($logfile, $no);

    if (isset($pass) === false) {
        error("該当記事が見当たりません");
    } else if ($pass === "") {
        error("該当記事には削除キーが設定されていません");
    }

    // 削除キーを照合
    $match = password_verify($pwd, $pass);
    if (($match != $pass)) {
        error("削除キーが違います");
    }

    // ログを更新
    Log::removePost($logfile, $no);
}

function adminAuth($password)
{
    $admin_pass = Config::get('admin_pass');

    if (isset($password) && $password != $admin_pass) {
        return false;
    }
    return true;
}

function adminDel()
{
    $logfile = Config::get('logfile');
    $del = isset($_POST['del']) ? (array)$_POST['del'] : [];
    $del = array_filter($del, 'is_string');

    // 削除処理
    Log::removePosts($logfile, $del);
}

function MakeHtml()
{
    //HTML生成
    $config = new Config();
    $html_file = $config->getConfig('html_file');

    ob_start();
    ShowHtml();
    $buf = ob_get_contents();
    ob_end_clean();

    $hp = @fopen($html_file, "w");
    flock($hp, LOCK_EX);
    fputs($hp, $buf);
    fflush($hp);
    flock($hp, LOCK_UN);
    fclose($hp);
}

function pastLog($data)
{
    $config = new Config();
    $past_no = $config->getConfig('past_no');
    $past_dir = $config->getConfig('past_dir');
    $past_line = $config->getConfig('past_line');
    $autolink = $config->getConfig('autolink');
//過去ログ作成

    $fc = @fopen($past_no, "r") or die(__LINE__ . $past_no . "が開けません");
    $count = fgets($fc, 10);
    fclose($fc);
    $pastfile = $past_dir . "index" . $count . ".html";
    if (file_exists($pastfile)) {
        $past = file($pastfile);
    }

    if (sizeof($past) > $past_line) {
        $count++;
        $pf = fopen($past_no, "w");
        fputs($pf, $count);
        fclose($pf);
        $pastfile = $past_dir . "index" . $count . ".html";
        $past = "";
    }

    list($pno, $pdate, $pname, $pemail, $psub,
        $pcom, $purl, $pho, $ppw) = explode("<>", $data);

    if ($purl) {
        $purl = "<a href=\"http://$purl\" target=\"_blank\">HP</a>";
    }
    if ($pemail) {
        $pname = "<a href=\"mailto:$pemail\">$pname</a>";
    }
    // ＞がある時は色変更
    $pcom = preg_replace("/(&gt;)([^<]*)/i", "<font color=999999>\\1\\2</font>", $pcom);
    // URL自動リンク
    if ($autolink) {
        $pcom = ViewModel::autoLink($pcom);
    }

    $dat .= "<hr>[$pno] <font color=\"#009900\"><b>$psub</b></font> Name：<b>$pname</b> <small>Date：$pdate</small> $purl<br><ul>$pcom</ul><!-- $pho -->\n";

    $np = fopen($pastfile, "w");
    fputs($np, $dat);
    if ($past) {
        while (list(, $val) = each($past)) {fputs($np, $val);}
    }
    fclose($np);
}

//エラーフォーマット
function error($mes)
{
    $tpl = new Template();

    $tpl->mes = nl2br($mes);

    $tpl->show('template/error.tpl.php');

    exit;
}

/*=====================
    メイン
======================*/
/**
* Main
*/
class Main
{
    function index($mode)
    {
        $vm = new ViewModel();
        $config = new Config();
        $htmlw = $config->getConfig('htmlw');
        // *$modeはregist、admin,userdel,past,その他（通常時）の4つ。
        switch ($mode) {
            case 'regist':
                if (check_spam()) {
                    die("指定されたIPからは投稿できません。");
                }

                // *ログ書き込み
                regist();
                // *トップページをHTMLに書き出す場合はMakeHtml()でHTMLファイル作成
                if ($htmlw) {
                    MakeHtml();
                }

                // *転送
                header("Location: {$_SERVER['SCRIPT_NAME']}");
                break;
            case 'admin':
                // *管理
                $apass = filter_input(INPUT_POST, 'apass');
                if (adminAuth($apass)) {
                    adminDel();
                    $vm->admin();
                } else {
                    $vm->error('パスワードが違います');
                }
                break;
            case 'usrdel':
                // *ユーザー権限による書き込みの削除
                usrdel();
                // *トップページをHTMLに書き出す場合はMakeHtml()でHTMLファイル作成
                if ($htmlw) {
                    MakeHtml();
                }

                // *転送
                header("Location: {$_SERVER['SCRIPT_NAME']}");
                break;
            case 'past':
                // 過去ログモード
                $vm->pastView();
                break;
            default:
                $vm->main();
                break;
        }
    }
}

$mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');

$main = new Main();
$main->index($mode);

?>