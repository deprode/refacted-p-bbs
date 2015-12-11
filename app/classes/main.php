<?php

/**
* メインループ用クラス
* ルーティング+コントローラ
*/
class Main
{
    public $vm;
    private $config;
    private $input;

    public function __construct(Config $config, Input $input)
    {
        $this->config = $config;
        $this->input = $input;
        $this->vm = new ViewModel($config);
    }

    /**
     * ユーザーによる削除を行う
     */
    function usrdel()
    {
        $pwd = $this->input->post('pwd');
        $no = $this->input->post('no');
        $logfile = $this->config->get('logfile');

        if (!Security::checkToken($this->input->post('token'))) {
            throw new Exception("不正な操作が行われました");
        }

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
     * 管理者による投稿の削除を行う
     */
    function adminDel()
    {
        if (!Security::checkToken($this->input->post('token'))) {
            throw new Exception("不正な操作が行われました");
        }

        $logfile = $this->config->get('logfile');
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
        $view_model = new ViewModel($this->config);
        $html_file = $this->config->get('html_file');

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
