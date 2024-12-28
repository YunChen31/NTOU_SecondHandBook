<?php
// 啟用 Session
session_start();

// 引入資料庫連線設定
include("connect.php");
// 檢查表單是否透過 POST 提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單獲取使用者輸入的帳號和密碼
    $account = mysqli_real_escape_string($conn, $_POST['account']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 從資料庫中檢查帳號和密碼是否匹配
    $query = "SELECT * FROM user WHERE account_number = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $account, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if (mysqli_num_rows($result) == 1) {
        // 帳號和密碼匹配，取得使用者資料
        $user = mysqli_fetch_assoc($result);

        // 設置 Session
        $_SESSION['user_id'] = $user['user_ID'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['account'] = $user['account_number'];

        // 跳轉到書本賣家頁面
        header("Location: home.php");
        exit();
    } else {
        // 帳號或密碼錯誤，顯示錯誤訊息並彈出視窗提示
        echo "<script>alert('帳號或密碼錯誤，請重新輸入。'); window.location.href='menu.php';</script>";
    }
    mysqli_stmt_close($stmt);
}
// 關閉資料庫連線
mysqli_close($conn);
?>
