<?php require_once('connect.php'); ?>
<?
if(isset($_POST['user_id']) && isset($_POST['user_password'])){
$user_id = $_POST['user_id'];
$user_password = $_POST['user_password'];
$user_nickname = (!empty($_POST['user_nickname'])) ? $_POST['user_nickname'] : $user_id;

$hash_password = password_hash($user_password, PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT INTO `tingkao_user` (`user_nickname`, `user_id`, `user_password`) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user_nickname, $user_id, $hash_password);
if($stmt->execute()){
    echo '註冊成功';
    echo '<a href="login.php">返回登入頁面</a>';
}else{
    echo '註冊失敗';
    echo '<a href="signup.html  ">返回註冊頁面</a>';
    // header('Location: login.html');
}

}
// $conn->close();
?>
