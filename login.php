<?php require_once('connect.php'); ?>
<?
//情境：在未登出的情況下，來到登入頁面
// 如果有登入 $logined = true;
$logined = 0;
if(isset($_COOKIE['session_id'])){
    $stmt = $conn->prepare("SELECT `session_id`, `user_id` FROM `tingkao_session_id` WHERE `session_id`=?");
    $stmt->bind_param("s", $_COOKIE['session_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows>0){
        $row = $result->fetch_assoc();
        $logined = true;
        echo '你好，'.htmlspecialchars($row["user_id"], ENT_QUOTES, 'UTF-8').'，請問要登出嗎？   ';
        echo '<a href="index.php">我要繼續留言</a>      ';
        echo '<a href="logout.php">我要登出</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LogIn</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="wrap">
        <div class="content">
            <form id="login_form" action="handle_login.php" method="post">
                <input type="text" class= "input user_nickname display-none" name="user_nickname" placeholder= "Nickname">
                <input type="text" class= "input user_id" name="user_id" placeholder= "UserId">
                <input type= "password" class= "input user_password" name="user_password" placeholder= "Password">
                <input id= "login_btn" class="input" type="submit" value="登入">
                <div class="change_btn">我要註冊</div>
            </form>
        </div>
    </div>
</body>

<script>
    let logined = <? echo $logined; ?>
    
    document.querySelector('#login_btn').addEventListener('click',function(e){
        if(logined){
            e.preventDefault();
            alert('目前是登入狀態噢！')
        }
    })
    document.querySelector('.change_btn').addEventListener('click',function(e){
        console.log(document.querySelector('.change_btn').innerText)
        if(document.querySelector('.change_btn').innerText === '我要註冊'){
            document.querySelector('.change_btn').innerText = '我要登入'
            document.querySelector('#login_btn').value = '註冊'
            document.querySelector('#login_btn').classList.add('signup_btn')
            document.querySelector('#login_form').setAttribute('action','register.php')
            document.querySelector('.user_id').setAttribute('placeholder','UserId*(必填)')
            document.querySelector('.user_password').setAttribute('placeholder','Password*(必填)')
            document.querySelector('.user_nickname').classList.remove('display-none')
        }else{
            document.querySelector('.change_btn').innerText = '我要註冊'
            document.querySelector('#login_btn').value = '登入'
            document.querySelector('#login_btn').classList.remove('signup_btn')
            document.querySelector('#login_form').setAttribute('action','handle_login.php')
            document.querySelector('.user_id').setAttribute('placeholder','UserId')
            document.querySelector('.user_password').setAttribute('placeholder','Password')
            document.querySelector('.user_nickname').classList.add('display-none')
        }
        
    })

// 判斷兩欄都有填寫資料
    document.querySelector('#login_btn').addEventListener('click',function(e){
        let oname = document.forms['login_form'];
        let name = oname.elements.user_id.value;
        let opassword = document.forms['login_form'];
        let password = opassword.elements.user_password.value;

        if(name&&password){
            // alert('填寫了帳號密')
            // 到資料端比對資料
        }else{
            e.preventDefault();
            alert('請輸入正確的帳號密碼！')
        }
    })

</script>
</html>