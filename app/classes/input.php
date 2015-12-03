<?php

/**
* filter_inputのラッパークラス
*/
class Input
{
    /**
     * @param string $name 取得する変数の名前
     */
    public function get($name)
    {
        return filter_input(INPUT_GET, $name);
    }

    public function post($name)
    {
        return filter_input(INPUT_POST, $name);
    }

    public function server($name)
    {
        return filter_input(INPUT_SERVER, $name);
    }

    public function cookie($name)
    {
        return filter_input(INPUT_COOKIE, $name);
    }

    public function session($name)
    {
        return (isset($_SESSION) && isset($_SESSION[$name])) ? $_SESSION[$name] : null;
    }
}