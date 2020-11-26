<?php
session_start();

// セッション情報を削除
$_SESSION = array();
session_destroy();

// Cookie情報も削除
setcookie('email', '', time() - 420000);
setcookie('password', '', time() - 420000);

header('Location: login.php');
exit();
