<?php

/**
* 過去ログ
*/
class Pastlog
{
    private static $past_dir;

    function __construct()
    {
        self::$past_dir = Config::get('past_dir');
    }

    public static function readPastIndexLog($filepath)
    {
        $fc = @fopen($filepath, "r");
        if ($fc === false) {
            throw new Exception($filepath . "が開けません");
        }
        $count = fgets($fc, 10);
        fclose($fc);

        return $count;
    }

    public static function writePastIndexLog($filepath, $count)
    {
        $fp = fopen($filepath, "w");
        flock($fp, LOCK_EX);
        fputs($fp, $count);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public static function buildPastnoFilePath($count, $past_dir)
    {
        if (!is_numeric($count)) {
            return '';
        }
        $past_dir = $past_dir ?: self::$past_dir;
        return $past_dir . "index" . $count . ".html";
    }

    public static function buildPastLogHtml($data = '', $autolink = false)
    {
        if (empty($data)) {
            return '';
        }

        // 投稿データをパース
        $post = Post::buildPost($data);

        // URLをHTMLリンク形式に変換
        if ($post->url) {
            $post->url = "<a href=\"http://{$post->url}\" target=\"_blank\">HP</a>";
        }
        // メールアドレスをHTMLリンク形式に変換
        if ($post->email) {
            $post->name = "<a href=\"mailto:{$post->email}\">$post->name</a>";
        }
        // 返信（＞）がある時は色変更
        $post->body = preg_replace("/(&gt;)([^<]*)/i", "<span style=\"color: #999;\">\\1\\2</span>", $post->body);
        // URL自動リンク
        if ($autolink) {
            $post->body = ViewModel::autoLink($post->body);
        }

        // 追加で書き込むHTMLの作成
        $dat = "<hr>[{$post->no}] <span style=\"color: #009900; font-weight: bold;\">{$post->subject}</span> Name：<span style=\"font-weight: bold;\">{$post->name}</span> <span style=\"font-size: smaller;\">Date：{$post->date}</span> {$post->url}<br><div style=\"margin: 1em 0; padding: 0 0 0 40px;\">{$post->body}</div><!-- {$post->host} -->";

        return $dat;
    }


    public static function writePastLog($filepath, $dat, $past)
    {
        $np = fopen($filepath, "w");
        flock($np, LOCK_EX);
        fputs($np, $dat);
        if ($past) {
            foreach ($past as $val) {
                fputs($np, $val);
            }
        }
        fflush($np);
        flock($np, LOCK_UN);
        fclose($np);
    }
}