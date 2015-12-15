<?php

/**
* 投稿内容を格納するクラス
*
* あらかじめ設定されたキーでのみ代入が可能。
*/
class Post
{
    /**
     * 投稿のデフォルト値。set時にこの変数にキーがない場合は代入されない
     */
    private static $post_default = [
        'no'       => 0,
        'date'     => '',
        'name'     => '',
        'email'    => null,
        'subject'  => null,
        'body'     => '',
        'url'      => null,
        'host'     => null,
        'delpass'  => null,
        'unixtime' => null
    ];
    /**
     * 投稿の内容
     */
    public $post = [];

    /**
     * コンストラクタ。キーに値がない場合、デフォルト値が設定される
     * @param array $post 投稿データ。キーがある配列。
     */
    public function __construct($post = [])
    {
        $this->post['no']       = isset($post['no'])       ? $post['no']       : self::$post_default['no'];
        $this->post['date']     = isset($post['date'])     ? $post['date']     : self::$post_default['date'];
        $this->post['name']     = isset($post['name'])     ? $post['name']     : self::$post_default['name'];
        $this->post['email']    = isset($post['email'])    ? $post['email']    : self::$post_default['email'];
        $this->post['subject']  = isset($post['subject'])  ? $post['subject']  : self::$post_default['subject'];
        $this->post['body']     = isset($post['body'])     ? $post['body']     : self::$post_default['body'];
        $this->post['url']      = isset($post['url'])      ? $post['url']      : self::$post_default['url'];
        $this->post['host']     = isset($post['host'])     ? $post['host']     : self::$post_default['host'];
        $this->post['delpass']  = isset($post['delpass'])  ? $post['delpass']  : self::$post_default['delpass'];
        $this->post['unixtime'] = isset($post['unixtime']) ? $post['unixtime'] : self::$post_default['unixtime'];
    }

    /**
     * セッター
     * @param string $key
     * @param mixed $val
     */
    public function __set($key, $val)
    {
        if (array_key_exists($key, self::$post_default)) {
            $this->post[$key] = $val;
        }
    }

    /**
     * ゲッター
     * @param string $key
     */
    public function __get($key)
    {
        return $this->post[$key];
    }

    /**
     * 渡された文字列からPostを作成する
     *
     * @param string $data 過去ログ形式の文字列
     * @return Post 文字列から作成された投稿データ
     */
    public static function buildPost($data)
    {
        if (!is_string($data) || mb_strlen($data) === 0) {
            return null;
        }
        if (mb_substr_count($data, "<>") != 9) {
            return null;
        }
        $data = trim($data);

        list($no, $date, $name, $email, $sub, $body, $url, $host, $password, $unixtime) = explode("<>", $data);

        return new Post([
                    'no'       => $no,
                    'date'     => $date,
                    'name'     => $name,
                    'email'    => $email,
                    'subject'  => $sub,
                    'body'     => $body,
                    'url'      => $url,
                    'host'     => $host,
                    'delpass'  => $password,
                    'unixtime' => $unixtime
                ]);
    }

    /**
     * 投稿データを過去ログ形式で出力する
     * @return string 過去ログ形式のデータ
     */
    public function getPostStr()
    {
        return sprintf("%d<>%s<>%s<>%s<>%s<>%s<>%s<>%s<>%s<>%d\n",
                        $this->post['no'],
                        $this->post['date'],
                        $this->post['name'],
                        $this->post['email'],
                        $this->post['subject'],
                        $this->post['body'],
                        $this->post['url'],
                        $this->post['host'],
                        $this->post['delpass'],
                        $this->post['unixtime']);
    }

}
