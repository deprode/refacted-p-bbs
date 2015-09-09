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
}