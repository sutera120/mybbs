<?php
session_start();
require('dbconnect.php');
require('common.php');
if (!isset($_SESSION['join'])) {
    header('Location: register.php');
    exit();
}

if (!empty($_POST)) {
    // データベースに接続
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if ($mysqli->connect_errno) {
        $error_message[] = '書き込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
    } else {
        // 文字コード設定
        $mysqli->set_charset('utf8');

        // 書き込み日時を取得
        $now_date = date("Y-m-d H:i:s");

        //passの暗号化
        //todo password_hash関数に変更する
        $_SESSION['join']['password'] = sha1($_SESSION['join']['password']);

        //エスケープ
        foreach ($_SESSION['join'] as $key => $value) {
            $clean[$key] = $mysqli->real_escape_string($value);
            $clean[$key]= sanitize($clean[$key]);
        }


        // データを登録するSQL作成
        $sql = "INSERT INTO members (name, email, password,created) VALUES ( '$clean[name]', '$clean[email]', '$clean[password]','$now_date')";

        // データを登録
        $res = $mysqli->query($sql);


        // データベースの接続を閉じる
        $mysqli->close();

        header('Location: thanks.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>MyBBS</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require('component/header.php'); ?>
    <main>
            <h2>入力内容の確認</h2>
        <div id="content">
            <p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
            <form action="" method="post">
                <input type="hidden" name="action" value="submit" />
                <dl>
                    <dt>ユーザー名</dt>
                    <dd>
                        <?php echo sanitize($_SESSION['join']['name']); ?>
                    </dd>
                    <dt>メールアドレス</dt>
                    <dd>
                        <?php echo sanitize($_SESSION['join']['email']); ?>
                    </dd>
                    <dt>パスワード</dt>
                    <dd>
                        【表示されません】
                    </dd>
                </dl>
                <div><a href="register.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する" /></div>
            </form>
        </div>
    </main>
</body>
</html>