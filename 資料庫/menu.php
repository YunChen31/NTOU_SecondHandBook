<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>海大校園書的世界</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f2e9f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .container img {
            width: 120px;
            margin-bottom: 20px;
        }
        .input-box {
            margin-bottom: 15px;
        }
        .input-box input {
            width: 200px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-group button {
            padding: 10px 20px;
            font-size: 14px;
            color: #fff;
            background-color: #6c63ff;
            border: none;
            border-radius: 5px;
            margin: 0 5px;
            cursor: pointer;
        }
        .btn-group button:hover {
            background-color: #5750d1;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>海大校園<br>書的世界</h1>
        <form action="login.php" method="POST">
            <div class="input-box">
                <input type="text" name="account" placeholder="帳號" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="密碼" required>
            </div>
            <div class="btn-group">
                <button type="button" onclick="window.location.href='register.php'">註冊</button>
                <button type="submit">登入</button>
            </div>
        </form>
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
