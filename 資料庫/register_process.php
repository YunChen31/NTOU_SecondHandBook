<?php

include("connect.php");

// 插入資料進資料庫
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userID = mysqli_real_escape_string($conn, $_POST['student_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $account = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 查詢是否已存在相同 user_ID 或 account_number
    $checkQuery = "SELECT * FROM user WHERE user_ID = ? OR account_number = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $userID, $account);
        mysqli_stmt_execute($stmt);
        $checkResult = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($checkResult) > 0) {
            // 檢查具體的重複類型
            $row = mysqli_fetch_assoc($checkResult);
            if ($row['user_ID'] === $userID) {
                echo "<script>alert('學號已被註冊，請使用其他學號。'); history.back();</script>";
            } elseif ($row['account_number'] === $account) {
                echo "<script>alert('帳號已被使用，請使用其他帳號。'); history.back();</script>";
            }
        } else {
            // 插入 user 資料進資料庫
            $insertQuery = "INSERT INTO user (user_ID, name, account_number, password) VALUES (?, ?, ?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);

            if ($insertStmt) {
                mysqli_stmt_bind_param($insertStmt, "ssss", $userID, $name, $account, $password);
                if (mysqli_stmt_execute($insertStmt)) {
                    echo "<script>alert('註冊成功！'); window.location.href='login.php';</script>";
                } else {
                    // 捕獲可能的資料庫錯誤（例如主鍵或其他約束）
                    echo "<script>alert('資料插入失敗：" . mysqli_error($conn) . "'); history.back();</script>";
                }
                mysqli_stmt_close($insertStmt);
            } else {
                echo "<script>alert('準備插入資料時發生錯誤。'); history.back();</script>";
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('準備檢查帳號或學號時發生錯誤。'); history.back();</script>";
    }
}

mysqli_close($conn);
?>
