<?php require_once('connect.php'); ?>
<?
// 判斷是否登入，如果登入狀態則顯示使用者的 nickname
$user_nickname = '未登入';
$user_id = 'NaN';
if(isset($_COOKIE['session_id'])){
// $session_id 不繪輸出在畫面上所以不用再 htmlspecialchars
$session_id = $_COOKIE['session_id'];
$stmt = $conn->prepare("SELECT `user_id` FROM `tingkao_session_id` WHERE `session_id`=?");
$stmt->bind_param("s", $session_id);
$stmt->execute();
$result = $stmt->get_result();
    if($result->num_rows>0){
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $sql = "SELECT * FROM `tingkao_user` WHERE `user_id`='$user_id'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $user_nickname = $row['user_nickname'];
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- bootstrap.css 裡面就包含了 normalize.css ,BS4 是 reboot.scss   -->
    <!-- <link rel="stylesheet" href="css/normalize.css">  -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <sript src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="index.php">留言板</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link login" href="login.php">login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link signup" href="signup.html">signup</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link logout display-none" href="logout.php">logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="wrap">
        <div class="main_comment">
            <h1 class="title">我要留言</h1>
            <p class="commenter"><?php echo $user_nickname; ?></p>
            <form action="handle_comment.php" method="post">
                <textarea class="comment_text" name="comment_text" id="" cols="30" rows="6" placeholder="comment..."></textarea>
                <input class="comment_btn btn btn-outline-primary" type="submit" value="留言">
            </form>
        </div>
<?
// 頁數的判斷
$sql = "SELECT COUNT(*) as sum FROM `tingkao_main` WHERE deleted=0";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_comment = $row['sum'];
$comment_num = $total_comment + 1;
if(is_int($row['sum']/10)){
    $pages = $row['sum']/10;
}else{
    $pages = ceil($row['sum']/10);
};
//取出資料數量/10，來判斷總共有幾頁

$page = 1;//現在在第幾頁，一開始進來的時候在第一頁(預設)．後面隨著點選頁數，$page 會改變數值（JS）
if(isset($_GET['page'])){
    $page = $_GET['page'];
};
if($page === 1){
    $sql = "SELECT * FROM tingkao_main WHERE deleted=0 ORDER BY created_at DESC LIMIT 10"; 
    // DESC 顛倒順序，讓新留言在最上面，LIMIT 10，只取十筆資料在一頁
}else{
    $number = 10*($page - 1);
    $sql = "SELECT * FROM tingkao_main WHERE deleted=0 ORDER BY created_at DESC LIMIT $number, 10"; 
    $comment_num = $total_comment + 1 - $number;
    // DESC 顛倒順序，讓新留言在最上面，LIMIT 參數一, 參數二，參數一：略過前面幾個，參數二：取幾個。
}
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $comment_num -= 1;
        $id = $row['id'];
        $main_commenter_user_id = $row['user_id'];
        $main_commenter = htmlspecialchars($row['main_commenter'], ENT_QUOTES, 'UTF-8');
        $main_comment = htmlspecialchars($row['main_comment'], ENT_QUOTES, 'UTF-8');
        $created_at = $row['created_at'];
        // 以上為撈出的資料取變數，增加程式碼閱讀性

        $sum_sql = "SELECT COUNT(*) as sum FROM `tingkao_sub` WHERE parent_id = '$id' AND `deleted`=0"; // 撈出每個主留言 id 下的子留言數量
        $sum_result = $conn->query($sum_sql);
        $sum_row = $sum_result->fetch_assoc();
        $sum = $sum_row['sum'];  //$sum 為每個主留言下的子留言數量
?>
        <section class="content">
            <div class="main_message">
                <div class="message_num"><? echo '#0'.$comment_num ; ?></div>
                <div class="message_pic"></div>
            <?
            if($main_commenter_user_id === $user_id){ //登入者的留言才會出現 message_edit_option
            ?>
                <div class="message_edit">
                    <p class="message_edit_btn">...</p>
                    <form class="message_edit_option display-none" action="handle_edit_comment.php" method="post">
                        <input type="hidden" name="message_edit" value="<? echo $id ;?>">
                        <input class="message_edit_comment btn-outline-primary" type="submit" value="編輯留言">
                        <hr>
                        <input type="hidden" name="message_delete" value="<? echo $id ;?>">
                        <!-- 提問：這邊如果從 clien 端更改 $id，就可以刪除別的留言，避免刪除別人的留言，目前解決方式：刪除留言的 SQL 執行前，比對該留言跟刪除者是否同一人 -->
                        <!-- 或是有什麼其他方式可以帶 id 到後端？? -->
                        <input class="message_delete_comment btn-outline-danger" type="submit" value="刪除留言">
                    </form>
                </div>
            <?
            } //登入者的留言才會出現 message_edit_option
            ?>
                <div class="message_info">
                    <div class="message_commenter"><? echo $main_commenter; ?></div>
                    <div class="message_created_at"><? echo $created_at; ?></div>
                </div>
                <div class="message_text"><? echo $main_comment; ?></div>
                <div class="message_sum"><? echo $sum; ?> 則回應</div>
                <hr>
                <div class="message_btn ">我要留言 ►</div>
            </div>
            <div class="sub_content display-none">
<?
$sub_sql = "SELECT * FROM `tingkao_sub` WHERE `deleted`=0";
$sub_result = $conn->query($sub_sql);
if ($sub_result->num_rows > 0) {
    while($sub_row = $sub_result->fetch_assoc()) {
        $ind = $sub_row['ind'];
        $parent_id = $sub_row['parent_id'];
        $sub_commenter_user_id = $sub_row['user_id'];
        $sub_commenter = htmlspecialchars($sub_row['sub_commenter'], ENT_QUOTES, 'UTF-8');
        $sub_comment = htmlspecialchars($sub_row['sub_comment'], ENT_QUOTES, 'UTF-8');
        $sub_created_at = $sub_row['created_at'];
        if($parent_id === $id && $main_commenter_user_id === $user_id){ //父留言是登入者的留言
?>
                <div class="sub_message own_color"> <!-- 自己的留言加了 own_color -->
                    <div class="message_pic"></div>

                    <div class="message_edit"> <!-- 自己的子留言可以編輯與刪除 -->
                        <p class="message_edit_btn">...</p>
                        <form class="message_edit_option display-none" action="handle_edit_comment.php" method="post">
                            <input type="hidden" name="sub_message_edit" value="<? echo $ind ;?>">
                            <input class="message_edit_comment btn-outline-primary" type="submit" value="編輯子留言">
                            <hr>
                            <input type="hidden" name="sub_message_delete" value="<? echo $ind ;?>">
                            <input class="message_delete_comment btn-outline-danger" type="submit" value="刪除子留言">
                        </form>
                    </div> <!-- 自己的子留言可以編輯與刪除 -->

                    <div class="message_info">
                        <div class="message_commenter"><? echo $sub_commenter; ?></div>
                        <div class="message_created_at"><? echo $sub_created_at; ?></div>
                    </div>
                    <div class="message_text"><? echo $sub_comment; ?></div>
                </div>
<?  
        }else if($parent_id === $id){ //父留言不是登入者的留言
?>
                <div class="sub_message">
                    <div class="message_pic"></div>
                <?
                if($sub_commenter_user_id === $user_id){ //如果子留言是自己的留言，可以編輯與刪除
                ?>
                    <div class="message_edit"> 
                        <p class="message_edit_btn">...</p>
                        <form class="message_edit_option display-none" action="handle_edit_comment.php" method="post">
                            <input type="hidden" name="sub_message_edit" value="<? echo $ind ;?>">
                            <input class="message_edit_comment btn-outline-primary" type="submit" value="編輯子留言">
                            <hr>
                            <input type="hidden" name="sub_message_delete btn-outline-primary" value="<? echo $ind ;?>">
                            <input class="message_delete_comment btn-outline-danger" type="submit" value="刪除子留言">
                        </form>
                    </div>
                <?
                } //如果子留言是自己的留言，可以編輯與刪除
                ?>
                    <div class="message_info">
                        <div class="message_commenter"><? echo $sub_commenter; ?></div>
                        <div class="message_created_at"><? echo $sub_created_at; ?></div>
                    </div>
                    <div class="message_text"><? echo $sub_comment; ?></div>
                </div>
<?
        }
    };
}
?>
<!-- 最下方的留言區 -->
                    <div class="sub_comment">
                        <p class="commenter"><? echo $user_nickname; ?></p>
                        <form action="handle_comment.php" method="post">
                            <textarea class="comment_text" name="sub_comment_text" id="" cols="30" rows="4" placeholder="comment..."></textarea>
                            <input name="parent_id" type="hidden" value=<? echo $id; ?>>
                            <input class="comment_btn sub_comment_btn btn btn-outline-primary" type="submit" value="留言">
                        </form>
                    </div>
            </div>
        </section>
<?
    
    }
}
?>
    </div>

    <footer class="pages">
        <form class="page-form" action="" method="get">
            <!-- <input class="page-btn" type="submit" name="page" value="1">
            <input class="page-btn" type="submit" name="page" value="2">
            <input class="page-btn" type="submit" name="page" value="3"> -->
        </form>
    </footer>
    
</body>
<?
$isLogin = 0;
//這邊用 0 不用 false 是因為下面要執行 let isLogin = <? echo $isLogin ?/> 如果用 false 的話會變成 'false' 字串，丟到 if 裡面仍然是 true
// 如果擁有 session_id 表示有登入，就可以留言
if(isset($_COOKIE['session_id'])){
    $stmt = $conn->prepare("SELECT `session_id` FROM `tingkao_session_id` WHERE `session_id`=?");
    $stmt->bind_param("s", $_COOKIE['session_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows>0){
        $isLogin = true;
    }
}
?>
<script>
    let isLogin = <? echo $isLogin; ?>;
    if(isLogin){
        document.querySelector('.login').classList.add('display-none')
        document.querySelector('.signup').classList.add('display-none')
        document.querySelector('.logout').classList.remove('display-none')
    }

    document.querySelectorAll('.wrap').forEach(function(element){
        element.addEventListener('click', function(e){
            if(e.target.classList.contains('message_btn')=== true){
                let sub_content = e.target.parentNode.parentNode.querySelector('.sub_content')
                // 操控子留言區的顯示
                sub_content.classList.toggle('display-none')
                if(e.target.innerText === '我要留言 ►'){
                    e.target.innerText = '關閉留言 ▼'
                }else{
                    e.target.innerText = '我要留言 ►'
                }
            }

        })
    })
    document.querySelectorAll('.comment_text').forEach(function(element){
        element.addEventListener('click', function(e){
            if(!isLogin){
                e.preventDefault
                alert('請先登入會員！')
            }
        })
    })
    let pages = <?php echo $pages ;?>; //有幾頁 
    // 網頁底部的頁碼
    let fragment = document.createDocumentFragment();
    function createInput(){
        let newInput = null
        for(i=1; i<=pages; i++){
            newInput = document.createElement('input');
            newInput.className = "page-btn btn btn-light";
            newInput.setAttribute('value', i );
            newInput.setAttribute('name', 'page');
            newInput.setAttribute('type', 'submit');
            fragment.appendChild(newInput);
            document.querySelector('.page-form').appendChild(fragment); 
        }
    }
    createInput()

// 按了．．．之後 編輯和刪除留言區顯示與不顯示
    document.querySelectorAll('.wrap').forEach(function(element){
        element.addEventListener('click', function(e){
            if(e.target.classList.contains('message_edit_btn')){
                e.target.parentNode.querySelector('.message_edit_option').classList.toggle('display-none')
            }
        })
    })
    document.querySelectorAll('.wrap').forEach(function(element){
        element.addEventListener('click', function(e){
            if(e.target.classList.contains('message_delete_comment')){
                alert('刪除留言')
                e.preventDefault();
                // 按了 刪除留言的按鈕後，編輯留言按鈕的 value 為空值(不帶值到後端))
                // 以下判斷為父層留言還是子留言(因為設定的屬性名稱不同所以要判斷))
                if(e.target.parentNode.querySelector('input[name="message_edit"]')){
                    e.target.parentNode.querySelector('input[name="message_edit"]').setAttribute("value", "");
                }else if(e.target.parentNode.querySelector('input[name="sub_message_edit"]')){
                    e.target.parentNode.querySelector('input[name="sub_message_edit"]').setAttribute("value", "");
                }
                    let request = new XMLHttpRequest();
                    request.open('POST', 'handle_edit_comment.php', true);
                    request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                    request.onload = function(){
                        if(request.status >= 200 && request.status < 400){
                            if(main_message_delete){
                                e.target.parentNode.parentNode.parentNode.parentNode.remove()
                            }else if(sub_message_delete){
                                let sum_node = e.target.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector('.message_sum')
                                sum_node.innerText = request.responseText + " 則回應"
                                e.target.parentNode.parentNode.parentNode.remove()
                            }
                        }
                    }
                    let sub_message_delete = e.target.parentNode.querySelector('input[name="sub_message_delete"]')
                    let main_message_delete = e.target.parentNode.querySelector('input[name="message_delete"]')
                    let message_delete_value = (main_message_delete) ? main_message_delete.value : sub_message_delete.value
                    let message_delete_key = (main_message_delete) ? 'message_delete' : 'sub_message_delete'
                    let parent_id = e.target.parentNode.parentNode.parentNode.parentNode.querySelector('input[name="parent_id"]').value
                    // 帶要刪除的資料到後端
                    request.send(message_delete_key + '=' + message_delete_value +'&sub_message_parent_id=' + parent_id);
                
            }
        })
    })
    //  編輯留言的部分沒有做 ajax
    document.querySelectorAll('.wrap').forEach(function(element){
        element.addEventListener('click', function(e){
            if(e.target.classList.contains('message_edit_comment')){
                alert('編輯留言')
                if(e.target.parentNode.querySelector('input[name="message_delete"]')){
                    e.target.parentNode.querySelector('input[name="message_delete"]').setAttribute("value", "");
                }else{
                    e.target.parentNode.querySelector('input[name="sub_message_delete"]').setAttribute("value", "");
                }
            }
        })
    })

    //  主留言的部分做 ajax
    document.querySelector('.comment_btn').addEventListener('click',function(e){
        e.preventDefault();
        if(document.querySelector('.comment_text').value===''){
            alert('請輸入文字!')
            return false;
        }
        let request = new XMLHttpRequest();
        request.open('POST', 'handle_comment.php', true);
        request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                // Success!
                let responseObj = JSON.parse(request.responseText)
                let parent_id = responseObj.data.id
                let user_id = responseObj.data.user_id
                let main_commenter = responseObj.data.main_commenter
                let main_comment = responseObj.data.main_comment
                let created_at = responseObj.data.created_at
                let sum_comment = responseObj.sum
                document.querySelector('.main_comment').insertAdjacentHTML('afterend', `
                    <section class="content">
                    <div class="main_message">
                        <div class="message_num">#0${sum_comment}</div>
                        <div class="message_pic"></div>

                        <div class="message_edit">
                            <p class="message_edit_btn">...</p>
                            <form class="message_edit_option display-none" action="handle_edit_comment.php" method="post">
                                <input type="hidden" name="message_edit" value="${parent_id}"> 

                                <input class="message_edit_comment btn-outline-primary" type="submit" value="編輯留言">
                                <hr>
                                <input type="hidden" name="message_delete" value="${parent_id}">
                                <input class="message_delete_comment btn-outline-danger" type="submit" value="刪除留言">
                            </form>
                        </div>

                        <div class="message_info">
                            <div class="message_commenter">${main_commenter}</div>
                            <div class="message_created_at">${created_at}</div>
                        </div>
                        <div class="message_text">${main_comment}</div>
                        <div class="message_sum">0 則回應</div>
                        <hr>
                        <div class="message_btn">我要留言 ►</div>
                    </div>

                    <div class="sub_content display-none">

                        <div class="sub_comment">
                            <p class="commenter">${main_commenter}</p>
                            <form action="handle_comment.php" method="post">
                                <textarea class="comment_text" name="sub_comment_text" id="" cols="30" rows="4" placeholder="comment..."></textarea>
                                <input name="parent_id" type="hidden" value='${parent_id}'>
                                <input class="comment_btn sub_comment_btn btn btn-outline-primary" type="submit" value="留言">
                            </form>
                        </div>

                    </div>

    
                    </section>
                
                `);
                document.querySelector('.comment_text').value = "";
                

            }
        };
        let comment_text = document.querySelector('.comment_text').value
        let user_name = document.querySelector('p.commenter').innerText
        request.send('comment_text=' + comment_text );
    })

    //  子留言的部分做 ajax
    document.querySelector('.wrap').addEventListener('click',function(e){
        if(e.target.classList.contains('sub_comment_btn') === true){
            e.preventDefault();
            let request = new XMLHttpRequest();
            request.open('POST', 'handle_comment.php', true);
            request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            request.onload = function() {
                if (request.status >= 200 && request.status < 400) {
                    // Success!
                    let response_sub = JSON.parse(request.responseText)
                    let sub_comment = response_sub.sub_data.sub_comment
                    let sub_commenter = response_sub.sub_data.sub_commenter
                    let parent_id = response_sub.sub_data.parent_id
                    let ind = response_sub.sub_data.ind
                    let created_at = response_sub.sub_data.created_at
                    let sub_sum = response_sub.sub_sum
                    let message_commenter_text = e.target.parentNode.parentNode.parentNode.parentNode.querySelector('.message_commenter').innerText
                    let message_sum = e.target.parentNode.parentNode.parentNode.parentNode.querySelector('.message_sum')
                    let own_color_class = (message_commenter_text === document.querySelector('p.commenter').innerText) ? 'own_color' : '' ;
                    e.target.parentNode.parentNode.insertAdjacentHTML('beforebegin',`
                        <div class="sub_message ${own_color_class}">
                            <div class="message_pic"></div>

                            <div class="message_edit">
                                <p class="message_edit_btn">...</p>
                                <form class="message_edit_option display-none" action="handle_edit_comment.php" method="post">
                                    <input type="hidden" name="sub_message_edit" value="${ind}">
                                    <input class="message_edit_comment btn-outline-primary" type="submit" value="編輯子留言">
                                    <hr>
                                    <input type="hidden" name="sub_message_parent_id" value="${parent_id}">
                                    <input type="hidden" name="sub_message_delete" value="${ind}">
                                    <input class="message_delete_comment btn-outline-danger" type="submit" value="刪除子留言">
                                </form>
                            </div>
                            <div class="message_info">
                                <div class="message_commenter">${sub_commenter}</div>
                                <div class="message_created_at">${created_at}</div>
                            </div>
                            <div class="message_text">${sub_comment}</div>
                        </div>
                    `);
                    e.target.parentNode.querySelector('.comment_text').value = "";
                    message_sum.innerText = sub_sum + " 則回應"
                }
            };
            let comment_text = e.target.parentNode.querySelector('.comment_text').value
            let user_name = document.querySelector('p.commenter').innerText
            let parent_id = e.target.parentNode.querySelector('input[name="parent_id"]').value
            request.send('sub_comment_text=' + comment_text + '&parent_id=' + parent_id );
        }
    }) 
</script>
</html>