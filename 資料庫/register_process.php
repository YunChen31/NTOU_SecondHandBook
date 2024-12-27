<?php
// 引入資料庫連線設定
include("connect.php");

// 檢查表單是否透過 POST 提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單獲取使用者輸入的值
    $userID = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $account = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 檢查是否有重複的帳號
    $checkQuery = "SELECT * FROM user WHERE account_number = '$account'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // 帳號已存在
        echo "帳號已被使用，請使用其他帳號。";
    } else {
        // 插入資料到資料庫
        $insertQuery = "INSERT INTO user (user_ID, name, account_number, password) VALUES ('$userID', '$name', '$account', '$password')";
        if (mysqli_query($conn, $insertQuery)) {
            // 成功訊息
            echo "註冊成功！";
        } else {
            // 錯誤訊息
            echo "資料插入失敗：" . mysqli_error($conn);
        }
    }
}

// 關閉資料庫連線
mysqli_close($conn);
?>
