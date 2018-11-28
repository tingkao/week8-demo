<?php require_once('connect.php'); ?>
<?
$stmt = $conn->prepare("DELETE FROM `tingkao_session_id` WHERE `session_id`=?");
$stmt->bind_param("s", $_COOKIE['session_id']);
$stmt->execute();
setcookie('session_id', '', time()-3600*24);
$_COOKIE['session_id'] = '';
echo '登出成功！';
echo '<a href="login.php">反回登入頁面</a>';
?>