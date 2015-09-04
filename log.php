<?php

/**
* Log
*/
class Log
{
    public static function getDataFromFile($filename)
    {
        return file($filename);
    }

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