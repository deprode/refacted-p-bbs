<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $c->title1; ?></title>
    <style type="text/css">
.title {
    font-size: x-large;
    font-weight: bold;
    font-family: "Verdana";
    color: gray;
}
.caution {
    font-size: x-small;
}
.center {
    margin: 0 auto;
    text-align: center;
}
.right {
    float: right;
    text-align: right;
}

.cf:before,
.cf:after {
    content: " ";
    display: table;
}
.cf:after {
    clear: both;
}
.cf {
    *zoom: 1;
}

.post-title {
    font-size: large;
    color: rgb(208, 17, 102);
}
.post-name {
    font-weight: bold;
    color: rgb(0, 112, 0);
}
.post-date {
    font-size: small;
}
.post-response {
    color: <?php echo $c->re_color ?>;
}
</style>
</head>
<body <?php echo $c->body; ?>>
    <form action="<?php echo $script_name; ?>" method="post" accept-charset="utf-8">
        <input type="hidden" name="mode" value="regist">

        <h1 class="title"><?php echo $c->title2; ?></h1>
        <hr size="1">
        <br>
        <tt>
            お名前 <input type=text name="name" size="20" value="<?php echo $r_name; ?>" maxlength=24><br>
            メール <input type=text name="email" size="30" value="<?php echo $r_mail; ?>"><br>
            題名　 <input type=text name="sub" size="30" value="<?php echo $r_sub; ?>">
            <input type=submit value="     投稿     ">
            <input type=reset value="消す"><br>
            <textarea name="com" rows="5" cols="82"><?php echo $r_com; ?></textarea><br><br>

            ＵＲＬ　 <input type=text name="url" size="70" value="http://"><br>
            削除キー <input type=password name="password" size="8" value="<?php echo $r_pass; ?>">(記事の削除用。英数字で8文字以内)
        </tt>
    </form>
    <hr size="1">
    <div class="caution">
        新しい記事から表示します。最高<?php echo $c->max; ?>件の記事が記録され、それを超えると古い記事から過去ログへ移ります。<br>
         １回の表示で<?php echo $c->page_def; ?>件を越える場合は、下のボタンを押すことで次の画面の記事を表示します。
    </div>

    <?php for ($i=0; $i < count($dat); $i++): ?>
        <hr size="1">
        [<a href="<?php echo $script_name; ?>?mode=resmsg&no=<?php echo $dat[$i]['no'] ?>"><?php echo $dat[$i]['no'] ?></a>]
        <span class="post-title"><b><?php echo $dat[$i]['sub'] ?></b></span><br>
        　Name：<span class="post-name"><b>
        <?php if (isset($dat[$i]['email']) && !empty($dat[$i]['email'])): ?>
            <a href="mailto:<?php echo $dat[$i]['email'] ?>"><?php echo $dat[$i]['name'] ?></a>
        <?php else: ?>
            <?php echo $dat[$i]['name'] ?>
        <?php endif; ?>
        </b></span>

        <span class="post-date">　Date： <?php echo $dat[$i]['now'] ?></span>
        <p>
            <blockquote>
                <tt>
                <?php echo preg_replace("/(^|>)(&gt;[^<]*)/i", "\\1<span class=\"post-response\">\\2</span>", $dat[$i]['com']); ?>
                </tt>
                <p>
                    <?php if (isset($dat[$i]['url']) && !empty($dat[$i]['url'])): ?>
                        <a href="http://<?php echo $dat[$i]['url']; ?>" target="_blank">
                            http://<?php echo $dat[$i]['url']; ?>
                        </a>
                    <?php endif; ?><br>
                    <?php echo $dat[$i]['host']; ?>
                </p>
            </blockquote>
        </p>
    <?php endfor; ?>
    <hr size="1">
    <?php echo $start; ?> 番目から <?php echo ($start + count($dat) - 1); ?> 番目の記事を表示<br>
    <div class="center">
        Page:[<b>
        <?php if(intval($page, 10) > 0): ?>
            <a href="<?php echo $script_name; ?>?page=<?php echo "" . ($page - $c->page_def); ?>">&lt;&lt;</a>
        <?php endif; ?>

        <?php if ($c->page_def): ?>
        <?php for ($i = 0; ($i * $c->page_def) < $total; $i++): ?>
            <?php if (intval($page, 10) === ($i * $c->page_def)): ?>
                <?php echo ($i+1); ?>
            <?php else: ?>
                <a href="<?php echo $script_name; ?>?page=<?php echo ($i * $c->page_def); ?>"><?php echo ($i+1); ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php endif; ?>

        <?php if ($total > ($page + $c->page_def)): ?>
            <a href="<?php echo $script_name; ?>?page=<?php echo ($page + $c->page_def); ?>">&gt;&gt;</a>
        <?php else: ?>

        <?php endif; ?>
        </b> ]
    </div>

    <div class="right cf">
        <form method="POST" action="<?php echo $script_name; ?>">
            <input type="hidden" name="mode" value="usrdel">
            No <input type="text" name="no" size="2">
            pass <input type="password" name="pwd" size="4" maxlength="8">
            <input type="submit" value="Del">
        </form>
        [ <a href=<?php echo $c->home;?>>ホーム</a> ]
        [ <a href=<?php echo $script_name;?>?mode=admin>管理</a> ]
        <?php if($c->past_key): ?>
            [ <a href=<?php echo $script_name; ?>?mode=past>過去ログ</a> ]
        <?php endif; ?>
        <br><br>
        <small><!-- P-BBS v1.232 -->- <a href="http://php.s3.to" target="_top">P-BBS</a> -</small>
    </div>
</body>
</html>
