<?php
// 啟用 Session
session_start();

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: menu.php");
    exit();
}

// 引入資料庫連線設定
include("connect.php");

// 初始化變數
$message = "";
$book = null;

// 檢查是否有 edit_id 傳入
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);

    // 從資料庫中查詢對應的書籍資料
    $query = "
        SELECT 
            b.book_ID,
            b.book_title,
            b.dept_name,
            b.subject,
            b.price,
            b.description,
            t.meeting_point,
            t.available_time,
            t.contact_information
        FROM book b
        JOIN transaction t ON b.book_ID = t.book_ID
        WHERE b.book_ID = '$edit_id'
    ";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $book = mysqli_fetch_assoc($result);
    } else {
        $message = "未找到指定的書籍資料。";
    }
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_ID'])) {
    $book_ID = intval($_POST['book_ID']);
    $book_title = mysqli_real_escape_string($conn, $_POST['book_title']);
    $dept_name = mysqli_real_escape_string($conn, $_POST['dept_name']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $meeting_point = mysqli_real_escape_string($conn, $_POST['meeting_point']);
    $available_time = mysqli_real_escape_string($conn, $_POST['available_time']);
    $contact_information = mysqli_real_escape_string($conn, $_POST['contact_information']);

    // 更新資料庫
    mysqli_begin_transaction($conn);
    try {
        $update_book_query = "
            UPDATE book 
            SET book_title = '$book_title', dept_name = '$dept_name', subject = '$subject', price = '$price', description = '$description'
            WHERE book_ID = '$book_ID'";

        $update_transaction_query = "
            UPDATE transaction 
            SET meeting_point = '$meeting_point', available_time = '$available_time', contact_information = '$contact_information'
            WHERE book_ID = '$book_ID'";

        if (!mysqli_query($conn, $update_book_query)) {
            throw new Exception("更新書籍資料失敗: " . mysqli_error($conn));
        }

        if (!mysqli_query($conn, $update_transaction_query)) {
            throw new Exception("更新交易資料失敗: " . mysqli_error($conn));
        }

        mysqli_commit($conn);
        $message = "資料已成功更新！";
    } catch (Exception $e) {
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
    <title>修改書籍</title>
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
        <h1>修改書籍</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
            <a href="bookseller.php">回到主選單</a>
        <?php endif; ?>
        <?php if ($book): ?>
            <form action="edit_book.php" method="POST">
                <input type="hidden" name="book_ID" value="<?php echo $book['book_ID']; ?>">
                <div class="form-group">
                    <label for="book_title">書名</label>
                    <input type="text" id="book_title" name="book_title" value="<?php echo $book['book_title']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="dept_name">科系</label>
                    <input type="text" id="dept_name" name="dept_name" value="<?php echo $book['dept_name']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="subject">科目</label>
                    <input type="text" id="subject" name="subject" value="<?php echo $book['subject']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">價錢</label>
                    <input type="number" id="price" name="price" value="<?php echo $book['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">敘述</label>
                    <textarea id="description" name="description" rows="3" required><?php echo $book['description']; ?></textarea>
                </div>
                <div class="form-group">
                    <label for="meeting_point">面交地點</label>
                    <input type="text" id="meeting_point" name="meeting_point" value="<?php echo $book['meeting_point']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="available_time">面交時間</label>
                    <input type="text" id="available_time" name="available_time" value="<?php echo $book['available_time']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_information">聯絡資訊</label>
                    <input type="text" id="contact_information" name="contact_information" value="<?php echo $book['contact_information']; ?>" required>
                </div>
                <button type="submit" class="btn">更新</button>
            </form>
            <button class="btn" onclick="window.location.href='bookseller.php'">返回</button>
        <?php else: ?>
        <?php endif; ?>
    </div>
</body>
</html>
