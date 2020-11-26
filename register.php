<?php
session_start();
require('dbconnect.php');
require('common.php');

// ログインしてたら一覧ページへ
if (isset($_SESSION['name'])) {
    header('Location: index.php');
    exit();
}

if (!empty($_POST)) {
    // エラー項目の確認
    if (empty($_POST['name'])) {
        $error['name'] = '* ユーザー名を入力してください';
    }
    if (empty($_POST['email'])) {
        $error['email'] = '* メールアドレスを入力してください';
    }
    if (empty($_POST['password'])) {
        $error['password'] = '* パスワードを入力してください';
    } elseif (strlen($_POST['password']) < 4) {
        $error['password_length'] = '* パスワードは4文字以上で入力してください';
    }

    //重複アカウントのチェック
    if (empty($error)) {
        // データベースに接続
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // 接続エラーの確認
        if ($mysqli->connect_errno) {
            $error_message[] = 'データの読み込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
        } else {
            $search = $mysqli->real_escape_string($_POST['email']);
            $search = sanitize($search);
            $sql = "SELECT * FROM members WHERE email='$search'";
            $res = $mysqli->query($sql);
            $table = $res->fetch_assoc();
            $mysqli->close();
            if (isset($table['email'])) {
                $error['email_already_exists'] = '* 指定されたメールアドレスはすでに登録されています';
            } else {
                $_SESSION['join'] = $_POST;
                header('Location: check.php');
                exit();
            }
        }
    }
}
// 書き直し
if ($_REQUEST['action'] == 'rewrite') {
    $_POST = $_SESSION['join'];
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

    <?php if (!empty($success_message)) : ?>
        <p class="success_message"><?php echo $success_message; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)) : ?>
        <ul class="error_message">
            <?php foreach ($error_message as $value) : ?>
                <li>・<?php echo $value; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php require('component/header.php'); ?>
    <main>
        <h2>会員登録</h2>
        <div id="content">
            <p>次のフォームに必要事項をご記入ください。</p>
            <form action="" method="post">
                <section>
                    <label for="email">ユーザー名<span class="required">必須</span></label>
                    <input type="text" name="name" size="35" maxlength="255" value="<?php echo sanitize($_POST['name']); ?>" />
                    <?php if (!empty($error['name'])) : ?>
                        <div class="error_message"><?php echo $error['name']; ?></div>
                    <?php endif; ?>
                </section>
                <section>
                    <label for="email">メールアドレス<span class="required">必須</span></label>
                    <input type="email" name="email" size="35" maxlength="255" value="<?php echo sanitize($_POST['email']); ?>" />
                    <?php if (!empty($error['email'])) : ?>
                        <div class="error_message"><?php echo $error['email']; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error['email_already_exists'])) : ?>
                        <div class="error_message"><?php echo $error['email_already_exists']; ?></div>
                    <?php endif; ?>
                </section>
                <section>
                    <label for="password">パスワード<span class="required">必須</span></label>
                    <input type="password" name="password" size="10" maxlength="20" value="<?php echo sanitize($_POST['password']); ?>" />
                    <?php if (!empty($error['password'])) : ?>
                        <div class="error_message"><?php echo $error['password']; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error['password_length'])) : ?>
                        <div class="error_message"><?php echo $error['password_length']; ?></div>
                    <?php endif; ?>
                </section>
                <input type="submit" value="入力内容を確認する" />
            </form>
        </div>
    </main>
</body>

</html>