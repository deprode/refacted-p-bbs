<?php

require_once 'validation.php';
require_once 'config.php';
require_once 'template.php';


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

function head(&$dat)
{
    $mode = filter_input(INPUT_GET, 'mode');
    $no = filter_input(INPUT_GET, 'no');

    $p_bbs = filter_input(INPUT_COOKIE, 'p_bbs');

    //ヘッダー表示部
    $config = new Config();
    $logfile = $config->getConfig('logfile');
    $title1 = $config->getConfig('title1');
    $title2 = $config->getConfig('title2');
    $body = $config->getConfig('body');
    $htmlw = $config->getConfig('htmlw');
    $max = $config->getConfig('max');
    $page_def = $config->getConfig('page_def');

    $r_name = $r_mail = null;
    $r_sub = $r_com = $r_pass = null;

    //クッキーを頂きます

    // * cookieには名前とメールが入っているので呼び出してるっぽい
    if (!$htmlw && isset($p_bbs)) {
        list($r_name, $r_mail) = explode(",", $p_bbs);
    }

    if ($mode == "resmsg") {
        list($r_sub, $r_com) = getResMsg($logfile, $no);
    }

    $head = <<<HEAD
<html><head>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=utf-8">
<title>$title1</title>
</head>
HEAD;

    $dat = $head . $body;
    $dat .= <<<DAT
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}">
<input type="hidden" name="mode" value="regist">
<BASEFONT SIZE="3">$title2<hr size=1><br>
<TT>
お名前 <input type=text name="name" size=20 value="$r_name" maxlength=24><br>
メール <input type=text name="email" size=30 value="$r_mail"><br>
題名　 <input type=text name="sub" size=30 value="$r_sub">
<input type=submit value="     投稿     "><input type=reset value="消す"><br>
<textarea name="com" rows=5 cols=82>$r_com</textarea><br><br>
ＵＲＬ　 <input type=text name="url" size=70 value="http://"><br>
削除キー <input type=password name="password" size=8 value="$r_pass">(記事の削除用。英数字で8文字以内)
</form></TT>
<hr size=1><font size=-2>新しい記事から表示します。最高{$max}件の記事が記録され、それを超えると古い記事から過去ログへ移ります。<br>
 １回の表示で{$page_def}件を越える場合は、下のボタンを押すことで次の画面の記事を表示します。</font>
DAT;

}

function foot(&$dat)
{
    //フッター表示部
    $config = new Config();
    $home = $config->getConfig('home');
    $past_key = $config->getConfig('past_key');

    $past_log = ($past_key) ? '[ <a href=' . $_SERVER['SCRIPT_NAME'] . '?mode=past>過去ログ</a> ]' : '';

    $dat .= <<<DAT
<div align="right"><form method="POST" action="{$_SERVER['SCRIPT_NAME']}">
<input type=hidden name=mode value="usrdel">No <input type=text name=no size=2>
pass <input type=password name=pwd size=4 maxlength=8>
<input type=submit value="Del"></form>
[ <a href=$home>ホーム</a> ] [ <a href={$_SERVER['SCRIPT_NAME']}?mode=admin>管理</a> ] $past_log
<br><br><small><!-- P-BBS v1.232 -->- <a href="http://php.s3.to" target="_top">P-BBS</a> -</small></div>
</body></html>
DAT;
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

function Main(&$dat)
{
    $page = filter_input(INPUT_GET, 'page');

    $config = new Config();
    $logfile = $config->getConfig('logfile');
    $page_def = $config->getConfig('page_def');
    $re_color = $config->getConfig('re_color');
    $autolink = $config->getConfig('autolink');
    $hostview = $config->getConfig('hostview');

    $validation = new Validation();

    // ログファイルを読み出し、件数を数える
    $view = file($logfile);
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
            $host, $pw) = explode("<>", $view[$s]);

        // タグ禁止
        $sub = $validation->h($sub);
        $name = $validation->h($name);
        $email = $validation->h($email);
        $url = $validation->h($url);
        $com = br2nl($com);
        $com = $validation->h($com);
        $com = str_replace("&amp;", "&", $com);
        $com = nl2br($com);

        if ($url) {
            $url = "<a href=\"http://$url\" target=\"_blank\">http://$url</a>";
        }
        if ($email) {
            $name = "<a href=\"mailto:$email\">$name</a>";
        }
        // ＞がある時は色変更
        $com = preg_replace("/(^|>)(&gt;[^<]*)/i", "\\1<font color=$re_color>\\2</font>", $com);
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

        $dat .= '<hr size=1>[<a href="' . $_SERVER['SCRIPT_NAME'] . '?mode=resmsg&no=' . $no . '">' . $no . '</a>] ';
        $dat .= '<font size="+1" color="#D01166"><b>' . $sub . '</b></font><br>';
        $dat .= '　Name：<font color="#007000"><b>' . $name . '</b></font><font size="-1">　Date： ' . $now . '</font>';
        $dat .= '<p><blockquote><tt>' . $com . '<br></tt>';
        $dat .= '<p>' . $url . '<br>' . $host . '</blockquote><p>';

        $p++;
    } //end for

    $prev = $page - $page_def;
    $next = $page + $page_def;
    $dat .= sprintf("<hr size=1> %d 番目から %d 番目の記事を表示<br><center>Page:[<b> ", $st, $st + $p - 1);
    ($page > 0) ? $dat .= "<a href=\"{$_SERVER['SCRIPT_NAME']}?page=$prev\">&lt;&lt;</a> " : $dat .= " ";
    $p_no = 1;
    $p_li = 0;

    while ($total > 0) {
        if ($page == $p_li) {
            $dat .= "$p_no ";
        } else {
            $dat .= "<a href=\"{$_SERVER['SCRIPT_NAME']}?page=$p_li\">$p_no</a> ";
        }
        $p_no++;
        $p_li = $p_li + $page_def;
        $total = $total - $page_def;
    }

    ($total2 > $next) ? $dat .= " <a href=\"{$_SERVER['SCRIPT_NAME']}?page=$next\">&gt;&gt;</a>" : $dat .= " ";
    $dat .= "</b> ]\n";
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
    $apass = filter_input(INPUT_POST, 'apass');
    $del = isset($_POST['del']) ? (array)$_POST['del'] : [];
    $del = array_filter($del, 'is_string');

    $config = new Config();
    $admin_pass = $config->getConfig('admin_pass');
    $logfile = $config->getConfig('logfile');
    $body = $config->getConfig('body');
    $title1 = $config->getConfig('title1');

    $head = <<<HEAD
<html><head>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=utf-8">
<title>$title1</title>
</head>
HEAD;

    //管理機能
    if ($apass && $apass != "$admin_pass") {error("パスワードが違います");}
    echo "$head";
    echo "$body";
    echo "[<a href=\"{$_SERVER['SCRIPT_NAME']}?\">掲示板に戻る</a>]\n";
    echo "<table width='100%'><tr><th bgcolor=\"#508000\">\n";
    echo "<font color=\"#FFFFFF\">管理モード</font>\n";
    echo "</th></tr></table>\n";

    if (!$apass) {
        echo "<P><center><h4>パスワードを入力して下さい</h4>\n";
        echo "<form action=\"{$_SERVER['SCRIPT_NAME']}\" method=\"POST\">\n";
        echo "<input type=hidden name=mode value=\"admin\">\n";
        echo "<input type=password name=apass size=8>";
        echo "<input type=submit value=\" 認証 \"></form>\n";
    } else {
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
        echo "<form action=\"{$_SERVER['SCRIPT_NAME']}\" method=\"POST\">\n";
        echo "<input type=hidden name=mode value=\"admin\">\n";
        echo "<input type=hidden name=apass value=\"$apass\">\n";
        echo "<center><P>削除したい記事のチェックボックスにチェックを入れ、削除ボタンを押して下さい。\n";
        echo "<P><table border=0 cellspacing=0>\n";
        echo "<tr bgcolor=bbbbbb><th>削除</th><th>記事No</th><th>投稿日</th><th>題名</th>";
        echo "<th>投稿者</th><th>コメント</th><th>ホスト名</th>";
        echo "</tr>\n";

        $delmode = file($logfile);

        if (is_array($delmode)) {
            while (list($l, $val) = each($delmode)) {
                list($no, $date, $name, $email, $sub, $com, $url,
                    $host, $pw, $tail, $w, $h, $time, $chk) = explode("<>", $val);

                list($date, $dmy) = split("\(", $date);
                if ($email) {$name = "<a href=\"mailto:$email\">$name</a>";}
                $com = str_replace("<br>", "", $com);
                $com = htmlspecialchars($com);
                if (strlen($com) > 40) {$com = substr($com, 0, 38) . " ...";}

                echo ($l % 2) ? "<tr bgcolor=F8F8F8>" : "<tr bgcolor=DDDDDD>";
                echo "<th><input type=checkbox name=del[] value=\"$no\"></th>";
                echo "<th>$no</th><td><small>$date</small></td><td>$sub</td>";
                echo "<td><b>$name</b></td><td><small>$com</small></td>";
                echo "<td>$host</td>\n</tr>\n";
            }
        }

        echo "</table>\n";
        echo "<P><input type=submit value=\"削除する\">";
        echo "<input type=reset value=\"リセット\"></form>\n";

    }
    echo "</center></body></html>\n";
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

    head($buf);
    Main($buf);
    foot($buf);

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

    $config = new Config();
    $past_no = $config->getConfig('past_no');
    $past_dir = $config->getConfig('past_dir');
    $past_line = $config->getConfig('past_line');
    $body = $config->getConfig('body');

    $pno = htmlspecialchars($pno);

    $fc = @fopen($past_no, "r") or die(__LINE__ . $past_no . "が開けません");
    $count = fgets($fc, 10);
    fclose($fc);
    if (!$pno) {
        $pno = $count;
    }

    echo '<html><head><title>■ 過去ログ ' . $pno . ' ■</title>
' . $body . '<font size=2>[<a href="' . $_SERVER['SCRIPT_NAME'] . '?">掲示板に戻る</a>]</font><br>
<center>■ 過去ログ ' . $pno . ' ■<P>new← ';
    $pastkey = $count;
    while ($pastkey > 0) {
        if ($pno == $pastkey) {
            echo "[<b>$pastkey</b>]";
        } else {
            echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?mode=past&pno=$pastkey\">[$pastkey]</a>";
        }
        $pastkey--;
    }
    echo ' →old</center>' . $past_line . '件ずつ表示';
    $pastfile = $past_dir . "index" . $pno . ".html";
    if (!file_exists($pastfile)) {
        error("<br>過去ログがみつかりません");
    }

    include $pastfile;
    die("</body></html>");
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

function error($mes)
{
    $config = new Config();
    $body = $config->getConfig('body');

    //エラーフォーマット
    ?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<?php echo $body;?>
<br><br><hr size=1><br><br>
<center><font color=red size=5><b><?php echo $mes;?></b></font></center>
<br><br><hr size=1></body></html>
	<?php

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
        $mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');
        // *$modeはregist、admin,userdel,past,その他（通常時）の4つ。
        switch ($mode) {
            case 'regist':
                // *rbl.phpには、RealtimeBlackListサーバに問い合わせてスパム判定するcheck_spam関数がある
                require_once "./rbl.php";
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