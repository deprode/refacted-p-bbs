<?php

/**
 * テンプレートを読み込んで、変数展開するだけのクラス
 */
class Template
{
    /**
     * パスを読み込んで、テンプレートを展開する
     * @param string $tpl_file_path 読み込むテンプレートファイルのパス
     */
    function show($tpl_file_path)
    {
        extract((array)$this);
        include($tpl_file_path);
    }
}