<?php
session_start();
require('common.php');
require('dbconnect.php');

// 最終ログインからの経過時間
$elapsed_time =24 * 60 * 60; //一日

if (isset($_SESSION['id']) && $_SESSION['time'] + $elapsed_time > time()) {
    // ログインしている

    $_SESSION['time'] = time();

    // データベースに接続
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if ($mysqli->connect_errno) {
        $error_message[] = '書き込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
    } else {
        $id = $mysqli->real_escape_string($_SESSION['id']);

        // SQL作成
        $sql = "SELECT * FROM members WHERE id=$id";

        // データを登録
        $res = $mysqli->query($sql);

        $member = $res->fetch_assoc();

        // データベースの接続を閉じる
        $mysqli->close();
    }
} else {
    // 指定時間経過、またはログインしていない場合はセッションを破棄
    session_destroy();
    header('Location: login.php');
    exit();
}

// 投稿を記録する
if (!empty($_POST)) {
    if ($_POST['message'] != '') {
        // データベースに接続
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // 接続エラーの確認
        if ($mysqli->connect_errno) {
            $error_message[] = '書き込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
        } else {
            // 文字コード設定
            $mysqli->set_charset('utf8');

            // 書き込み日時を取得
            $id = $mysqli->real_escape_string($member['id']);
            $message = $mysqli->real_escape_string($_POST['message']);
            $message = sanitize($message);
            $reply_post_id = $_POST['reply_post_id'] == '' ? 0 : $mysqli->real_escape_string($_POST['reply_post_id']);
            $now_date = date("Y-m-d H:i:s");

            // データを登録するSQL作成
            $sql = "INSERT INTO posts (member_id, message, reply_post_id ,created) VALUES ( '$id','$message','$reply_post_id','$now_date')";
            // データを登録
            $res = $mysqli->query($sql);

            // データベースの接続を閉じる
            $mysqli->close();
            header('Location: index.php');
            exit();
        }
    }
}

// 投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
    $page = 1;
}
$page = max($page, 1);

$num = 5;  //表示する件数

// データベースに接続
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// 接続エラーの確認
if ($mysqli->connect_errno) {
    $error_message[] = 'データの読み込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
} else {
    // 最終ページを取得する


    $sql = 'SELECT COUNT(*) AS cnt FROM posts';
    $recordSet = $mysqli->query($sql);
    $table = $recordSet->fetch_assoc();
    $maxPage = ceil($table['cnt'] / $num);
    $page = min($page, $maxPage);
    $start = ($page - 1) * $num;
    $start = max(0, $start);


    $sql = "SELECT m.name, p.* FROM members m,posts p WHERE m.id = p.member_id ORDER BY p.id DESC LIMIT $start, $num";
    $res = $mysqli->query($sql);

    $res = $mysqli->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $posts[] = $row;
        }
        // $posts = $res->fetch_all(MYSQLI_ASSOC);
    }
    $mysqli->close();
}

// 返信の場合
if (isset($_REQUEST['res'])) {
    // データベースに接続
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 接続エラーの確認
    if ($mysqli->connect_errno) {
        $error_message[] = 'データの読み込みに失敗しました。 エラー番号 ' . $mysqli->connect_errno . ' : ' . $mysqli->connect_error;
    } else {
        $reply = $mysqli->real_escape_string($_REQUEST['res']);

        $sql = "SELECT m.name, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=$reply ORDER BY p.created DESC";
        $res = $mysqli->query($sql);

        if ($res) {
            $table = $res->fetch_assoc();
            $message = '@' . $table['name'] . ' ' . ($table['message']);
        }
        $mysqli->close();
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
            <?php foreach ($posts as $post) : ?>
                <article>
                    <p>
                        <?php
                        echo sanitize($post['name']); ?>
                        /
                        <time><?php echo date('Y年m月d日 H:i', strtotime($post['created'])); ?></time>
                        <?php if ($_SESSION['id'] == $post['member_id']) : ?>
                            <a id="delete" href="delete.php?id=<?php echo sanitize($post['id']); ?>" onclick="return confirm('削除しますか？')">削除</a>
                            <a id="edit" href="edit.php?id=<?php echo sanitize($post['id']); ?>" onclick="return confirm('編集しますか？')">編集</a>
                        <?php endif; ?>
                    </p>
                    <div class="info">
                        <p><?php
                            $post['message'] = sanitize($post['message']);
                            $post['message'] = nl2br($post['message']);
                            $post['message'] = makeLink($post['message']);
                            echo $post['message']; ?>
                        </p>
                        <p class="reply">
                            <a href="index.php?res=<?php echo sanitize($post['id']); ?>&page=<?php echo sanitize($_REQUEST['page']); ?>">✏︎コメントする</a>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>

            <ul class="paging">
                <?php
                if ($page > 1) {
                ?>
                    <li><a href="index.php?page=<?php echo ($page - 1); ?>">&lArr;前のページへ</a></li>
                <?php
                } else {
                ?>
                    <li>&lArr;前のページへ</li>
                <?php
                }
                ?>
                <?php
                if ($page < $maxPage) {
                ?>
                    <li><a href="index.php?page=<?php echo ($page + 1); ?>">次のページへ&rArr;</a></li>
                <?php
                } else {
                ?>
                    <li>次のページへ&rArr;</li>
                <?php
                }
                ?>
            </ul>
            <hr>

            <form action="" method="post">
                <dl>
                    <dt><?php echo sanitize($_SESSION['name']); ?>さん、メッセージをどうぞ</dt>
                    <dd>
                        <textarea name="message" cols="50" rows="5"><?php echo sanitize($message); ?></textarea>
                        <input type="hidden" name="reply_post_id" value="<?php echo sanitize($_REQUEST['res']); ?>" />
                    </dd>
                </dl>
                <div>
                    <p>
                        <input type="submit" value="投稿する" />
                    </p>
                </div>
            </form>
        </div>
    </main>
</body>

</html>