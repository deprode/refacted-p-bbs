<?php

/**
 * HTML書き出しを行う
 */
class Html
{
    /**
     * メイン表示をHtmlに書き出す
     * @param ViewModel $view_model
     * @param Input $input
     * @param $script_name
     * @param string $filepath
     */
    public static function makeHtml(ViewModel $view_model, Input $input, $script_name, $filepath = "pbbs.html")
    {
        $no = $input->get('no');
        $page = $input->get('page');
        $p_bbs = $input->cookie('p_bbs');

        // HTML生成
        ob_start();
        $view_model->main("", $no, $page, $p_bbs, $script_name);
        $buf = ob_get_contents();
        ob_end_clean();

        // バッファをHTMLファイルに書き込み
        $handle = @fopen($filepath, "w");
        flock($handle, LOCK_EX);
        fputs($handle, $buf);
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}