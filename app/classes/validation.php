<?php

/**
* バリデーション。入力値の検査を行う
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

    /**
     * htmlspecialcharsのエイリアス
     * @param string $string 変換対象の文字列
     * @return string 変換後の文字列
     */
    public static function h($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
    }

    /**
     * 文字列が空かどうかを調べる
     * 文字列が空のときと、半角空白、全角空白、タブ、改行をチェックする
     * @param string $s 対象の文字列
     * @param string $multiline 複数行か（trueの時はタブ、改行をチェックしない）
     * @param boolean 空の時はtrue
     */
    public static function isEmpty($s, $multiline = false)
    {
        if ($multiline) {
            return (empty($s) || preg_match("/^( |　|\t|\r|\n)*$/", $s));
        }
        return (empty($s) || preg_match("/^( |　)*$/", $s));
    }

    /**
     * 文字列が指定された長さを超えていないか調べる
     * @param string $s 文字列
     * @param integer $len 長さ
     */
    public static function overLength($s, $len)
    {
        return (mb_strlen($s) > $len);
    }

    /**
     * 文字列が指定された行数を超えていないか調べる
     * @param string $com 文字列
     * @param integer $maxline 最大行数
     */
    public static function overMaxline($com, $maxline = 0)
    {
        // \n数える
        $com = str_replace("\r\n", "\r", $com);
        $com = str_replace("\r", "\n", $com);
        $count = preg_match_all('/\n/', $com);

        return ($count > $maxline);
    }

    /**
     * 二重投稿をチェックする
     * @param string $name 投稿者名
     * @param string $com 投稿本文
     * @param Post $post 前回の投稿内容
     * @return boolean 二重投稿であればtrue,そうでなければfalse
     */
    public static function checkDuplicatePost($name, $com, Post $post)
    {
        /* 注: 元の二重投稿チェックが名前と投稿内容のチェックだったのでそうしている */
        return ($name === $post->name && $com === $post->body);
    }

    /**
     * 連続投稿しているかチェック
     * @param integer $w_regist 許される投稿間隔（秒）
     * @param integer $time unixtime形式の投稿時刻
     * @param integer $prev_time unixtime形式の前回の投稿時刻
     * @return boolean 投稿間隔より短ければtrue,そうでなければfalse
     */
    public static function checkShortTimePost($w_regist, $time, $prev_time)
    {
        return ($w_regist && ($time - $prev_time) < $w_regist);
    }
}
