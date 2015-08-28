<?php

const CONFIG_FILE_NAME = 'config.ini';

/**
* 設定、設定ファイル読み込み
*/
class Config
{
    private $config;

    function __construct()
    {
        $this->config = parse_ini_file(CONFIG_FILE_NAME);
        if ($this->config === false) {
            error("設定ファイルが読み込めません。書式とエンコードを確認してください。");
        }
    }

    function __get($key)
    {
        return (isset($this->config[$key])) ? $this->config[$key] : null;
    }

    function getConfig($key)
    {
        return $this->__get($key);
    }
}
