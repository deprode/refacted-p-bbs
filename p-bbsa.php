<?php

require_once 'validation.php';
require_once 'config.php';
require_once 'template.php';

// *rbl.phpには、RealtimeBlackListサーバに問い合わせてスパム判定するcheck_spam関数がある
require_once "rbl.php";


/*
 * P-BBS by ToR
 * http://php.s3.to
 *
 * 2000/12/02 pre  完成
 * 2001/03/06 v1.0 完成ー
 * 2001/03/11 v1.1 HTML書き出すOnOff、書き込み後Locationで飛ばす、管理ﾓｰﾄﾞpass→apass
 * 2001/04/16 v1.2 過去ログ対応、＞がつくと色変わる。デザイン変更
 * 2001/04/24 v1.23 書き込み後表示関数化、ページング変更、管理ﾓｰﾄﾞ実行後修正、ホスト表示、Re:[2]
 * 2001/05/04 v1.231 クッキーをHTMLに書き出してしまうバグ修正,過去ログモードの非表示<br>
 * 2001/05/17 v1.232 文字数制限、行数制限追加
 * 2001/05/27 v1.24 autolink修正、書き込み後refreshで飛ばす
 * 2001/06/02 v1.25 GET投稿禁止、外部投稿禁止
 * 2001/11/15 v1.26 >の後のスペース無くす。PHP3の時レスで<br>となるバグ修正
 * 2002/05/25 v1.27 i18n削除、空白ﾁｪｯｸ修正
 * 2002/02/11 v1.28 クッキーの文字化け対策
 * 2003/05/25 v1.29 禁止ホスト、禁止ワード追加
 * 2003/06/07 v1.3  複数削除出来るように
 *
 * シンプルな掲示板です。管理モード付
 * 空のログファイルを用意して、パーミッションを606にしてください
 * HTMLを書き出す場合は、そのディレクトリが707か777じゃないとダメです
 */

date_default_timezone_set('Asia/Tokyo');


// 禁止ホスト
if (is_array($no_host)) {
    // IPアドレスをホスト名にしてホストをはじいている
    $host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
    foreach ($no_host as $user) {
        if (preg_match("/$user/i", $host)) {
            header("Status: 204\n\n"); //空白ページ
            exit;
        }
    }
}

function getResMsg($logfile, $no)
{
    //レスの場合
    $res = file($logfile);
    $flag = 0;

    while (list($key, $value) = each($res)) {
        list($rno, $date, $name, $email, $sub, $com, $url) = explode("<>", $value);
        if ($no == "$rno") {
            $flag = 1;
            break;
        }
    }

    if ($flag == 0) {
        error("該当記事が見つかりません");
    }

    if (preg_match("/Re\[([0-9]+)\]:/", $sub, $reg)) {
        $reg[1]++;
        $r_sub = preg_replace("/Re\[([0-9]+)\]:/", "Re[$reg[1]]:", $sub);
    } elseif (preg_match("/^Re:/", $sub)) {
        $r_sub = preg_replace("/^Re:/", "Re[2]:", $sub);
    } else {
        $r_sub = "Re:$sub";
    }
    $r_com = "&gt;$com";
    $r_com = preg_replace("/<br( \/)?>/", "\r&gt;", $r_com);

    return [$r_sub, $r_com];
}

function createMain($view, $page, $page_def)
{
    $validation = new Validation();
    $dat = [];

    // ログファイルを読み出し、件数を数える
    $total = sizeof($view);
    $total2 = $total;

    // 開始ページ（レス番号）数の設定
    (isset($page)) ? $start = $page : $start = 0;
    $end = $start + $page_def;
    $st = $start + 1;

    $p = 0;
    for ($s = $start; $s < $end && $p < count($view); $s++) {
        if (!$view[$s]) {
            break;
        }

        list($no, $now, $name, $email, $sub, $com, $url,
            $host,) = explode("<>", $view[$s]);

        // タグ禁止
        $sub = $validation->h($sub);
        $name = $validation->h($name);
        $email = $validation->h($email);
        $url = $validation->h($url);
        $com = br2nl($com);
        $com = $validation->h($com);
        $com = str_replace("&amp;", "&", $com);
        $com = nl2br($com);

        // URL自動リンク
        if ($autolink) {
            $com = autoLink($com);
        }
        // Host表示形式
        if ($hostview == 1) {
            $host = "<!--$host-->";
        } elseif ($hostview == 2) {
            $host = "[ $host ]";
        } else {
            $host = "";
        }

        $dat[] = [
            'no' => $no,
            'now' => $now,
            'sub' => $sub,
            'email' => $email,
            'name' => $name,
            'now' => $now,
            'com' => $com,
            'url' => $url,
            'host' => $host
        ];
    }

    return $dat;
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
    $config = new Config();
    $past_key = $config->getConfig('past_key');
    $maxn = $config->getConfig('maxn');
    $maxs = $config->getConfig('maxs');
    $maxv = $config->getConfig('maxv');
    $maxline = $config->getConfig('maxline');
    $html_url = $config->getConfig('html_url');
    $logfile = $config->getConfig('logfile');
    $max = $config->getConfig('max');
    $w_regist = $config->getConfig('w_regist');
    $autolink = $config->getConfig('autolink');
    $mudai = $config->getConfig('mudai');
    $no_word = $config->getConfig('no_word');
    $GAIBU = $config->getConfig('GAIBU');

    $validation = new Validation();

    if (preg_match("/(<a\b[^>]*?>|\[url(?:\s?=|\]))|href=/i", $com)) {
        error("禁止ワードエラー！！");
    }

    if ($_SERVER['REQUEST_METHOD'] != "POST") {
        error("不正な投稿をしないで下さい");
    }

    if ($GAIBU && !preg_match("/" . $_SERVER['SCRIPT_NAME'] . "/i", getenv("HTTP_REFERER"))) {
        error("外部から書き込みできません");
    }

    // フォームが空かチェック
    if ($validation->isEmpty($name)) {
        error("名前が書き込まれていません");
    }
    if ($validation->isEmpty($com)) {
        error("本文が書き込まれていません");
    }
    if ($validation->isEmpty($sub)) {
        $sub = $mudai;
    }

    // 最大長チェック
    if ($validation->overLength($name, $maxn)) {
        error("名前が長すぎますっ！");
    }
    if ($validation->overLength($sub, $maxs)) {
        error("タイトルが長すぎますっ！");
    }
    if ($validation->overLength($com, $maxv)) {
        error("本文が長すぎますっ！");
    }

    // 禁止ワード
    if (is_array($no_word)) {
        foreach ($no_word as $fuck) {
            if (preg_match("/$fuck/", $com)) {
                error("使用できない言葉が含まれています！");
            }

            if (preg_match("/$fuck/", $sub)) {
                error("使用できない言葉が含まれています！");
            }

            if (preg_match("/$fuck/", $name)) {
                error("使用できない言葉が含まれています！");
            }

        }
    }

    $times = time();

    $check = file($logfile);
    $tail = sizeof($check);

    list($tno, $tdate, $tname, $tmail, $tsub, $tcom, , , $tpw, $ttime) = explode("<>", $check[0]);
    if ($name == $tname && $com == $tcom) {
        error("二重投稿は禁止です");
    }

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
    if ($str_cnt > $maxline) {error("行数が長すぎますっ！");}

    $com = preg_replace("/\n((　| |\t)*\n){3,}/", "\n", $com); //連続する空行を一行
    $com = nl2br($com); //改行文字の前に<br>を代入する。
    $com = preg_replace("/\n/", "", $com); //\nを文字列から消す。

    $new_msg = "$no<>$now<>$name<>$email<>$sub<>$com<>$url<>$host<>$PW<>$times\n";

    //クッキー保存
    $cookvalue = implode(",", array($name, $email));
    setcookie("p_bbs", $cookvalue, time() + 14 * 24 * 3600); /* 2週間で期限切れ */

    $old_log = file($logfile);
    $line = sizeof($old_log);
    $new_log[0] = $new_msg; //先頭に新記事
    if ($past_key && $line >= $max) {
        //はみ出した記事を過去ログへ
        for ($s = $max; $s <= $line; $s++) { //念の為複数行対応
            pastLog($old_log[$s - 1]);
        }
    }

    for ($i = 1; $i < $max; $i++) {
        //最大記事数処理
        $new_log[$i] = $old_log[$i - 1];
    }
    renewlog($new_log); //ログ更新

}

function usrdel()
{
    $pwd = filter_input(INPUT_POST, 'pwd');
    $no = filter_input(INPUT_POST, 'no');
    $config = new Config();
    $logfile = $config->getConfig('logfile');

    //ユーザー削除
    if ($no == "" || $pwd == "") {error("削除Noまたは削除キーが入力モレです");}

    $logall = file($logfile);
    $flag = 0;

    while (list(, $lines) = each($logall)) {
        list($ono, $dat, $name, $email, $sub, $com, $url, $host, $opas) = explode("<>", $lines);
        if ($no == "$ono") {
            $flag = 1;
            $pass = $opas;} else { $pushlog[] = $lines;}
    }

    if ($flag == 0) {error("該当記事が見当たりません");}
    if ($pass == "") {error("該当記事には削除キーが設定されていません");}

    // 削除キーを照合
    $match = password_verify($pwd, $pass);
    if (($match != $pass)) {error("削除キーが違います");}

    // ログを更新
    renewlog($pushlog);
}

function admin()
{
    $tpl = new Template();
    $config = new Config();

    $admin_pass = $config->getConfig('admin_pass');
    $logfile = $config->getConfig('logfile');
    $body = $config->getConfig('body');
    $title1 = $config->getConfig('title1');

    $apass = filter_input(INPUT_POST, 'apass');
    $del = isset($_POST['del']) ? (array)$_POST['del'] : [];
    $del = array_filter($del, 'is_string');

    if (isset($apass) && $apass != $admin_pass) {
        error('パスワードが違います');
    }

    // 削除処理
    if (is_array($del)) {
        // 削除情報をマッチングし更新
        $delall = file($logfile);

        for ($i = 0; $i < count($delall); $i++) {
            list($no) = explode("<>", $delall[$i]);
            if (in_array($no, $del)) {
                $delall[$i] = "";
            }

        }
        // ログを更新
        renewlog($delall);
    }

    // 削除画面を表示
    $tpl->c = $config;
    $tpl->script_name = $_SERVER['SCRIPT_NAME'];

    $tpl->apass = $apass;
    $tpl->del = $del;


    // ログを削除モードで表示
    $delmode = file($logfile);
    $logs = [];
    if (is_array($delmode)) {
        foreach ($delmode as $l => $val) {
            list($no, $date, $name, $email, $sub, $com, ,
                $host, , , , , $time, ) = explode("<>", $val);

            list($date, $dmy) = split("\(", $date);
            if ($email) {
                $name = "<a href=\"mailto:$email\">$name</a>";
            }
            $com = str_replace("<br>", "", $com);
            $com = htmlspecialchars($com);
            if (strlen($com) > 40) {$com = substr($com, 0, 38) . " ...";}

            $log = [];
            $log['no'] = $no;
            $log['date'] = $date;
            $log['sub'] = $sub;
            $log['name'] = $name;
            $log['com'] = $com;
            $log['host'] = $host;

            $logs[] = $log;
        }
    }

    $tpl->delmode = $logs;
    $tpl->show('template/admin.tpl.php');
}

function lockDir($name = "")
{
    //ディレクトリロック
    if ($name == "") {
        $name = "lock";
    }

    // 3分以上前のディレクトリなら解除失敗とみなして削除
    if ((file_exists($name)) && filemtime($name) < time() - 180) {
        @RmDir($name);
    }

    do {
        if (@MkDir($name, 0777)) {
            return 1;
        }
        sleep(1); // 一秒待って再トライ
        $i++;
    } while ($i < 5);

    return 0;
}

function unlockDir($name = "")
{
    //ロック解除
    if ($name == "") {
        $name = "lock";
    }

    @rmdir($name);
}

function renewlog($arrline)
{
    $config = new Config();
    $logfile = $config->getConfig('logfile');

    //ログ更新  入力:配列

    if (LOCKEY == 1) {
        lockDir(LOCK)
        or error("ロックエラー<br>しばらく待ってからにして下さい");}

    $rp = fopen($logfile, "w");
    if (LOCKEY == 2) {flock($rp, 2);}
    while (list(, $val) = each($arrline)) {
        fputs($rp, $val);
    }
    fclose($rp);
    if (LOCKEY == 1) {unlockDir(LOCK);}
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
    flock($hp, 2);
    fputs($hp, $buf);
    fclose($hp);
}

function ShowHtml()
{
    $mode = filter_input(INPUT_GET, 'mode');
    $no = filter_input(INPUT_GET, 'no');
    $page = filter_input(INPUT_GET, 'page');

    $p_bbs = filter_input(INPUT_COOKIE, 'p_bbs');

    $r_name = $r_mail = null;
    $r_sub = $r_com = $r_pass = null;

    $tpl = new Template();
    $config = new Config();

    $tpl->c = $config;
    $tpl->script_name = $_SERVER['SCRIPT_NAME'];

    // * cookieには名前とメールが入っているので呼び出してるっぽい
    $htmlw = $config->getConfig('htmlw');
    if (!$htmlw && isset($p_bbs)) {
        list($r_name, $r_mail) = explode(",", $p_bbs);
    }
    $logfile = $config->getConfig('logfile');
    if ($mode == "resmsg") {
        list($r_sub, $r_com) = getResMsg($logfile, $no);
    }

    $tpl->r_name = $r_name;
    $tpl->r_mail = $r_mail;
    $tpl->r_sub = $r_sub;
    $tpl->r_com = $r_com;
    $tpl->r_pass = $r_pass;

    $page_def = $config->getConfig('page_def');
    $view = file($logfile);
    $tpl->dat = createMain($view, $page, $page_def);

    $tpl->page = $page;
    $tpl->start = (isset($page)) ? $page + 1 : 1;
    $tpl->total = count($view);

    $tpl->show('template/index.tpl.php');
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

    if ($purl) {$purl = "<a href=\"http://$purl\" target=\"_blank\">HP</a>";}
    if ($pemail) {$pname = "<a href=\"mailto:$pemail\">$pname</a>";}
    // ＞がある時は色変更
    $pcom = preg_replace("/(&gt;)([^<]*)/i", "<font color=999999>\\1\\2</font>", $pcom);
    // URL自動リンク
    if ($autolink) {$pcom = autoLink($pcom);}

    $dat .= "<hr>[$pno] <font color=\"#009900\"><b>$psub</b></font> Name：<b>$pname</b> <small>Date：$pdate</small> $purl<br><ul>$pcom</ul><!-- $pho -->\n";

    $np = fopen($pastfile, "w");
    fputs($np, $dat);
    if ($past) {
        while (list(, $val) = each($past)) {fputs($np, $val);}
    }
    fclose($np);

}

function pastView()
{
    $pno = filter_input(INPUT_GET, 'pno');

    $tpl = new Template();
    $config = new Config();

    $past_no = $config->getConfig('past_no');
    $past_dir = $config->getConfig('past_dir');

    $pno = htmlspecialchars($pno);

    $fc = @fopen($past_no, "r") or die(__LINE__ . $past_no . "が開けません");
    $count = fgets($fc, 10);
    fclose($fc);
    if (!$pno) {
        $pno = $count;
    }

    $pastfile = $past_dir . "index" . $pno . ".html";
    if (!file_exists($pastfile)) {
        error("<br>過去ログがみつかりません");
    }

    $tpl->pno = $pno;
    $tpl->count = $count;
    $tpl->pastfile = $pastfile;

    $tpl->c = $config;
    $tpl->script_name = $_SERVER['SCRIPT_NAME'];
    $tpl->show('template/past.tpl.php');

    exit;
}

function autoLink($proto)
{
    //自動リンク5/25修正
    $proto = preg_replace("/(https?|ftp|news)(:\/\/[[A-Za-z0-9]\+\$\;\?\.%,!#~*\/:@&=_-]+)/", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $proto);
    return $proto;
}

function br2nl($string)
{
    return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
}

//エラーフォーマット
function error($mes)
{
    $tpl = new Template();
    $config = new Config();

    $tpl->c = $config;
    $tpl->script_name = $_SERVER['SCRIPT_NAME'];
    $tpl->body = $config->getConfig('body');
    $tpl->mes = $mes;

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
    function index()
    {
        $config = new Config();
        $htmlw = $config->getConfig('htmlw');
        $mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');
        // *$modeはregist、admin,userdel,past,その他（通常時）の4つ。
        switch ($mode) {
            case 'regist':
                if (check_spam()) {
                    die("梅干たべてすっぱぃまん！！");
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
                admin();
                break;
            case 'usrdel':
                // *ユーザー権限による書き込みの削除
                usrdel();
                // *トップページをHTMLに書き出す場合はMakeHtml()でHTMLファイル作成
                if ($htmlw) {
                    MakeHtml();
                }

                // HTML表示？
                ShowHtml();
                break;
            case 'past':
                // 過去ログモード
                pastView();
                break;
            default:
                ShowHtml();
                break;
        }
    }
}

$main = new Main();
$main->index();

?>