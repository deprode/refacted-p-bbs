<?php

/**
* Validation
*/
class Validation
{
    private $white_arguments = [
        "mode" => ["alpha"],
        "page" => ["number"],
        "pno"  => ["number"],
        "name" => ["utf-8"],
        "email" => ["email"],
        "sub" => ["utf-8"],
        "com" => ["utf-8"],     // 本文
        "url" => ["url"],
        "password" => ["alpha", "number"],
        "no"   => ["number"],
        "pwd" => ["alpha", "number"],
        "apass" => ["utf-8"],
        "del" => ["number"]
    ];

    function __construct()
    {

    }

    public static function h($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
    }

    public static function isEmpty($s, $multiline = false)
    {
        if ($multiline) {
            return (empty($s) || preg_match("/^( |　|\t|\r|\n)*$/", $s));
        }
        return (empty($s) || preg_match("/^( |　)*$/", $s));
    }

    public static function overLength($s, $len)
    {
        return (mb_strlen($s) > $len);
    }
}