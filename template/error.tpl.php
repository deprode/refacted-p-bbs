<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title><?php echo $c->title1; ?></title>
    <style type="text/css">
.content {
    margin: 60px 0;
}
.center {
    margin: 0 auto;
    text-align: center;
}
.message {
    font-size: large;
    font-weight: bold;
    color: red;
}
    </style>
</head>
<body <?php echo $c->body; ?>>
<div class="content">
    <hr size="1">
        <div class="center">
            <p class="message"><?php echo $mes;?></p>
        </div>
    <hr size="1">
</div>
</body>
</html>