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
$seller_name="";
$book = null;
$seller_ID = $_SESSION['user_id'];

// 查詢賣家名稱
$seller_query = "SELECT name FROM user WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $seller_query)) {
    mysqli_stmt_bind_param($stmt, "s", $seller_ID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $seller_name);
    if (!mysqli_stmt_fetch($stmt)) {
        $seller_name = "未找到賣家名稱";
    }
    mysqli_stmt_close($stmt);
}

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
        WHERE b.book_ID = ?
    ";

    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) == 1) {
            $book = mysqli_fetch_assoc($result);
        } else {
            $message = "未找到指定的書籍資料。";
        }
        mysqli_stmt_close($stmt);
    }
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_ID'])) {
    $book_ID = intval($_POST['book_ID']);
    $book_title = $_POST['book_title'];
    $dept_name = $_POST['dept_name'];
    $subject = $_POST['subject'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $meeting_point = $_POST['meeting_point'];
    $available_time = $_POST['available_time'];
    $contact_information = $_POST['contact_information'];

    // 更新資料庫
    mysqli_begin_transaction($conn);
    try {
        $update_book_query = "
            UPDATE book 
            SET book_title = ?, dept_name = ?, subject = ?, price = ?, description = ?
            WHERE book_ID = ?";

        $update_transaction_query = "
            UPDATE transaction 
            SET meeting_point = ?, available_time = ?, contact_information = ?
            WHERE book_ID = ?";

        // 更新 book 資料
        if ($stmt = mysqli_prepare($conn, $update_book_query)) {
            mysqli_stmt_bind_param($stmt, "sssssi", $book_title, $dept_name, $subject, $price, $description, $book_ID);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("更新書籍資料失敗: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        }

        // 更新 transaction 資料
        if ($stmt = mysqli_prepare($conn, $update_transaction_query)) {
            mysqli_stmt_bind_param($stmt, "sssi", $meeting_point, $available_time, $contact_information, $book_ID);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("更新交易資料失敗: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
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
        <h1>修改書籍</h1>
        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <a href="bookseller.php">回到主選單</a>
        <?php endif; ?>
        <?php if ($book): ?>
            <form action="edit_book.php" method="POST">
                <input type="hidden" name="book_ID" value="<?php echo htmlspecialchars($book['book_ID']); ?>">

                <div class="form-group">
                    <label for="seller_name">賣家:</label>
                    <div id="seller_name"><?php echo htmlspecialchars($seller_name); ?></div>
                </div>
                <div class="form-group">
                    <label for="book_title">書名</label>
                    <input type="text" id="book_title" name="book_title" value="<?php echo htmlspecialchars($book['book_title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="dept_name">科系</label>
                    <input type="text" id="dept_name" name="dept_name" value="<?php echo htmlspecialchars($book['dept_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="subject">科目</label>
                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($book['subject']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">價錢</label>
                    <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($book['price']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">敘述</label>
                    <textarea id="description" name="description" rows="3" required><?php echo htmlspecialchars($book['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="meeting_point">面交地點</label>
                    <input type="text" id="meeting_point" name="meeting_point" value="<?php echo htmlspecialchars($book['meeting_point']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="available_time">面交時間</label>
                    <input type="text" id="available_time" name="available_time" value="<?php echo htmlspecialchars($book['available_time']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="contact_information">聯絡資訊</label>
                    <input type="text" id="contact_information" name="contact_information" value="<?php echo htmlspecialchars($book['contact_information']); ?>" required>
                </div>
                <button type="submit" class="btn">更新</button>
            </form>
            <button class="btn" onclick="window.location.href='bookseller.php'">返回</button>
        <?php endif; ?>
    </div>
</body>
</html>
