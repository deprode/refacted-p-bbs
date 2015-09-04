<?php

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

}