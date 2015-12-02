<?php

/**
* Securtyクラス
*/
class Security
{
    /**
     * 配列に該当のホストが入っているか調べる
     * IPをホストに変換して、指定されたホストが入っているか検索する
     * @param string $remote_addr アクセス元のIPアドレス
     * @param array $hosts ホスト一覧
     * @return boolean ホストが一覧から見つかったらtrue,なければfalse
     */
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

    /**
     * リクエストメソッドが指定されたものと同一か調べる
     * @param string $method 指定するメソッド（GET,POSTなど）
     * @param boolean 同一であればtrue、そうでなければfalse
     */
    public static function equalRequestMethod($method)
    {
        $input = new Input();
        $request = $input->server('REQUEST_METHOD');
        return $request === $method;
    }

    /**
     * 外部から投稿されていないかチェック
     * 注：リファラは偽装される可能性があるのでこれだけでは不十分
     * @return 外部から投稿されていればtrue,そうでなければfalse
     */
    public static function checkReferrer()
    {
        $input = new Input();
        $script_name = $input->server('SCRIPT_NAME');
        $referrer = $input->server('HTTP_REFERER');
        return !preg_match("/" . $script_name . "/i", $referrer);
    }
}