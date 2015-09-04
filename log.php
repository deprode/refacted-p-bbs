<?php

/* 使用するファイルロックのタイプ（mkdir=1 flock=2 使わない=0）*/
define("LOCKEY", 2);        //通常は2でOK

/* mkdirロックを使う時はlockという名でディレクトリを作成して777にしてください */
define("LOCK" , "lock/plock");  //lockの中に作るロックファイル名

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

    // ディレクトリロック
    public function lockDir($name = "")
    {
        if ($name == "") {
            $name = "lock";
        }

        // 3分以上前のディレクトリなら解除失敗とみなして削除
        if ((file_exists($name)) && filemtime($name) < time() - 180) {
            @rmdir($name);
        }

        do {
            if (@mkdir($name, 0777)) {
                return 1;
            }
            sleep(1); // 一秒待って再トライ
            $i++;
        } while ($i < 5);

        return 0;
    }

    // ディレクトリロック解除
    public function unlockDir($name = "")
    {
        if ($name == "") {
            $name = "lock";
        }

        @rmdir($name);
    }

    /**
     * ログを更新する
     * @param $filename ログファイル名
     * @param $arrline 書き換えるデータ
     */
    public static function renewLog($filename, $arrline)
    {
        if (LOCKEY == 1) {
            if (self::lockDir(LOCK) === 0) {
                throw new Exception("ロックエラー<br>しばらく待ってからにして下さい");
            }
        }

        $rp = fopen($filename, "w");
        if (LOCKEY == 2) {
            flock($rp, 2);
        }
        foreach ($arrline as $val) {
            fputs($rp, $val);
        }
        fclose($rp);

        if (LOCKEY == 1) {
            self::unlockDir(LOCK);
        }

    }
}