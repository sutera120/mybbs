<?php
session_start();
require('common.php');
require('dbconnect.php');

// ログインしてたら一覧ページへ
if (isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

if ($_COOKIE['email'] != '') {
    $_POST['email'] = $_COOKIE['email'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
}

if (!empty($_POST)) {
    // ログインの処理
    if ($_POST['email'] != '' && $_POST['password'] != '') {
        // データベースに接続
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // 接続エラーの確認
        if ($mysqli->connect_errno) {
            $error_message[] = 'データの読み込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
        } else {
            $searech_email = $mysqli->real_escape_string($_POST['email']);
            $searech_pass = sha1($mysqli->real_escape_string($_POST['password']));
            $sql = "SELECT * FROM members WHERE email='$searech_email' AND password='$searech_pass'";
            $res = $mysqli->query($sql);
            $table = $res->fetch_assoc();
            $mysqli->close();
            if (!empty($table)) {
                // ログイン成功
                $_SESSION['id'] = $table['id'];
                $_SESSION['name'] = $table['name'];
                $_SESSION['time'] = time();

                // ログイン情報を記録する
                if ($_POST['save'] == 'on') {
                    setcookie('email', $_POST['email'], time() + 60 * 60 * 24 * 14);
                    setcookie('password', $_POST['password'], time() + 60 * 60 * 24 * 14);
                }
                header('Location: index.php');
                exit();
            } else {
                $error['login'] = '* ログインに失敗しました。正しくご記入ください。';
            }
        }
    } else {
        $error['login'] = '* メールアドレスとパスワードをご記入ください';
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
        <h2>ログインする</h2>
        <div id="content">
            <div id="lead">
                <p>メールアドレスとパスワードを記入してログインしてください。</p>
                <p>入会手続きがまだの方はこちらからどうぞ。</p>
                <p>&raquo;<a href="register.php">入会手続きをする</a></p>
            </div>
            <form action="" method="post">
                <dl>
                    <label for="email">メールアドレス</label>
                    <dd>
                        <input id="email" type="email" name="email" size="35" maxlength="255" value="<?php echo sanitize($_POST['email']); ?>" />
                    </dd>
                    <label for="password">パスワード</label>
                    <dd>
                        <input id="password" type="password" name="password" size="35" maxlength="255" value="<?php echo sanitize($_POST['password']); ?>" />
                    </dd>
                    <?php if (!empty($error['login'])) : ?>
                        <div class="error_message"><?php echo $error['login']; ?></div>
                    <?php endif; ?>
                    <label for="save">ログイン情報の記録</label>
                    <dd>
                        <input id="save" type="checkbox" name="save" value="on">
                        <p>次回からは自動的にログインする</p>
                    </dd>
                </dl>
                <div>
                    <input type="submit" value="ログインする" />
                </div>
            </form>
        </div>
    </main>
</body>

</html>