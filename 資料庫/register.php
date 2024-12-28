<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>註冊</title>
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
            text-align: left;
            background-color: #fff;
            padding: 30px 50px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .container h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 90%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn-submit, .btn-back {
            padding: 10px 20px;
            font-size: 14px;
            color: #fff;
            background-color: #6c63ff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
        }
        .btn-submit {
            background-color: #6c63ff;
        }
        .btn-submit:hover {
            background-color: #5750d1;
        }
        .btn-back {
            background-color: #6c63ff;
        }
        .btn-back:hover {
            background-color: #5750d1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>註冊</h1>
        <form action="register_process.php" method="POST">
            <div class="input-group">
                <label for="username">帳號</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">密碼</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="student_id">學號</label>
                <input type="text" id="student_id" name="student_id" required>
            </div>
            <div class="input-group">
                <label for="name">名字</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn-submit">註冊</button>
                <button type="button" class="btn-back" onclick="window.location.href='menu.php'">返回登入頁面</button>
            </div>
        </form>
    </div>
</body>
</html>
