<?php

/**
 * テンプレートを読み込んで、変数展開するだけのクラス
 */
class Template
{
    function show($tpl_file_path)
    {
        extract((array)$this);
        include($tpl_file_path);
    }
}