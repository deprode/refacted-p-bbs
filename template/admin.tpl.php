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
    <?php if(!isset($apass)): ?>
        <p><center><h4>パスワードを入力して下さい</h4>
        <form action="<?php echo $script_name; ?>" method="POST">
            <input type="hidden" name="mode" value="admin">
            <input type="password" name="apass" size="8">
            <input type="submit" value=" 認証 ">
        </form>
    <?php else: ?>
        <form action="<?php echo $script_name; ?>" method="POST">
        <input type="hidden" name="mode" value="admin">
        <input type="hidden" name="apass" value="<?php echo $apass; ?>">
        <center><P>削除したい記事のチェックボックスにチェックを入れ、削除ボタンを押して下さい。
        <P><table border="0" cellspacing="0">
        <tr bgcolor="bbbbbb">
            <th>削除</th>
            <th>記事No</th>
            <th>投稿日</th>
            <th>題名</th>
            <th>投稿者</th>
            <th>コメント</th>
            <th>ホスト名</th>
        </tr>
        <?php foreach ($delmode as $l => $val): ?>
                <tr bgcolor=<?php echo ($l % 2) ? "F8F8F8" : "DDDDDD"; ?>>
                    <th>
                        <input type="checkbox" name="del[]" value="<?php echo $val['no']; ?>">
                    </th>
                    <th>
                        <?php echo $val['no']; ?>
                    </th>
                    <td>
                        <small><?php echo $val['date']; ?></small>
                    </td>
                    <td>
                        <?php echo $val['sub']; ?>
                    </td>
                    <td>
                        <b><?php echo $val['name']; ?></b>
                    </td>
                    <td>
                        <small><?php echo $val['com']; ?></small>
                    </td>
                    <td>
                        <?php echo $val['host']; ?>
                    </td>
                </tr>
        <?php endforeach; ?>
    <?php endif; ?>
            </table>
            <p>
                <input type="submit" value="削除する">
                <input type="reset" value="リセット">
            </p>
        </form>
    </center>
</body>
</html>