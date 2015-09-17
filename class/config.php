<?php

/**
* 設定、設定ファイル読み込み
*/
class Config
{
    // 設定情報
    private static $config = null;

    /**
     * コンストラクタ
     * @param $string filename 設定ファイル名（ファイルパス）
     */
    function __construct($filename = CONFIG_FILE_NAME)
    {
        self::initConfig($filename);
    }

    /**
     * 設定情報の初期化
     * @param string $filename 設定ファイル名（ファイルパス）
     */
    private static function initConfig($filename = CONFIG_FILE_NAME)
    {
        self::$config = parse_ini_file($filename);
        if (self::$config === false) {
            throw new Exception("設定ファイルが読み込めません。書式とエンコードを確認してください。");
        }
    }

    /**
     * 設定の取得
     * @param string $key 設定のキー（iniファイルの）
     */
    private static function _getConfig($key)
    {
        if (self::$config === null) {
            self::initConfig();
        }
        return (isset(self::$config[$key])) ? self::$config[$key] : null;
    }

    /**
     * 設定の取得（動的時）
     * @param string $key 設定のキー（iniファイルの）
     */
    public function __get($key)
    {
        return self::_getConfig($key);
    }

    /**
     * 設定の取得（静的時）
     * @param string $key 設定のキー（iniファイルの）
     */
    public static function get($key)
    {
        return self::_getConfig($key);
    }

    public static function getConfig($key)
    {
        return self::_getConfig($key);
    }
}
