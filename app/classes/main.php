<?php

/**
* メインループ用クラス
* ルーティング+コントローラ
*/
class Main
{
    public $vm;
    private $config;
    private $input;

    public function __construct(Config $config, Input $input)
    {
        $this->config = $config;
        $this->input = $input;
        $this->vm = new ViewModel($config);
    }
}
