<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注册</title>
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

        /* 输入框和选择框样式 */
        input[type="text"],
        input[type="password"],
        select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
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

        /* 返回登录链接样式 */
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #007BFF;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #0056b3;
        }

        /* 媒体查询 - 小屏幕设备 */
        @media (max-width: 600px) {
            form {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
  
    <form action="register_process.php" method="post">
        <label for="school">学校：</label>
        <input type="text" id="school" name="school" value="xxx小学">
        <label for="grade">年级：</label>
        <select id="grade" name="grade">
            <option value="一年级">一年级</option>
            <option value="二年级">二年级</option>
            <option value="三年级">三年级</option>
            <option value="四年级">四年级</option>
            <option value="五年级">五年级</option>
            <option value="六年级">六年级</option>
        </select>
        <label for="class">班级：</label>
        <select id="class" name="class">
            <option value="1班">1班</option>
            <option value="2班">2班</option>
            <option value="3班">3班</option>
            <option value="4班">4班</option>
            <option value="5班">5班</option>
            <option value="6班">6班</option>
            <option value="7班">7班</option>
            <option value="8班">8班</option>
            <option value="9班">9班</option>
        </select>
        <label for="name">姓名：（非正式比赛时不要使用真名注册）</label>
        <input type="text" id="name" name="name">
        <label for="username">用户名：</label>
        <input type="text" id="username" name="username">
        <label for="password">密码：</label>
        <input type="password" id="password" name="password">
        <input type="submit" value="注册">
     <center><?php
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
    }
    ?></center>
    <div class="back-to-login">
        <a href="index.php">已有账号？返回登录</a>
    </div>
    </form>
</body>

</html>