<?php

/* 使用するファイルロックのタイプ（flock=2 使わない=0）*/
define("LOCKEY", 2);        //通常は2でOK

/**
* 掲示板ログファイルの書き込み、読み込みを管理する
*/
class Log
{
    /**
     * ファイルデータを返す
     *
     * @param string $filename ファイル名
     * @return array or false ファイルがない場合はfalse、それ以外はログを配列で返す
     */
    public static function getDataFromFile($filename)
    {
        return file($filename);
    }

    /**
     * 返信のデータを返す
     * @param $filename ファイル名
     * @param $no 読み込むレス番号（≠行番号）
     * @return array or null 読み込めた場合は返信のデータ、そうでなければnull
     */
    public static function getResData($filename, $no)
    {
        $res = Log::getDataFromFile($filename);
        if ($res === false) {
            return null;
        }

        foreach ($res as $key => $value) {
            list($rno, $date, $name, $email, $sub, $com, $url) = explode("<>", $value);
            if ($no == "$rno") {
                return [
                    'no' => $rno,
                    'date' => $date,
                    'name' => $name,
                    'email' => $email,
                    'sub' => $sub,
                    'com' => $com,
                    'url' => $url
                ];
            }
        }
        return null;
    }

    /**
     * ログを更新する
     * @param $filename ログファイル名
     * @param $arrline 書き換えるデータ
     */
    public static function renewLog($filename, $arrline)
    {
        $rp = fopen($filename, "w");
        if (LOCKEY == 2) {
            flock($rp, LOCK_EX);
        }
        foreach ($arrline as $val) {
            fputs($rp, $val);
        }
        if (LOCKEY == 2) {
            fflush($rp);
            flock($rp, LOCK_UN);
        }
        fclose($rp);

    }

    /**
     * 削除パスの取得
     * @param $filename ログファイル名
     * @param $no 削除パスを取得するレス番号
     * @return string|null 削除パス。見つからなければnull。削除パスが設定されていなければ空文字列。
     */
    public static function searchDelPass($filename, $no)
    {
        $res = Log::getDataFromFile($filename);
        if ($res === false) {
            return null;
        }

        $pass = null;

        // 同じ番号の削除パスを検索
        foreach ($res as $lines) {
            list($ono, , , , , , , , $opas) = explode("<>", $lines);
            if (intval($no, 10) === intval($ono, 10)) {
                $pass = $opas;
                break;
            }
        }

        return $pass;
    }

    /**
     * ユーザーによる削除
     * @param $filename ログファイル名
     * @param $no 削除するレス番号
     */
    public static function removeRes($filename, $no)
    {
        $res = Log::getDataFromFile($filename);
        if ($res === false) {
            return;
        }

        // 削除するレス以外を収集
        $pushlog = [];
        foreach ($res as $lines) {
            list($ono, , , , , , , , ) = explode("<>", $lines);
            if (intval($no, 10) !== intval($ono, 10)) {
                $pushlog[] = $lines;
            }
        }

        // 該当レス以外のログで上書き
        Log::renewlog($filename, $pushlog);
    }
}