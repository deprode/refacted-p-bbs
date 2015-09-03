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
</style>
</head>
<body <?php echo $c->body; ?>>
    <font size=2>[<a href="<?php echo $script_name; ?>?">掲示板に戻る</a>]</font>
    <br>
    <center>■ 過去ログ <?php echo $pno; ?> ■<P>new←
    <?php for ($pastkey = $count; $pastkey > 0; $pastkey--): ?>
        <?php if ($pno == $pastkey): ?>
            [<b><?php echo $pastkey; ?></b>]
        <?php else: ?>
            <a href="<?php echo $script_name; ?>?mode=past&pno=<?php echo $pastkey; ?>">[<?php echo $pastkey; ?>]</a>
        <?php endif; ?>
    <?php endfor; ?>
     →old
    </center>
    <?php echo $c->past_line; ?>件ずつ表示
    <?php include $pastfile; ?>
</body>
</html>