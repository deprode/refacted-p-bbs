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

    // 最大行チェック
    public static function overMaxline($com, $maxline = 0)
    {
        // \n数える
        $com = str_replace("\r\n", "\r", $com);
        $com = str_replace("\r", "\n", $com);
        $count = preg_match_all('/\n/', $com);

        return ($count > $maxline);
    }

    // 二重投稿チェック
    public static function checkDuplicatePost($name, $com, Post $post)
    {
        /* 注: 元の二重投稿チェックが名前と投稿内容のチェックだったのでそうしている */
        return ($name === $post->name && $com === $post->body);
    }

    // 短時間に連続投稿しているかチェック
    public static function checkShortTimePost($w_regist, $time, $prev_time)
    {
        return ($w_regist && ($time - $prev_time) < $w_regist);
    }
}
