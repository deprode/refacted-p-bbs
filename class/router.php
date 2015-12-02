<?php

/**
 * ルーティング用コントローラ
 */
class Router
{
    private $main;
    private $config;
    private $input;

    public function __construct()
    {
        $this->config = new Config();
        $this->input = new Input();
        $this->main = new Main($this->config, $this->input);
    }

    /**
     * ルーティングを行う
     * @param string $mode 分岐用クエリ文字列
     */
    function index($mode)
    {
        $script_name = $this->input->server('SCRIPT_NAME');
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
                    // トップページをHTMLに書き出す
                    if ($htmlw) {
                        $this->main->MakeHtml();
                    }

                    // *転送
                    header("Location: {$script_name}");
                } catch (Exception $e) {
                    $this->main->vm->error($e->getMessage());
                }
                break;
            case 'admin':
                // *管理
                $apass = $this->input->post('apass');
                if ($this->main->adminAuth($apass)) {
                    $this->main->adminDel();
                    $this->main->vm->admin($apass, $script_name);
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
                $pno = $this->input->get('pno');
                try {
                    $this->main->vm->pastView($pno, $script_name);
                } catch (Exception $e) {
                    $this->main->vm->error($e->getMessage());
                }
                break;
            default:
                $no = $this->input->get('no');
                $page = $this->input->get('page');
                $p_bbs = $this->input->cookie('p_bbs');
                try {
                    $this->main->vm->main($mode, $no, $page, $p_bbs, $script_name);
                } catch (Exception $e) {
                    $this->main->vm->error($e->getMessage());
                }
                break;
        }
    }
}