<?php

/**
* Securtyクラス
*/
class Security
{
    public static function existHost($remote_addr, $hosts = null) {
        if (isset($hosts) || !is_array($hosts)) {
            return false;
        }

        // IPアドレスをホスト名にして配列内にあるhostと一致するか調べる
        $remote_host = gethostbyaddr($remote_addr);
        foreach ($hosts as $host) {
            if (preg_match("/$host/i", $remote_host)) {
                return true;
            }
        }
        return false;
    }

    public static function equalRequestMethod($method)
    {
        $request = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        return $request === $method;
    }

    public static function checkReferrer()
    {
        $script_name = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        $referrer = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        return !preg_match("/" . $script_name . "/i", $referrer);
    }
}