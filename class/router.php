<?php

/**
 * ルーティング用コントローラ
 */
class Router
{
    private $main;
    private $config;

    public function __construct()
    {
        $this->config = new Config();
        $this->main = new Main($this->config);
    }

    /**
     * ルーティングを行う
     * @param string $mode 分岐用クエリ文字列
     */
    function index($mode)
    {
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        if (!$script_name) {
            throw new Exception("スクリプト名を取得できません。");
        }

        $htmlw = $this->config->get('htmlw');

        // ルーティング
        switch ($mode) {
            case 'regist':
                if (check_spam()) {
                    throw new Exception("指定されたIPからは投稿できません。");
                }

                // *ログ書き込み
                try {
                    $this->main->regist();
                } catch (Exception $e) {
                    $this->main->vm->error($e->getMessage());
                }
                // トップページをHTMLに書き出す
                if ($htmlw) {
                    $this->main->MakeHtml();
                }

                // *転送
                header("Location: {$script_name}");
                break;
            case 'admin':
                // *管理
                $apass = filter_input(INPUT_POST, 'apass');
                if ($this->main->adminAuth($apass)) {
                    $this->main->adminDel();
                    $this->main->vm->admin();
                } else {
                    $this->main->vm->error('パスワードが違います');
                }
                break;
            case 'usrdel':
                // *ユーザー権限による書き込みの削除
                try {
                    $this->main->usrdel();
                } catch (Exception $e) {
                    $this->main->vm->error($e->getMessage());
                }
                // トップページをHTMLに書き出す
                if ($htmlw) {
                    $this->main->MakeHtml();
                }

                // *転送
                header("Location: {$script_name}");
                break;
            case 'past':
                // 過去ログモード
                $this->main->vm->pastView();
                break;
            default:
                $this->main->vm->main();
                break;
        }
    }
}