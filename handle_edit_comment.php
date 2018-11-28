<?php require_once('connect.php'); ?>
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
    <style>

    </style>
</head>
<?
//父留言編輯
if(!empty($_POST['message_edit'])){
    $id = $_POST['message_edit'];
    $stmt = $conn->prepare("SELECT * FROM `tingkao_main` WHERE `id`=? ");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows>0){
        $row = $result->fetch_assoc();
        $main_comment = $row['main_comment'];
    }
?>


<body>
    <div class="wrap">
        <div class="main_comment">
            <p class="commenter">編輯留言內容：</p>
            <form action="handle_comment.php" method="post">
                <input type="hidden" name="edited_comment_id" value="<? echo $id;?>">
                <input type="hidden" name="ex-page" value="<? echo $_SERVER['HTTP_REFERER'];?>">
                <textarea class="comment_text" name="edited_comment" id="" cols="30" rows="6"><? echo $main_comment ;?></textarea>
                <input class="comment_btn btn btn-outline-primary" type="submit" value="送出">
            </form>
        </div>
    </div>
</body>
</html>

<?
}
//子留言編輯
if(!empty($_POST['sub_message_edit'])){
    $ind = $_POST['sub_message_edit'];
    $stmt = $conn->prepare("SELECT * FROM `tingkao_sub` WHERE `ind`=? ");
    $stmt->bind_param("s", $ind);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows>0){
        $row = $result->fetch_assoc();
        $sub_comment = $row['sub_comment'];
    }
?>

<body>
    <div class="wrap">
        <div class="main_comment">
            <p class="commenter">編輯留言內容：</p>
            <form action="handle_comment.php" method="post">
                <input type="hidden" name="edited_sub_comment_id" value="<? echo $ind;?>">
                <input type="hidden" name="ex-page" value="<? echo $_SERVER['HTTP_REFERER'];?>">
                <textarea class="comment_text" name="edited_sub_comment" id="" cols="30" rows="6"><? echo $sub_comment ;?></textarea>
                <input class="comment_btn btn btn-outline-primary" type="submit" value="送出">
            </form>
        </div>
    </div>
</body>
</html>

<?
}
//刪除父留言
if(!empty($_POST['message_delete'])){
    // echo $_POST['message_delete'];
    $id = $_POST['message_delete'];
    $stmt = $conn->prepare("UPDATE `tingkao_main` SET `deleted`=1 WHERE `id`=? ");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    // header('Location: index.php');
    
}

//刪除子留言
if(!empty($_POST['sub_message_delete'])){
    $ind = $_POST['sub_message_delete'];
    $parent_id = $_POST['sub_message_parent_id'];
    $stmt = $conn->prepare("UPDATE `tingkao_sub` SET `deleted`=1 WHERE `ind`=? ");
    $stmt->bind_param("s", $ind);
    $stmt->execute();
    // header('Location: index.php');
    $sub_stmt = $conn->prepare("SELECT * FROM `tingkao_sub` WHERE `deleted`=0 and `parent_id`='$parent_id'");
    $sub_stmt->execute();
    $sub_result = $sub_stmt->get_result();
    echo $sub_result->num_rows;
}


?>