<?php
// connect to db
// 建立登入檔案
$servername = "166.62.28.131"; 
// 166.62.28.131
$username = "";
$password = "";
$dbname = "mentor_program_db";

//連接資料庫
$conn = new mysqli($servername, $username, $password, $dbname); // 建立一個新物件 mysqli
$conn->query("SET NAMES 'UTF8'"); // 設定資料庫編碼
$conn->query("set time_zone = '+8:00'"); // 設定資料庫時區，不然會以使用者的瀏覽器時間為主
if ($conn->connect_error) {  //connect_error 是內建函式 回傳錯誤訊息
    die("Connection failed(連接失敗): " . $conn->connect_error);  // die 停止執行 PHP 程式
}
// else{
//     echo "連接成功";
// };
?>