<?php
// 啟用 Session
session_start();

// 引入資料庫連線設定
include("connect.php");

// 初始化變數
$message = "";
$selected_dept = "";

// 處理篩選請求
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['dept_name'])) {
    $selected_dept = mysqli_real_escape_string($conn, $_GET['dept_name']);
}

// 從資料庫抓取書籍資料
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
";

if (!empty($selected_dept)) {
    $query .= " WHERE b.dept_name = '$selected_dept'";
}

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

// 抓取所有科系選項
$dept_query = "SELECT DISTINCT dept_name FROM book";
$dept_result = mysqli_query($conn, $dept_query);
$departments = [];
while ($row = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $row['dept_name'];
}

// 關閉資料庫連線
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>尋找書本</title>
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
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="menu.php">Home</a>
            <a href="find_books.php" class="active">尋找書本</a>
            <a href="bookseller.php">書本賣家</a>
        </nav>
    </header>

    <div class="container">
        <h1>尋找書本</h1>
        <form action="find_books.php" method="GET">
            <label for="dept_name">科系：</label>
            <select name="dept_name" id="dept_name">
                <option value="">全部</option>
                <?php foreach ($departments as $dept) { ?>
                    <option value="<?php echo $dept; ?>" <?php echo ($selected_dept == $dept) ? 'selected' : ''; ?>>
                        <?php echo $dept; ?>
                    </option>
                <?php } ?>
            </select>
            <button type="submit" class="btn">篩選</button>
        </form>
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
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="9">目前無資料</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
