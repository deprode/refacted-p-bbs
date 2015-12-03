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
</style>
</head>
<body <?php echo $c->body; ?>>
    [<a href="<?php echo $script_name; ?>">掲示板に戻る</a>]
    <table width='100%'>
        <tr>
            <th bgcolor="#508000">
                <font color="#FFFFFF">管理モード</font>
            </th>
        </tr>
    </table>
    <div class="center">
        <h4>パスワードを入力して下さい</h4>
        <form action="<?php echo $script_name; ?>" method="POST">
            <input type="hidden" name="mode" value="admin">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="password" name="apass" size="8">
            <input type="submit" value=" 認証 ">
        </form>
    </div>
</body>
</html>