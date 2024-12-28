<?php
session_start(); // 啟動會話

// 清除所有 session 資料
session_unset();

// 銷毀 session
session_destroy();

// 重定向到首頁
header("Location: menu.php");
exit();
?>
