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
    public static function equalRequestMethod($input, $method)
    {
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

    /**
     * トークンを生成し取得する
     */
    public static function generateToken()
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }

    /**
     * トークンのチェック
     */
    public static function checkToken($token)
    {
        $input = new Input();
        $session_token = $input->session('token');
        if (!$token || $token != $session_token) {
            return false;
        }
        return true;
    }

    /**
     * 管理者パスワードを検証する
     * @param stirng $admin_password 管理用パスワード
     * @param stirng $password パスワード
     * @return boolean パスワードが一致していたらtrue,一致していなければfalse
     */
    public static function adminAuth($admin_pass, $password)
    {
        if (!isset($admin_pass)) {
            // MEMO:管理パスワードを設定してなければ検証しない
            return true;
        }
        if (!isset($password)) {
            return false;
        }
        if (isset($password) && $password !== $admin_pass) {
            return false;
        }
        return true;
    }
}