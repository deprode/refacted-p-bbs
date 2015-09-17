<?php

/**
* 設定、設定ファイル読み込み
*/
class Config
{
    private static $config = null;

    function __construct($filename = CONFIG_FILE_NAME)
    {
        self::initConfig($filename);
    }

    private static function initConfig($filename = CONFIG_FILE_NAME)
    {
        self::$config = parse_ini_file($filename);
        if (self::$config === false) {
            throw new Exception("設定ファイルが読み込めません。書式とエンコードを確認してください。");
        }
    }

    private static function _getConfig($key)
    {
        if (self::$config === null) {
            self::initConfig();
        }
        return (isset(self::$config[$key])) ? self::$config[$key] : null;
    }

    public function __get($key)
    {
        return self::_getConfig($key);
    }

    public static function get($key)
    {
        return self::_getConfig($key);
    }

    public static function getConfig($key)
    {
        return self::_getConfig($key);
    }
}
