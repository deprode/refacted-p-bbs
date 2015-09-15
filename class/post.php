<?php

/**
* Post
*/
class Post
{
    private static $post_default = [
        'no' => 0,
        'date' => '',
        'name' => '',
        'email' => null,
        'subject' => null,
        'body' => '',
        'url' => null,
        'host' => null,
        'delpass' => null,
        'unixtime' => null
    ];
    public $post = [];

    public function __construct($post = [])
    {
        $this->post['no']       = $post['no']       ?: self::$post_default['no'];
        $this->post['date']     = $post['date']     ?: self::$post_default['date'];
        $this->post['name']     = $post['name']     ?: self::$post_default['name'];
        $this->post['email']    = $post['email']    ?: self::$post_default['email'];
        $this->post['subject']  = $post['subject']  ?: self::$post_default['subject'];
        $this->post['body']     = $post['body']     ?: self::$post_default['body'];
        $this->post['url']      = $post['url']      ?: self::$post_default['url'];
        $this->post['host']     = $post['host']     ?: self::$post_default['host'];
        $this->post['delpass']  = $post['delpass']  ?: self::$post_default['delpass'];
        $this->post['unixtime'] = $post['unixtime'] ?: self::$post_default['unixtime'];
    }

    public function __set($key, $val)
    {
        if (array_key_exists($key, self::$post_default)) {
            $this->post[$key] = $val;
        }
    }

    public function __get($key)
    {
        return $this->post[$key];
    }

    public static function buildPost($data)
    {
        if (!is_string($data)) {
            return null;
        }

        list($no, $date, $name, $email, $sub, $body, $url, $host, $password, $unixtime) = explode("<>", $data);

        return new Post([
                    'no' => $no,
                    'date' => $date,
                    'name' => $name,
                    'email' => $email,
                    'subject' => $sub,
                    'body' => $body,
                    'url' => $url,
                    'host' => $host,
                    'delpass' => $password,
                    'unixtime' => $unixtime
                ]);
    }

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
