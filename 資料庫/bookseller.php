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

    // 刪除 `transaction` 和 `book` 中的資料
    $delete_transaction_query = "DELETE FROM transaction WHERE book_ID = ?";
    $delete_book_query = "DELETE FROM book WHERE book_ID = ?";

    // 使用 MySQL 交易保證操作一致性
    mysqli_begin_transaction($conn);
    try {
        // 刪除 `transaction` 資料
        $stmt = $conn->prepare($delete_transaction_query);
        $stmt->bind_param("i", $delete_id);
        if (!$stmt->execute()) {
            throw new Exception("刪除交易資料失敗: " . $stmt->error);
        }

        // 刪除 `book` 資料
        $stmt = $conn->prepare($delete_book_query);
        $stmt->bind_param("i", $delete_id);
        if (!$stmt->execute()) {
            throw new Exception("刪除書籍資料失敗: " . $stmt->error);
        }

        // 提交刪除操作
        mysqli_commit($conn);
        $delete_message = "資料已成功刪除！";
    } catch (Exception $e) {
        // 回滾刪除操作
        mysqli_rollback($conn);
        $delete_message = $e->getMessage();
    } finally {
        $stmt->close();
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
    WHERE b.seller_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// 如果查詢失敗，顯示錯誤訊息
if (!$result) {
    die("資料庫查詢失敗：" . $stmt->error);
}

// 將查詢結果存儲到陣列
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}

// 關閉資料庫連線
$stmt->close();
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

        /* RWD 支援 */
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            th, td {
                padding: 5px;
            }
            .btn {
                font-size: 12px;
                padding: 5px 10px;
            }
        }

        @media (max-width: 480px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            header nav {
                font-size: 14px;
            }
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
            <div class="message"><?php echo htmlspecialchars($delete_message, ENT_QUOTES, 'UTF-8'); ?></div>
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
                            <td><?php echo htmlspecialchars($book['book_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['dept_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>$<?php echo htmlspecialchars($book['price'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['meeting_point'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['available_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($book['contact_information'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form action="bookseller.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($book['book_ID'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn delete-btn">刪除</button>
                                </form>
                                <form action="edit_book.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($book['book_ID'], ENT_QUOTES, 'UTF-8'); ?>">
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
