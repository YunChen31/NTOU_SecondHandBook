<?php
// 啟用 Session
session_start();

// 檢查使用者是否已登入
if (!isset($_SESSION['user_id'])) {
    // 若未登入，跳轉回登入頁面
    header("Location: menu.php");
    exit();
}

// 引入資料庫連線設定
include("connect.php");

// 初始化訊息
$message = "";
$delete_message = "";

// 處理刪除請求
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // 刪除 `transaction` 中的資料
    $delete_transaction_query = "DELETE FROM transaction WHERE book_ID = '$delete_id'";
    $delete_book_query = "DELETE FROM book WHERE book_ID = '$delete_id'";

    // 執行刪除操作
    mysqli_begin_transaction($conn);
    try {
        if (!mysqli_query($conn, $delete_transaction_query)) {
            throw new Exception("刪除交易資料失敗: " . mysqli_error($conn));
        }
        if (!mysqli_query($conn, $delete_book_query)) {
            throw new Exception("刪除書籍資料失敗: " . mysqli_error($conn));
        }

        // 提交刪除
        mysqli_commit($conn);
        $delete_message = "資料已成功刪除！";
    } catch (Exception $e) {
        // 回滾刪除操作
        mysqli_rollback($conn);
        $delete_message = $e->getMessage();
    }
}

// 從 Session 獲取目前登入的 user_id
$user_id = $_SESSION['user_id'];

// 從資料庫抓取該使用者的書籍資料
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
    WHERE b.seller_ID = '$user_id'";

$result = mysqli_query($conn, $query);

// 如果查詢失敗，顯示錯誤訊息
if (!$result) {
    die("資料庫查詢失敗：" . mysqli_error($conn));
}

// 將查詢結果存儲到陣列
$books = [];
while ($row = mysqli_fetch_assoc($result)) {
    $books[] = $row;
}

// 關閉資料庫連線
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>書本賣家</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2e9f2;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #fff;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        header nav a {
            margin: 0 10px;
            text-decoration: none;
            color: #333;
        }
        header nav a.active {
            font-weight: bold;
            text-decoration: underline;
        }
        .container {
            padding: 20px;
        }
        .btn {
            padding: 8px 15px;
            font-size: 14px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #5750d1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: center;
            padding: 8px;
        }
        th {
            background-color: #f8f8f8;
        }
        .message {
            color: green;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="home.php">Home</a>
            <a href="find_books.php">尋找書本</a>
            <a href="bookseller.php" class="active">書本賣家</a>
        </nav>
    </header>

    <div class="container">
        <h1>書本賣家</h1>
        <?php if ($delete_message): ?>
            <div class="message"><?php echo $delete_message; ?></div>
        <?php endif; ?>
        <div>
            <button class="btn" onclick="window.location.href='add_book.php'">新增</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>書名</th>
                    <th>科系</th>
                    <th>科目</th>
                    <th>價錢</th>
                    <th>敘述</th>
                    <th>面交地點</th>
                    <th>時間</th>
                    <th>聯絡資訊</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($books)) { ?>
                    <?php foreach ($books as $index => $book) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $book['book_title']; ?></td>
                            <td><?php echo $book['dept_name']; ?></td>
                            <td><?php echo $book['subject']; ?></td>
                            <td>$<?php echo $book['price']; ?></td>
                            <td><?php echo $book['description']; ?></td>
                            <td><?php echo $book['meeting_point']; ?></td>
                            <td><?php echo $book['available_time']; ?></td>
                            <td><?php echo $book['contact_information']; ?></td>
                            <td>
                                <form action="bookseller.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $book['book_ID']; ?>">
                                    <button type="submit" class="btn">刪除</button>
                                </form>
                                <form action="edit_book.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="edit_id" value="<?php echo $book['book_ID']; ?>">
                                    <button type="submit" class="btn">修改</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="10">目前無資料</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
