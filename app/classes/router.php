<?php

/**
 * ルーティング用コントローラ
 */
class Router
{
    private $main;
    private $config;
    private $input;
    private $view_model;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->config = new Config();
        $this->input = new Input();
        $this->view_model = new ViewModel($this->config);
    }

    /**
     * ルーティングを行う
     * @param string $mode 分岐用クエリ文字列
     * @throws Exception
     */
    public function index($mode)
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
                $regist = new Regist($this->config, $this->input);
                try {
                    $regist->registPost();
                    // トップページをHTMLに書き出す
                    if ($htmlw) {
                        $filepath = $this->config->get('html_file');
                        Html::makeHtml($this->view_model, $this->input, $script_name, $filepath);
                    }

                    // *転送
                    header("Location: {$script_name}");
                } catch (Exception $e) {
                    $this->view_model->error($e->getMessage());
                }
                break;
            case 'admin':
                // *管理
                $apass = $this->input->post('apass');
                $admin_pass = $this->config->get('admin_pass');
                if (isset($apass)) {
                    if (Security::adminAuth($admin_pass, $apass)) {
                        $delete = new Delete($this->config, $this->input);
                        $delete->delFromAdmin();
                        $this->view_model->admin($apass, $script_name);
                    } else {
                        $this->view_model->error('パスワードが違います');
                    }
                } else {
                    $this->view_model->adminLogin($script_name);
                }
                break;
            case 'usrdel':
                // *ユーザー権限による書き込みの削除
                try {
                    $delete = new Delete($this->config, $this->input);
                    $delete->delFromUser();
                } catch (Exception $e) {
                    $this->view_model->error($e->getMessage());
                }
                // トップページをHTMLに書き出す
                if ($htmlw) {
                    $filepath = $this->config->get('html_file');
                    Html::makeHtml($this->view_model, $this->input, $script_name, $filepath);
                }

                // *転送
                header("Location: {$script_name}");
                break;
            case 'past':
                // 過去ログモード
                $pno = $this->input->get('pno');
                try {
                    $this->view_model->pastView($pno, $script_name);
                } catch (Exception $e) {
                    $this->view_model->error($e->getMessage());
                }
                break;
            default:
                $no = $this->input->get('no');
                $page = $this->input->get('page');
                $p_bbs = $this->input->cookie('p_bbs');
                try {
                    $this->view_model->main($mode, $no, $page, $p_bbs, $script_name);
                } catch (Exception $e) {
                    $this->view_model->error($e->getMessage());
                }
                break;
        }
    }
}