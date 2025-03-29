<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24 点比赛系统 - 登录与主页</title>
    <style>
        /* 全局样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        /* 标题样式 */
        h1 {
            color: #333;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            font-size: 36px;
        }

        /* 按钮样式 */
        button,
        a {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        button:hover,
        a:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        /* 容器样式 */
        .container {
            background-color: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 80%;
            max-width: 440px;
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
            text-align: center;
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
            /* 标题字体大小调整 */
            h1 {
                font-size: 28px;
            }

            /* 容器内边距调整 */
            .container {
                padding: 20px;
            }

            /* 表单内边距调整 */
            form {
                padding: 20px;
            }

            /* 按钮和链接的内边距调整 */
            button,
            a,
            input[type="submit"] {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <?php
    session_start();
    require_once 'config.php';

    if (isset($_SESSION['user_id'])) {
        $conn = getDatabaseConnection();
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT username FROM users WHERE id = $user_id";
        $result = $conn->query($sql);
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $username = $row['username'];
        }
        $conn->close();
    }
    ?>
    <div class="container">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h1>欢迎，<?php echo $username; ?></h1>
            <button onclick="window.location.href='game.php'">开始答题</button>
            <a href="logout.php">退出登录</a>
        <?php else: ?>
            <h1>24 点比赛系统         </h1>
            <form action="login_process.php" method="post">
                <label for="username">用户名：</label>
                <input type="text" id="username" name="username">
                <label for="password">密码：</label>
                <input type="password" id="password" name="password">
                <input type="submit" value="登录">
            
            <div class="">
                <a href="register.php">没有账号？点击注册</a>
            </div></form>
        <?php endif; ?>
    </div>
</body>

</html>