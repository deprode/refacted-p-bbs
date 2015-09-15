<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>■ 過去ログ <?php echo $pno; ?> ■</title>
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
.small {
    font-size: small;
}
.bold {
    font-weight: bold;
}
</style>
</head>
<body <?php echo $c->body; ?>>
    <div class="small">
        [<a href="<?php echo $script_name; ?>?">掲示板に戻る</a>]
    </div>
    <div class="center">
        ■ 過去ログ <?php echo $pno; ?> ■
        <p>
            new←
            <?php for ($pastkey = $count; $pastkey > 0; $pastkey--): ?>
                <?php if ($pno == $pastkey): ?>
                    [<span class="bold"><?php echo $pastkey; ?></span>]
                <?php else: ?>
                    <a href="<?php echo $script_name; ?>?mode=past&pno=<?php echo $pastkey; ?>">[<?php echo $pastkey; ?>]</a>
                <?php endif; ?>
            <?php endfor; ?>
             →old
        </p>
    </div>
    <?php echo $c->past_line; ?>件ずつ表示
    <?php include $pastfile; ?>
</body>
</html>