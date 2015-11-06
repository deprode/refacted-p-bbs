<?php

/**
* メインループ用クラス
* ルーティング+コントローラ
*/
class Main
{
    public $vm;

    public function __construct()
    {
        $this->vm = new ViewModel();
    }

    /**
     * 投稿内容を検査する
     * @param string $name 名前
     * @param string $sub 題名
     * @param string $com 投稿内容
     * @return string エラーメッセージ
     */
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

    /**
     * ホスト名を取得する
     * @return string アクセス元のホスト名
     */
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

    /**
     * ユーザ名とメールアドレスをクッキーに保存する
     * @param string $name 名前
     * @param string $email Eメールアドレス
     */
    function saveUserData($name, $email)
    {
        $now = new DateTime();
        $limit = 14 * 24 * 3600; /* 2週間で期限切れ */

        $cookvalue = implode(",", array($name, $email));
        setcookie("p_bbs", $cookvalue, $now->format('U') + $limit);
    }

    /**
     * 投稿内容から保存するデータを作成する
     * @param Post $prev 前回の投稿内容
     * @param string $email Eメールアドレス
     * @param string $name 名前
     * @param string $sub 題名
     * @param string $url URL
     * @param string $com 本文
     * @param string $password パスワード
     * @return string ログに保存可能な投稿データ
     */
    function buildMessageData($prev, $name, $email, $sub, $url, $com, $password)
    {
        $now = new DateTime(null, new DateTimeZone("Asia/Tokyo"));
        $nowtime = $now->format('U');

        // 記事Noを採番
        $no = ($prev) ? $prev->no + 1 : 1;

        // ホスト名を取得
        $host = $this->getHost();

        // 削除キーを暗号化
        $PW = '';
        if ($password) {
            $PW = password_hash($password, PASSWORD_DEFAULT);
        }

        if (Validation::isEmpty($sub)) {
            $sub = Config::get('mudai');
        }

        $now = $now->format("Y/m/d(D) H:i");
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

        return $new_msg;
    }

    /**
     * 投稿内容を記録する
     */
    function regist()
    {
        $name = filter_input(INPUT_POST, 'name');
        $email = filter_input(INPUT_POST, 'email');
        $sub = filter_input(INPUT_POST, 'sub');
        $url = filter_input(INPUT_POST, 'url');
        $com = filter_input(INPUT_POST, 'com');
        $password = filter_input(INPUT_POST, 'password');

        /*
         * セキュリティ処理
         */
        if (!Security::equalRequestMethod('POST')) {
            throw new Exception("不正な投稿をしないで下さい");
        }

        if (Config::get('GAIBU') && Security::checkReferrer()) {
            throw new Exception("外部から書き込みできません");
        }

        /**
         * バリデーション
         */
        $error_msg = $this->validationPost($name, $sub, $com);
        if (mb_strlen($error_msg) > 0) {
            throw new Exception($error_msg);
        }

        // 1つ前の書き込みを取得
        $logfile = Config::get('logfile');
        $prev_res = Log::getResDataForIndex($logfile, 0);

        // 二重投稿のチェック
        if ($prev_res && Validation::checkDuplicatePost($name, $com, $prev_res)) {
            throw new Exception("二重投稿は禁止です");
        }

        // 連続投稿のチェック
        $now = new DateTime();
        $nowtime = $now->format('U');
        $w_regist = Config::get('w_regist');

        if ($prev_res && Validation::checkShortTimePost($w_regist, $nowtime, $prev_res->unixtime)) {
            throw new Exception("連続投稿はもうしばらく時間を置いてからお願い致します");
        }

        // 最大行チェック
        if (Validation::overMaxline($com, Config::get('maxline'))) {
            throw new Exception("行数が長すぎますっ！");
        }


        // 記事整形
        $new_msg = $this->buildMessageData($prev_res, $name, $email, $sub, $url, $com, $password);

        // クッキー保存
        $this->saveUserData($name, $email);

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
                $this->pastLog($old_log[$s - 1]);
            }
        }

        //最大記事数処理
        $log_data = $old_log;
        array_splice($log_data, $max-1);
        $new_log = array_merge($new_log, $log_data);
        Log::renewlog($logfile, $new_log); //ログ更新
    }

    /**
     * 過去ログ作成
     * @param string $data 過去ログデータ（追加分）
     */
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
        $past = [];
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

    /**
     * ユーザーによる削除を行う
     */
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

    /**
     * 管理者パスワードを検証する
     * @param stirng $password パスワード
     * @return boolean パスワードが一致していたらtrue,一致していなければfalse
     */
    function adminAuth($password)
    {
        $admin_pass = Config::get('admin_pass');

        if (isset($password) && $password != $admin_pass) {
            return false;
        }
        return true;
    }

    /**
     * 管理者による投稿の削除を行う
     */
    function adminDel()
    {
        $logfile = Config::get('logfile');
        $del = isset($_POST['del']) ? (array)$_POST['del'] : [];
        $del = array_filter($del, 'is_string');

        // 削除処理
        Log::removePosts($logfile, $del);
    }

    /**
     * メイン表示をHtmlに書き出す
     */
    function MakeHtml()
    {
        $view_model = new ViewModel();
        $config = new Config();
        $html_file = $config->get('html_file');

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
