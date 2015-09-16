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
    $no_word = Config::get('no_word');
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

    return $error_msg;
}

function getHost()
{
    $host = filter_input(INPUT_SERVER, 'REMOTE_HOST');
    $addr = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    if ($host == "" || $host == $addr) {
        //gethostbyddrが使えるか
        $host = @gethostbyaddr($addr);
    }

    return $host;
}

function saveUserData($name, $email)
{
    $now = new DateTime();
    $limit = 14 * 24 * 3600; /* 2週間で期限切れ */

    $cookvalue = implode(",", array($name, $email));
    setcookie("p_bbs", $cookvalue, $now->format('U') + $limit);
}

function regist()
{
    $name = filter_input(INPUT_POST, 'name');
    $email = filter_input(INPUT_POST, 'email');
    $sub = filter_input(INPUT_POST, 'sub');
    $url = filter_input(INPUT_POST, 'url');
    $com = filter_input(INPUT_POST, 'com');

    /*
     * エラー処理
     */

    if (!Security::equalRequestMethod('POST')) {
        throw new Exception("不正な投稿をしないで下さい");
    }

    if (Config::get('GAIBU') && Security::checkReferrer()) {
        throw new Exception("外部から書き込みできません");
    }

    $error_msg = validationPost($name, $sub, $com);
    if (mb_strlen($error_msg) > 0) {
        throw new Exception($error_msg);
    }

    // 1つ前の書き込みを取得
    $logfile = Config::get('logfile');
    $prev_res = Log::getResDataForIndex($logfile, 0);

    // 二重投稿のチェック
    if (Validation::checkDuplicatePost($name, $com, $prev_res)) {
        throw new Exception("二重投稿は禁止です");
    }

    // 連続投稿のチェック
    $now = new DateTime();
    $nowtime = $now->format('U');
    $w_regist = Config::get('w_regist');

    if (Validation::checkShortTimePost($w_regist, $nowtime, $prev_res->unixtime)) {
        throw new Exception("連続投稿はもうしばらく時間を置いてからお願い致します");
    }

    // 最大行チェック
    if (Validation::overMaxline($com, Config::get('maxline'))) {
        throw new Exception("行数が長すぎますっ！");
    }


    /*
     * 記事整形
     */

    // 記事Noを採番
    $no = $prev_res->no + 1;

    // ホスト名を取得
    $host = getHost();

    // 削除キーを暗号化
    $password = filter_input(INPUT_POST, 'password');
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

    // 改行文字の統一。
    $com = str_replace("\r\n", "\r", $com);
    $com = str_replace("\r", "\n", $com);

    $com = preg_replace("/\n((　| |\t)*\n){3,}/", "\n", $com); //連続する空行を一行
    $com = nl2br($com); //改行文字の前に<br>を代入する。
    $com = preg_replace("/\n/", "", $com); //\nを文字列から消す。

    $new_msg = "$no<>$now<>$name<>$email<>$sub<>$com<>$url<>$host<>$PW<>$nowtime\n";


    // クッキー保存
    saveUserData($name, $email);

    /*
     * 記録
     */

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


// 過去ログ作成
function pastLog($data)
{
    $past_no = Config::get('past_no');
    $past_dir = Config::get('past_dir');
    $past_line = Config::get('past_line');
    $autolink = Config::get('autolink');

    // 過去ログのindex番号を読み取り
    $count = Pastlog::readPastIndexLog($past_no);
    // 作成する過去ログファイルの名前を作成
    $pastfile = Pastlog::buildPastnoFilePath($count, $past_dir);

    // 過去ログの読み込み
    if (file_exists($pastfile)) {
        $past = file($pastfile);
    }

    // 1行=1投稿なので、書き込み可能行数を超えていたら過去ログindex番号をインクリメントする
    if (sizeof($past) > $past_line) {
        $count++;
        Pastlog::writePastIndexLog($past_no, $count);
        // ファイルパスも作り直す
        $pastfile = Pastlog::buildPastnoFilePath($count, $past_dir);
        // ゼロから作るのでログを初期化する
        $past = "";
    }

    // 追加で書き込むHTMLの作成
    $dat = Pastlog::buildPastLogHtml($data, $autolink);

    // ログ(.html)の書き込み
    Pastlog::writePastLog($pastfile, $dat, $past);
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
        $view_model = new ViewModel();
        $config = new Config();
        $htmlw = $config->getConfig('htmlw');
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        if (!$script_name) {
            throw new Exception("スクリプト名を取得できません。");
        }

        // ルーティング
        switch ($mode) {
            case 'regist':
                if (check_spam()) {
                    throw new Exception("指定されたIPからは投稿できません。");
                }

                // *ログ書き込み
                try {
                    regist();
                } catch (Exception $e) {
                    $view_model->error($e->getMessage());
                }
                // トップページをHTMLに書き出す
                if ($htmlw) {
                    $this->MakeHtml();
                }

                // *転送
                header("Location: {$script_name}");
                break;
            case 'admin':
                // *管理
                $apass = filter_input(INPUT_POST, 'apass');
                if ($this->adminAuth($apass)) {
                    $this->adminDel();
                    $view_model->admin();
                } else {
                    $view_model->error('パスワードが違います');
                }
                break;
            case 'usrdel':
                // *ユーザー権限による書き込みの削除
                try {
                    $this->usrdel();
                } catch (Exception $e) {
                    $view_model->error($e->getMessage());
                }
                // トップページをHTMLに書き出す
                if ($htmlw) {
                    $this->MakeHtml();
                }

                // *転送
                header("Location: {$script_name}");
                break;
            case 'past':
                // 過去ログモード
                $view_model->pastView();
                break;
            default:
                $view_model->main();
                break;
        }
    }

    function usrdel()
    {
        $pwd = filter_input(INPUT_POST, 'pwd');
        $no = filter_input(INPUT_POST, 'no');
        $logfile = Config::get('logfile');

        //ユーザー削除
        if (!isset($no) || empty($no) || !isset($pwd) || empty($pwd)) {
            throw new Exception("削除Noまたは削除キーが入力モレです");
        }

        $pass = Log::searchDelPass($logfile, $no);

        if (isset($pass) === false) {
            throw new Exception("該当記事が見当たりません");
        } else if ($pass === "") {
            throw new Exception("該当記事には削除キーが設定されていません");
        }

        // 削除キーを照合
        $match = password_verify($pwd, $pass);
        if (($match != $pass)) {
            throw new Exception("削除キーが違います");
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
        $view_model = new ViewModel();
        $config = new Config();
        $html_file = $config->getConfig('html_file');

        // HTML生成
        ob_start();
        $view_model->main();
        $buf = ob_get_contents();
        ob_end_clean();

        // バッファをHTMLファイルに書き込み
        $handle = @fopen($html_file, "w");
        flock($handle, LOCK_EX);
        fputs($handle, $buf);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}

$mode = (isset($_GET['mode'])) ? filter_input(INPUT_GET, 'mode') : filter_input(INPUT_POST, 'mode');

$main = new Main();
try {
    $main->index($mode);
} catch (Exception $e) {
    echo $e->getMessage();
}
