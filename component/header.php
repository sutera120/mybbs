<header class="flex">
    <h1>MyBBS</h1>
    <div class="flex">
        <?php if (isset($_SESSION['name']) ): ?>
            <p>ようこそ、<?php echo sanitize($_SESSION['name']); ?>さん</p>
            <a href="logout.php">ログアウト</a>
        <?php else : ?>
            <a href="register.php">会員登録</a>
            <a href="login.php">ログイン</a>
        <?php endif ?>
    </div>
</header>