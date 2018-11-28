<?php require_once('connect.php'); ?>
<?
// 確認是否登入，如有登入，留言者的名稱改為登入者 nickname
if(isset($_COOKIE['session_id'])){
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
        $user_id = $row['user_id'];
    }
}
?>

<?
//父層留言
if(!empty($_POST['comment_text'])){
    // $main_comment = htmlspecialchars($_POST['comment_text'], ENT_QUOTES, 'UTF-8');
    $main_comment = $_POST['comment_text'];
    $stmt = $conn->prepare("INSERT INTO `tingkao_main` (`user_id`, `main_commenter`, `main_comment`) VALUES (?, ?, ?);");
    $stmt->bind_param("sss", $user_id, $user_nickname, $main_comment);

    if($stmt->execute()){
        // echo '父留言成功';
        // 執行 mySQL 抓取最後一筆留言，然後用 row 抓出來，ajax 回傳更新資料
        $select_stmt = $conn->prepare("SELECT * FROM `tingkao_main` ORDER BY `created_at` DESC LIMIT 1;");
        $select_stmt->execute();
        $select_result = $select_stmt->get_result();

        //新留言增加後，從新抓取留言的總數，ajax 回傳更新後的父層留言數量
        $sum_stmt = $conn->prepare("SELECT * FROM `tingkao_main` WHERE `deleted`=0 ;");
        $sum_stmt->execute();
        $sum_result = $sum_stmt->get_result();
        $sum = $sum_result->num_rows;

        while($row = $select_result->fetch_assoc()){
            // echo json_encode($row, JSON_UNESCAPED_UNICODE);
            echo json_encode(array('sum' => $sum, 'data' => $row), JSON_UNESCAPED_UNICODE);
        };      
        // header('Location: index.php');
    }else{
        // echo '留言失敗';
        // header('Location: login.html');
    }
}

//子層留言
if(!empty($_POST['sub_comment_text'])){
    $parent_id = $_POST['parent_id'];
    $sub_comment = $_POST['sub_comment_text'];
    $stmt = $conn->prepare("INSERT INTO `tingkao_sub` (`parent_id`, `user_id`, `sub_commenter`, `sub_comment`) VALUES (?, ?, ?, ?);");
    $stmt->bind_param("ssss", $parent_id, $user_id, $user_nickname, $sub_comment);
if($row = $stmt->execute()){
    //依時間抓取最新的留言，ajax 回傳新增的留言
    $sub_stmt = $conn->prepare("SELECT * FROM `tingkao_sub` ORDER BY `created_at` DESC LIMIT 1;");
    $sub_stmt->execute();
    $sub_result = $sub_stmt->get_result();

    //新留言增加後，從新抓取子留言的總數，ajax 回傳更新後的子留言數量
    $sub_sum_stmt = $conn->prepare("SELECT * FROM `tingkao_sub` WHERE `deleted`=0 and `parent_id`='$parent_id'");
    $sub_sum_stmt->execute();
    $sub_sum_result = $sub_sum_stmt->get_result();

    while($row = $sub_result->fetch_assoc()){
        echo json_encode(array('sub_sum' => $sub_sum_result->num_rows, 'sub_data' =>  $row), JSON_UNESCAPED_UNICODE);
    }
    // echo '子留言成功';
    // header('Location: index.php');
}else{
    echo '留言失敗';
    // header('Location: login.html');
}
}


if(!empty($_POST['edited_comment']) && isset($_POST['ex-page'])){
    $edited_comment_id =$_POST['edited_comment_id'];
    $edited_comment = $_POST['edited_comment'];
    $expage = $_POST['ex-page'];
    // 避免可以更改或刪除別人的留言，比對該編輯的留言者跟目前登入者是否為同一人
    $id_check_stmt = $conn->prepare("SELECT `user_id` FROM `tingkao_main` WHERE `id`=?");
    $id_check_stmt->bind_param("i", $edited_comment_id);
    $id_check_stmt->execute();
    $id_check_result = $id_check_stmt->get_result();
    $id_check_row = $id_check_result->fetch_assoc();
    $comment_data_id = $id_check_row['user_id'];
    if($comment_data_id === $user_id){
        $stmt = $conn->prepare("UPDATE `tingkao_main` SET `main_comment`=? WHERE `id`=?");
        $stmt->bind_param("si", $edited_comment, $edited_comment_id);
        if($stmt->execute()){
            echo '編輯留言成功';
            //導回上一頁，但是因為此頁的 $_SERVER['HTTP_REFERER'] 是 handle_edit_comment.php
            //所以用 input hidden 把真正的上一頁的留言板網址保留並且帶過來
            header("Location: $expage");
        }
    }else{
        echo "<script>
                alert('這不是你的留言，不可隨意更改喔！');
                window.location = 'index.php';
        </script>";
    }

}

if(!empty($_POST['edited_sub_comment'])){
    $edited_sub_comment_id = $_POST['edited_sub_comment_id'];
    $edited_sub_comment = $_POST['edited_sub_comment'];
    $expage = $_POST['ex-page'];
    $stmt = $conn->prepare("UPDATE `tingkao_sub` SET `sub_comment`=? WHERE `ind`=?");
    $stmt->bind_param("si", $edited_sub_comment, $edited_sub_comment_id);

    // 避免可以更改或刪除別人的留言，比對該編輯的留言者跟目前登入者是否為同一人
    $id_check_stmt = $conn->prepare("SELECT `user_id` FROM `tingkao_sub` WHERE `ind`=?");
    $id_check_stmt->bind_param("i", $edited_sub_comment_id);
    $id_check_stmt->execute();
    $id_check_result = $id_check_stmt->get_result();
    $id_check_row = $id_check_result->fetch_assoc();

    $sub_comment_data_id = $id_check_row['user_id'];
    if($sub_comment_data_id === $user_id){
        if($stmt->execute()){
            echo '編輯子留言成功';
            header("Location: $expage");
            // header('Location: index.php');
        }
    }else{
        echo "<script>
            alert('這不是你的留言，不可隨意更改喔！');
            window.location = 'index.php';
        </script>";
    }
}
?>