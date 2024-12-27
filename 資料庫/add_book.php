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

// 初始化訊息
$message = "";

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

    // 從 Session 中取得 seller_ID
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
            width: 500px;
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
            text-align: center;
            text-decoration: none; /* 確保文字樣式一致 */
            display: block; /* 確保按鈕占滿一整行 */
        }
        .btn:hover {
            background-color: #5750d1;
        }
        .message {
            text-align: center;
            color: green;
            margin-bottom: 10px;
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
