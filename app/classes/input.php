<?php

/**
 * filter_inputのラッパークラス
 */
class Input
{
    /**
     * @param string $name 取得する変数の名前
     * @return mixed
     */
    public function get($name)
    {
        return filter_input(INPUT_GET, $name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function post($name)
    {
        return filter_input(INPUT_POST, $name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function server($name)
    {
        return filter_input(INPUT_SERVER, $name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function cookie($name)
    {
        return filter_input(INPUT_COOKIE, $name);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function session($name)
    {
        return (isset($_SESSION) && isset($_SESSION[$name])) ? $_SESSION[$name] : null;
    }

    /**
     * ホスト名を取得する
     * @return string アクセス元のホスト名
     */
    public static function host()
    {
        $input = new Input();
        $host = $input->server('REMOTE_HOST');
        $addr = $input->server('REMOTE_ADDR');
        if ($host == "" || $host == $addr) {
            //gethostbyddrが使えるか
            $host = @gethostbyaddr($addr);
        }

        return $host;
    }
}