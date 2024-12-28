<?php
// 啟用 Session
session_start();
// 確認是否已登入，否則跳轉到登入頁面
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 引入資料庫連線設定
include("connect.php");

// 初始化賣家名稱變數
$seller_name = ""; // 預設為未找到賣家名稱
// 初始化訊息
$message = "";
    $seller_ID = $_SESSION['user_id'];

    // 查詢賣家名稱
    $seller_query = "SELECT name FROM user WHERE user_id = '$seller_ID'";
    $seller_result = mysqli_query($conn, $seller_query);

    if ($seller_result && mysqli_num_rows($seller_result) > 0) {
        $row = mysqli_fetch_assoc($seller_result);
        $seller_name = $row['name'];
    } else {
        $seller_name = "未找到賣家名稱";
    }
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單接收資料
    $book_title = mysqli_real_escape_string($conn, $_POST['book_title']);
    $dept_name = mysqli_real_escape_string($conn, $_POST['dept_name']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $meeting_point = mysqli_real_escape_string($conn, $_POST['meeting_point']);
    $available_time = mysqli_real_escape_string($conn, $_POST['available_time']);
    $contact_information = mysqli_real_escape_string($conn, $_POST['contact_information']);
    $seller_ID = $_SESSION['user_id'];

    // 開始資料庫事務
    mysqli_begin_transaction($conn);

    try {
        // 插入資料到 book 表格
        $book_query = "INSERT INTO book (book_title, seller_ID, dept_name, subject, price, description)
                       VALUES ('$book_title', '$seller_ID', '$dept_name', '$subject', '$price', '$description')";
        if (!mysqli_query($conn, $book_query)) {
            throw new Exception("新增書籍失敗：" . mysqli_error($conn));
        }

        // 獲取插入的 book_ID
        $book_ID = mysqli_insert_id($conn);

        // 插入資料到 transaction 表格
        $transaction_query = "INSERT INTO transaction (book_ID, seller_ID, meeting_point, available_time, contact_information)
                              VALUES ('$book_ID', '$seller_ID', '$meeting_point', '$available_time', '$contact_information')";
        if (!mysqli_query($conn, $transaction_query)) {
            throw new Exception("新增交易資訊失敗：" . mysqli_error($conn));
        }

        // 提交交易
        mysqli_commit($conn);
        $message = "書籍與交易資訊新增成功！";

    } catch (Exception $e) {
        // 若有錯誤，回滾交易
        mysqli_rollback($conn);
        $message = $e->getMessage();
    }
}

// 關閉資料庫連線
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新增書籍</title>
    <style>
       body {
    font-family: Arial, sans-serif;
    background-color: #f2e9f2;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

.container {
    background-color: #fff;
    padding: 20px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px; /* 設定最大寬度 */
    box-sizing: border-box; /* 包括 padding 和 border */
}

h1 {
    font-size: 24px;
    color: #333;
    margin-bottom: 20px;
    text-align: center;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-size: 14px;
    margin-bottom: 5px;
}

.form-group input, .form-group textarea {
    width: 100%;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.form-group textarea {
    resize: none;
}

.btn {
    width: 100%;
    padding: 10px 20px;
    font-size: 16px;
    background-color: #6c63ff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
}

.btn:hover {
    background-color: #5750d1;
}

.message {
    text-align: center;
    color: green;
    margin-bottom: 10px;
}

/* 響應式設計：當螢幕寬度小於768px時調整樣式 */
@media (max-width: 768px) {
    .container {
        padding: 20px;
        width: 90%; /* 調整為 90% 寬度以適應小螢幕 */
    }

    h1 {
        font-size: 20px; /* 調整標題字型大小 */
    }

    .form-group input, .form-group textarea {
        font-size: 12px; /* 輸入框的字型大小 */
    }

    .btn {
        font-size: 14px; /* 按鈕字型大小 */
        padding: 8px 16px; /* 按鈕大小調整 */
    }
}

/* 響應式設計：當螢幕寬度小於480px時調整樣式 */
@media (max-width: 480px) {
    .container {
        width: 100%; /* 更小螢幕時使用100%寬度 */
    }

    h1 {
        font-size: 18px; /* 更小螢幕時標題字型大小調整 */
    }

    .form-group input, .form-group textarea {
        font-size: 10px; /* 更小螢幕時輸入框字型大小調整 */
    }

    .btn {
        font-size: 12px; /* 按鈕字型大小調整 */
        padding: 6px 12px; /* 按鈕大小調整 */
    }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>新增書籍</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="add_book.php" method="POST">

            <div class="form-group">
            <label for="seller_name">賣家:</label>
            <div id="seller_name"><?php echo htmlspecialchars($seller_name); ?></div>
            </div>
            <div class="form-group">
                <label for="book_title">書名</label>
                <input type="text" id="book_title" name="book_title" placeholder="輸入書名" required>
            </div>
            <div class="form-group">
                <label for="dept_name">科系</label>
                <input type="text" id="dept_name" name="dept_name" placeholder="輸入科系" required>
            </div>
            <div class="form-group">
                <label for="subject">科目</label>
                <input type="text" id="subject" name="subject" placeholder="輸入科目" required>
            </div>
            <div class="form-group">
                <label for="price">價錢</label>
                <input type="number" id="price" name="price" placeholder="輸入價錢" required>
            </div>
            <div class="form-group">
                <label for="description">敘述</label>
                <textarea id="description" name="description" rows="3" placeholder="輸入敘述" required></textarea>
            </div>
            <div class="form-group">
                <label for="meeting_point">面交地點</label>
                <input type="text" id="meeting_point" name="meeting_point" placeholder="輸入面交地點" required>
            </div>
            <div class="form-group">
                <label for="available_time">面交時間</label>
                <input type="text" id="available_time" name="available_time" placeholder="輸入面交時間" required>
            </div>
            <div class="form-group">
                <label for="contact_information">聯絡資訊</label>
                <input type="text" id="contact_information" name="contact_information" placeholder="輸入聯絡資訊" required>
            </div>
            <button type="submit" class="btn">新增書籍</button>
        </form>
        <button class="btn" onclick="window.location.href='bookseller.php'">返回</button>
    </div>
</body>
</html>
