<?php
session_start();

include("connect.php");
// 確保使用者已經登入，並且 session 中有 userId
if (!isset($_SESSION['user_id'])) {
    // 如果沒有登入，重定向到登入頁面
    header("Location: menu.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT name FROM user WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // "i" 表示整數型態
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();

// 關閉連接
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首頁</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: #f2e9f2;
            color: black;
        }
        header {
            background-color: #fff;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        header nav a {
            position: relative;
            margin: 0 10px;
            text-decoration: none;
            color: #333;
        }
        header nav a.active {
            font-weight: bold;
            text-decoration: underline;
        }

        header nav a.logout-btn {
            margin-left: auto; /* 使登出按鈕推到最右邊 */
            padding: 8px 10px;
            background-color: rgb(168, 164, 239);
            color: rgb(22, 17, 117);
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        header nav a.logout-btn:hover {
            background-color:rgb(129, 123, 237);
        }

        .container {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            width: 100%;
            height: 100vh;
            padding: 100px;
            box-sizing: border-box;
        }
        .text-intro {
            flex: 1;
            padding: 15px;
            display: flex;           /* 使用 flex 來排版 */
            flex-direction: column;  /* 垂直排列 */
            justify-content: flex-start; /* 將內容對齊至容器的上方 */
            gap: 0px;
        }
        .text-intro h1 {
            font-size: 2.5rem;
            margin-bottom: 0px;
        }
        .text-intro h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .text-intro p {
            line-height: 1.6;
            font-size: 1.2rem;
        }
        .carousel {
            flex: 1;
            position: relative;
            overflow: hidden;
            width: 100%;  /* 讓carousel的寬度填滿父容器 */
            height: 100%;; /* 限制高度在視口內 */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
            width: 100%; /* 設為 100% 以顯示單一圖片 */
            height: 100%;
            object-fit: cover;
        }

        .carousel-images img {
            width: 100%; /* 每張圖片的寬度等於容器寬度 */
            height: 100%; /* 圖片高度填滿區域 */
            object-fit: cover; /* 保持比例且填充 */
        }

        .carousel-buttons {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }
        .carousel-buttons button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
        }
        .carousel-buttons button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .indicators {
            position: absolute;
            bottom: 10px;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .indicators div {
            width: 10px;
            height: 10px;
            margin: 0 5px;
            background-color: #ddd;
            border-radius: 50%;
            cursor: pointer;
        }
        .indicators .active {
            background-color: black;
        }

        @media screen and (max-width: 768px) {
        .container {
            flex-direction: column; /* 改為垂直排列 */
            padding: 20px;
        }

        .text-intro {
            padding: 10px;
            gap: 10px;
        }

        .text-intro h1, .text-intro h2 {
            font-size: 2rem; /* 調整字體大小 */
            margin-bottom: 10px;
        }

        .carousel {
            width: 100%;
            height: 300px; /* 限制高度 */
            flex: 1.2;
        }

        .carousel-images img {
            object-fit: cover; /* 確保圖片以適當比例顯示 */
        }

        header nav a {
            margin: 0 5px; /* 增加按鈕間距 */
        }

        .text-intro p {
            font-size: 1rem; /* 減小字體 */
        }
}
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="home.php" class="active">Home</a>
            <a href="find_books.php" >尋找書本</a>
            <a href="bookseller.php">書本賣家</a>
            <a href="logout.php" class="logout-btn">登出</a>
        </nav>
    </header>
    <div class="container">
        <div class="text-intro">
            <h1>歡迎登入，<?php echo htmlspecialchars($username); ?>！</h1>
            <h2>歡迎來到海大二手書平台</h2>
            <p>我們提供最方便的網頁<br>
                讓海大學生能夠輕鬆得知二手書資訊。<br>
                透過簡單的操作，您可以搜尋需要的書籍<br>
                也可以放上想賣的書本。</p>
        </div>
        <div class="carousel">
            <div class="carousel-images">
                <img src="school.jpg" alt="校園圖片1">
                <img src="book.jpg" alt="書本圖片2">
            </div>
            <div class="carousel-buttons">
                <button id="prev">&lt;</button>
                <button id="next">&gt;</button>
            </div>
            <div class="indicators">
                <div class="active" data-index="0"></div>
                <div data-index="1"></div>
                <div data-index="2"></div>
            </div>
        </div>
    </div>

    <script>
        const images = document.querySelector(".carousel-images");
        const indicators = document.querySelectorAll(".indicators div");
        const prevBtn = document.getElementById("prev");
        const nextBtn = document.getElementById("next");

        let currentIndex = 0;

        function updateCarousel(index) {
            images.style.transform = `translateX(-${index * 100}%)`;
            indicators.forEach(indicator => indicator.classList.remove("active"));
            indicators[index].classList.add("active");
        }

        prevBtn.addEventListener("click", () => {
            currentIndex = (currentIndex > 0) ? currentIndex - 1 : indicators.length - 1;
            updateCarousel(currentIndex);
        });

        nextBtn.addEventListener("click", () => {
            currentIndex = (currentIndex < indicators.length - 1) ? currentIndex + 1 : 0;
            updateCarousel(currentIndex);
        });

        indicators.forEach(indicator => {
            indicator.addEventListener("click", () => {
                currentIndex = parseInt(indicator.getAttribute("data-index"));
                updateCarousel(currentIndex);
            });
        });
    </script>
</body>
</html>
