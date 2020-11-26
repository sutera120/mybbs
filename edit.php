<?php
session_start();
require('common.php');
require('dbconnect.php');

// ログインしてなければログインページへ
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}

// 既存メッセージを取得
if (isset($_SESSION['id']) && isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];

    // データベースに接続
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if ($mysqli->connect_errno) {
        $error_message[] = '書き込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
    } else {
        // 文字コード設定
        $mysqli->set_charset('utf8');

        $id = $mysqli->real_escape_string($id);

        // データを登録するSQL作成
        $sql = "SELECT * FROM posts WHERE id=$id";

        // データを登録
        $res = $mysqli->query($sql);

        $table = $res->fetch_assoc();

        // データベースの接続を閉じる
        $mysqli->close();
    }
}else{
    header('Location: index.php');
    exit();
}

// メッセージを編集する
if (!empty($_POST)) {
    if ($_POST['message'] != '') {
        $id = $_REQUEST['id'];
        // データベースに接続
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // 接続エラーの確認
        if ($mysqli->connect_errno) {
            $error_message[] = '書き込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
        } else {
            // 文字コード設定
            $mysqli->set_charset('utf8');

            $id = $mysqli->real_escape_string($id);

            // データを登録するSQL作成
            $sql = "SELECT * FROM posts WHERE id=$id";

            // データを登録
            $res = $mysqli->query($sql);
            $table = $res->fetch_assoc();

            if ($table['member_id'] == $_SESSION['id']) {
                $edit_message = $mysqli->real_escape_string($_POST['message']);
                $edit_message = sanitize($edit_message);
                // 編集
                $sql = "UPDATE posts SET message='$edit_message' WHERE id=$id";
                $res = $mysqli->query($sql);
            }
            // データベースの接続を閉じる
            $mysqli->close();

            header('Location: index.php');
            exit();
        }
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
        <h2>メッセージ一覧</h2>
        <div id="content">
            <form action="" method="post">
                <dl>
                    <dt><?php echo sanitize($_SESSION['name']); ?>さん、メッセージを修正してください</dt>
                    <dd>
                        <textarea name="message" cols="50" rows="5"><?php echo sanitize($table['message']); ?></textarea>
                    </dd>
                </dl>
                <div>
                    <p>
                        <input type="submit" value="メッセージを編集する" />
                    </p>
                    <p><a href="index.php">コメント一覧に戻る</a></p>
                </div>
            </form>
        </div>
    </main>
</body>

</html>