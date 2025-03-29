<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录</title>
    <style>
        /* 全局样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* 表单容器样式 */
        form {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 80%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* 标签样式 */
        label {
            font-weight: 600;
            color: #333;
        }

        /* 输入框样式 */
        input[type="text"],
        input[type="password"] {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        /* 提交按钮样式 */
        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* 注册链接样式 */
        .register-link {
            text-align: center;
            margin-top: 20px;
        }

        .register-link a {
            color: #007BFF;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #0056b3;
        }

        /* 媒体查询 - 小屏幕设备 */
        @media (max-width: 600px) {
            form {
                padding: 20px;
            }

              /* 设置比赛名称显示样式 */
       .game-name-display {
            text-align: center;
            font-size: 20px;
            font-size: clamp(16px, 2vw, 20px); /* 自适应字体大小 */
            margin: 10px 0;
            color: white; /* 文字颜色 */
            background-color: #007BFF; /* 背景颜色 */
            padding: 10px; /* 内边距 */
            border-radius: 5px; /* 圆角 */
        }
    </style>
</head>

<body>

    <form action="login_process.php" method="post">
    <?php
    if (isset($_SESSION['success'])) {
        echo '<p style="color: green;">' . $_SESSION['success'] . '</p>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
    }
    ?> <div class="game-name-display">
        <center><strong><p style="font-size: 40px; font-family: Arial, sans-serif;"><?php echo htmlspecialchars($gameName); ?></p></strong></center>
    </div>
        <label for="username">用户名：</label>
        <input type="text" id="username" name="username">
        <label for="password">密码：</label>
        <input type="password" id="password" name="password">
        <input type="submit" value="登录">
    
    <div class="register-link">
        <a href="register.php">没有账号？点击注册</a>
    </div></form>
</body>

</html>