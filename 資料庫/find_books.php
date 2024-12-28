<?php
// 啟用 Session
session_start();

if (!isset($_SESSION['user_id'])) {
    // 若未登入，跳轉回登入頁面
    header("Location: menu.php");
    exit();
}

// 引入資料庫連線設定
include("connect.php");

if (isset($_GET['dept_name']) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $dept_name = mysqli_real_escape_string($conn, $_GET['dept_name']);
    $subject_query = "SELECT DISTINCT subject FROM book WHERE dept_name = '$dept_name'";
    $subject_result = mysqli_query($conn, $subject_query);
    
    $subjects = [];
    while ($subject = mysqli_fetch_assoc($subject_result)) {
        $subjects[] = $subject['subject'];
    }
    
    // 回傳 JSON 格式的科目資料
    echo json_encode($subjects);
    exit;
}



// 初始化變數
$message = "";
$selected_dept = "";
$selected_subject = "";

// 處理篩選請求
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['dept_name'])) {
        $selected_dept = mysqli_real_escape_string($conn, $_GET['dept_name']);
    }
    if (isset($_GET['subject'])) {
        $selected_subject = mysqli_real_escape_string($conn, $_GET['subject']);
    }
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

$conditions = [];
if (!empty($selected_dept)) {
    $conditions[] = "b.dept_name = '$selected_dept'";
}

// 如果選擇了科目，則加入條件
if (!empty($selected_subject)) {
    $conditions[] = "b.subject = '$selected_subject'";
}

// 如果有篩選條件，則在查詢中加上 WHERE 子句
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(' AND ', $conditions);
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


// 抓取所有科目選項
$subject_query = "SELECT DISTINCT subject FROM book";
$subject_result = mysqli_query($conn, $subject_query);
$all_subjects = [];
while ($row = mysqli_fetch_assoc($subject_result)) {
    $all_subjects[] = $row['subject'];
}

$subjects = [];
if (!empty($selected_dept)) {
    $subject_query = "SELECT DISTINCT subject FROM book WHERE dept_name = '$selected_dept'";
    $subject_result = mysqli_query($conn, $subject_query);
    while ($subject = mysqli_fetch_assoc($subject_result)) {
        $subjects[] = $subject['subject'];
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

        @media (max-width: 768px) {
        header nav a {
            display: block;
            margin: 5px 0;
        }
        .btn {
            width: 100%; /* Full width button on small screens */
            font-size: 16px;
        }
        table, th, td {
            font-size: 12px;
            padding: 6px;
        }

        /* Allow the table to scroll horizontally on smaller screens */
        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }

        /* Fix the columns in a horizontal layout without stacking them */
        th, td {
            white-space: nowrap;
        }

        /* Container width adjustment */
        .container {
            overflow-x: auto;
        }

        /* Form and input adjustments */
        select, input {
            width: 100%; /* Full width for select and input fields */
            font-size: 16px;
            padding: 5px;
            margin-bottom: 5px; /* Add space between elements */
        }
    
    }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="home.php">Home</a>
            <a href="find_books.php" class="active">尋找書本</a>
            <a href="bookseller.php">書本賣家</a>
        </nav>
    </header>

    <div class="container">
        <h1>尋找書本</h1>
        <form action="find_books.php" method="GET">
            <label for="dept_name">科系：</label>
            <select name="dept_name" id="dept_name" onchange="updateSubjects()">
                <option value="">全部</option>
                <?php foreach ($departments as $dept) { ?>
                    <option value="<?php echo $dept; ?>" <?php echo ($selected_dept == $dept) ? 'selected' : ''; ?>>
                        <?php echo $dept; ?>
                    </option>
                <?php } ?>
            </select>
            <label for="subject">科目：</label>
            <select name="subject" id="subject">
                <option value="">全部科目</option>
                <?php
                // 顯示所有科目
                foreach ($all_subjects as $subject) {
                    echo "<option value='".$subject."' ".($selected_subject == $subject ? 'selected' : '').">".$subject."</option>";
                }
                ?>
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

<script>
function updateSubjects() {
    var dept_name = document.getElementById("dept_name").value;
    var subjectSelect = document.getElementById("subject");

    if (dept_name) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "find_books.php?dept_name=" + dept_name, true);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var subjects = JSON.parse(xhr.responseText);
                subjectSelect.innerHTML = "<option value=''>選擇科目</option>";
                subjects.forEach(function(subject) {
                    var option = document.createElement("option");
                    option.value = subject;
                    option.text = subject;
                    subjectSelect.appendChild(option);
                });
            }
        };
        xhr.send();
    } else {
        // 如果科系選擇為「全部」，列出所有科目
        subjectSelect.innerHTML = "<option value=''>全部科目</option>";
        <?php
        foreach ($all_subjects as $subject) {
            echo "subjectSelect.innerHTML += '<option value=\"$subject\">$subject</option>';";
        }
        ?>
    }
}
</script>

</html>