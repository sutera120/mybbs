<?php
session_start();
require('dbconnect.php');
require('common.php');


if (!isset($_SESSION['join'])) {
    header('Location: register.php');
    exit();
}

// データベースに接続
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 接続エラーの確認
if ($mysqli->connect_errno) {

    $error_message[] = 'データの読み込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
} else {

    $searech_email = $_SESSION['join']['email'];
    $searech_pass = $_SESSION['join']['password'];
    unset($_SESSION['join']);

    $sql = "SELECT * FROM members WHERE email='$searech_email' AND password='$searech_pass'";
    $res = $mysqli->query($sql);
    $table = $res->fetch_assoc();

    $mysqli->close();
    if (!empty($table)) {
        // ログイン成功
        $_SESSION['id'] = $table['id'];
        $_SESSION['name'] = $table['name'];
        $_SESSION['time'] = time();

        // header('Location: index.php');
        // exit();
    } else {
        $error['login'] = 'failed';
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
        <h2>ご登録いただきありがとうございます</h2>
        <div id="content">
            <p>ユーザー登録が完了しました</p>
            <p><a href="index.php">コメント一覧を見る</a></p>
        </div>
        </div>
    </main>
</body>

</html>