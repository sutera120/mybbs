<?php
session_start();
require('dbconnect.php');


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
        
        if ($table['member_id'] == $_SESSION['id']) {
            // 削除
            $sql = "DELETE FROM posts WHERE id=$id";
            $res = $mysqli->query($sql);
        }
        // データベースの接続を閉じる
        $mysqli->close();
    }
}

header('Location: index.php');
exit();
