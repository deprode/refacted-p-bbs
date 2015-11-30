<?php

require_once 'template.php';

/**
* ViewModelクラス
* Viewを表示させるためにデータのロード、形成、テンプレートの表示を行う
*/
class ViewModel
{
    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * エラーを表示する
     * @param string $mes エラーメッセージ
     */
    public static function error($mes)
    {
        $tpl = new Template();

        $tpl->mes = nl2br($mes);

        $tpl->show('template/error.tpl.php');

        exit;
    }

    /**
     * URLをHTML形式のリンクを作成する
     * @param string $proto リンクに含めるURL
     * @return string HTML形式の文字列
     */
    public static function autoLink($proto)
    {
        //自動リンク5/25修正
        $proto = preg_replace("/(https?|ftp|news)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $proto);
        return $proto;
    }

    /**
     * 文字列に含まれる改行タグを改行文字に変換する
     * @param string $string 入力文字列
     * @return string 変換後の文字列
     */
    public static function br2nl($string)
    {
        return preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/i', "\n", $string);
    }

    /**
     * 管理画面を表示する
     */
    public function admin()
    {
        $tpl = new Template();

        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        $apass = filter_input(INPUT_POST, 'apass');
        $logfile = $this->config->get('logfile');
        $body = $this->config->get('body');
        $title1 = $this->config->get('title1');

        // 削除モードのログを読み込み
        $delmode = file($logfile);
        $logs = [];
        if (is_array($delmode)) {
            foreach ($delmode as $l => $val) {
                list($no, $date, $name, $email, $sub, $com, ,
                    $host, , $time) = explode("<>", $val);

                list($date, $dmy) = split("\(", $date);
                if ($email) {
                    $name = "<a href=\"mailto:$email\">$name</a>";
                }
                $com = str_replace("<br>", "", $com);
                $com = htmlspecialchars($com);
                if (strlen($com) > 40) {
                    $com = substr($com, 0, 38) . " ...";
                }

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

        // 削除画面を表示
        $tpl->c = $this->config;
        $tpl->script_name = $script_name;

        $tpl->apass = $apass;

        $tpl->delmode = $logs;
        $tpl->show('template/admin.tpl.php');
    }

    /**
     * 掲示板のログを読み出して返す
     * @param string $view 掲示板のログデータ
     * @param integer $page 読み込みを開始するページ
     * @param integer $page_def 一回で表示する件数
     * @return array 表示に使うログ
     */
    public function createMain($view, $page, $page_def)
    {
        $dat = [];

        // ログファイルを読み出し、件数を数える
        $total = sizeof($view);
        $total2 = $total;

        // 開始ページ（レス番号）数の設定
        (isset($page)) ? $start = $page : $start = 0;
        $end = $start + $page_def;
        $st = $start + 1;

        $p = 0;
        for ($s = $start; $s < $end && $p < count($view) && $s < count($view); $s++) {
            if (!$view[$s]) {
                break;
            }

            list($no, $now, $name, $email, $sub, $com, $url,
                $host,) = explode("<>", $view[$s]);

            // タグ禁止
            $sub = Validation::h($sub);
            $name = Validation::h($name);
            $email = Validation::h($email);
            $url = Validation::h($url);
            $com = ViewModel::br2nl($com);
            $com = Validation::h($com);
            $com = str_replace("&amp;", "&", $com);
            $com = nl2br($com);

            // URL自動リンク
            $autolink = $this->config->get('autolink');
            if ($autolink) {
                $com = ViewModel::autoLink($com);
            }
            // Host表示形式
            $hostview = $this->config->get('hostview');
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

    /**
     * 引用を返す
     * @param $logfile 掲示板の過去ログファイルのパス
     * @param $no 引用を行う投稿番号
     */
    public function getResMsg($logfile, $no)
    {
        //レスの場合
        $data = Log::getResData($logfile, $no);
        if ($data === null) {
            ViewModel::error("該当記事が見つかりません");
        }

        $sub = $data['sub'];
        $com = $data['com'];
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

    /**
     * トップページを表示する
     */
    public function main()
    {
        $mode = filter_input(INPUT_GET, 'mode');
        $no = filter_input(INPUT_GET, 'no');
        $page = filter_input(INPUT_GET, 'page');

        $p_bbs = filter_input(INPUT_COOKIE, 'p_bbs');
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');

        $r_name = $r_mail = null;
        $r_sub = $r_com = $r_pass = null;

        $tpl = new Template();

        $tpl->c = $this->config;
        $tpl->script_name = $script_name;

        // * cookieには名前とメールが入っているので呼び出してるっぽい
        $htmlw = $this->config->get('htmlw');
        if (!$htmlw && isset($p_bbs)) {
            list($r_name, $r_mail) = explode(",", $p_bbs);
        }
        $logfile = $this->config->get('logfile');
        if ($mode == "resmsg") {
            list($r_sub, $r_com) = $this->getResMsg($logfile, $no);
        }

        $tpl->r_name = $r_name;
        $tpl->r_mail = $r_mail;
        $tpl->r_sub = $r_sub;
        $tpl->r_com = $r_com;
        $tpl->r_pass = $r_pass;

        $page_def = $this->config->get('page_def');
        $view = file($logfile);
        $tpl->dat = $this->createMain($view, $page, $page_def);

        $tpl->page = $page ?: 0;
        $tpl->start = (isset($page)) ? $page + 1 : 1;
        $tpl->total = count($view);

        $tpl->show('template/index.tpl.php');
    }

    /**
     * 過去ログを表示する
     */
    public function pastView()
    {
        $pno = filter_input(INPUT_GET, 'pno');
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');

        $tpl = new Template();

        $past_no = $this->config->get('past_no');
        $past_dir = $this->config->get('past_dir');

        $pno = htmlspecialchars($pno);

        $fc = @fopen($past_no, "r") or die(__LINE__ . $past_no . "が開けません");
        $count = fgets($fc, 10);
        fclose($fc);
        if (!$pno) {
            $pno = $count;
        }

        $pastfile = $past_dir . "index" . $pno . ".html";
        if (!file_exists($pastfile)) {
            ViewModel::error("<br>過去ログがみつかりません");
        }

        $tpl->pno = $pno;
        $tpl->count = $count;
        $tpl->pastfile = $pastfile;

        $tpl->c = $this->config;
        $tpl->script_name = $script_name;
        $tpl->show('template/past.tpl.php');
    }
}