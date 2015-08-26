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

    public function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
    }

    public function isEmpty($s, $multiline = false) {
        if ($multiline) {
            return (!$s || preg_match("/^( |　|\t|\r|\n)*$/", $s));
        }
        return (!$s || preg_match("/^( |　)*$/", $s));
    }
/*
本文にリンクタグがあったらエラー
リクエストがPOST以外で投稿でエラー
外部からの投稿じゃないかチェック
\を削除
タグ禁止の場合、タグを除去
半角・全角空白除去後のemptyチェック
max_lengthチェック
禁止ワードチェック
改行文字を統一し、行数チェック
二重投稿チェック
連続投稿チェック

削除キー同一チェック
（ログ照合・記事noチェック）

パスワード同一チェック


ホワイトリストチェック?

*/
}
