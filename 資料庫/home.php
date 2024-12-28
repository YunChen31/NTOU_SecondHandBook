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
            margin-left: auto;
            padding: 8px 10px;
            background-color: rgb(168, 164, 239);
            color: rgb(22, 17, 117);
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        header nav a.logout-btn:hover {
            background-color: rgb(129, 123, 237);
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
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
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
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .carousel-images {
            display: flex;
            transition: transform 0.5s ease-in-out;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .carousel-images img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .faq {
            padding: 20px;
            margin-top: 0;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .faq h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .faq-container {
            display: flex;
            justify-content: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .faq-item {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            width: 30%;
            margin-bottom: 20px;
        }

        .faq-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;  /* 確保圖片填滿框架 */
            aspect-ratio: 16/9;  /* 保持圖片比例為16:9 */
            margin-bottom: 15px;
        }

        .faq-text {
            flex: 1;
            text-align: center;
        }

        .faq-item h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }

        .faq-item p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: #555;
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
                height: 100%; /* 限制高度 */
                flex:100%;
            }

            .carousel-images {
                object-fit: cover;
            }

            .carousel-images img {
                width: 100%;
                height: 100%;
                object-fit: cover
            }

            header nav a {
                margin: 0 5px; /* 增加按鈕間距 */
            }

            .text-intro p {
                font-size: 1rem; /* 減小字體 */
            }
            .faq-container {
                flex-direction: column;
                align-items: center;
            }

            .faq-item {
                width: 80%;
            }

            .faq-item img {
                width: 300px;  /* 增大圖片的寬度 */
                height: auto;  /* 保持圖片比例 */
                object-fit: cover;  /* 確保圖片填滿框架 */
                aspect-ratio: 16/9;  /* 保持圖片比例 */
            }

            .faq-text {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="home.php" class="active">Home</a>
            <a href="find_books.php">尋找書本</a>
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
            </div>
        </div>
    </div>
    <div class="faq">
        <h2>常見問題</h2>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-text">
                    <h3>如何查詢書籍？</h3>
                    <p>您可以透過我們的搜尋功能，篩選科系或科目來查詢書籍。</p>
                </div>
                <img src="尋找書本.png" alt="圖片1">
            </div>
            <div class="faq-item">
                <div class="faq-text">
                    <h3>如何出售我的書籍？</h3>
                    <p>您可以在「書本賣家」頁面上傳您的書籍資訊，讓其他用戶看到並聯繫您。要是成功售出，可以刪除書本。</p>
                </div>
                <img src="書本賣家.png" alt="圖片2">
            </div>
            <div class="faq-item">
                <div class="faq-text">
                    <h3>如何聯繫賣家？</h3>
                    <p>您可以在「尋找書本」頁面上，找到賣家聯絡資訊，並連絡賣家。</p>
                </div>
                <img src="聯絡.png" alt="圖片3">
            </div>
        </div>
    </div>

    <script>
        const images = document.querySelector(".carousel-images");
        const indicators = document.querySelectorAll(".indicators div");
        let currentIndex = 0;

        document.getElementById("next").addEventListener("click", function() {
            currentIndex = (currentIndex + 1) % images.children.length;
            updateCarousel();
        });

        document.getElementById("prev").addEventListener("click", function() {
            currentIndex = (currentIndex - 1 + images.children.length) % images.children.length;
            updateCarousel();
        });

        function updateCarousel() {
            const offset = -currentIndex * 100;
            images.style.transform = `translateX(${offset}%)`;
            indicators.forEach(indicator => indicator.classList.remove("active"));
            indicators[currentIndex].classList.add("active");
        }
    </script>
</body>
</html>
