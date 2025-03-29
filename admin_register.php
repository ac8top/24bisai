<?php
session_start();
require_once 'config.php'; // 包含数据库连接配置文件

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $conn = getDatabaseConnection();

    // 检查用户名是否已存在
    $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // 用户名已存在，提示注册失败
        $_SESSION['registration_error'] = "该管理员已注册";
    } else {
        // 用户名不存在，进行注册操作
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $_SESSION['registration_success'] = "管理员账户注册成功！<a href='admin_login.php'>点击返回登录页面</a>";
        } else {
            $_SESSION['registration_error'] = "注册失败: " . $stmt->error;
        }
        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();

    header("Location: admin_register.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员注册</title>
    <style>
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

        label {
            font-weight: 600;
            color: #333;
        }

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

        .message {
            text-align: center;
            margin-bottom: 10px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        /* 登录按钮样式 */
        .login-link {
            text-align: center;
            margin-top: 20px;
        }

        .login-btn {
            background-color: #6c757d;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .login-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    
    <form action="admin_register.php" method="post">
    <?php
    if (isset($_SESSION['registration_success'])) {
        echo '<p class="message success">' . $_SESSION['registration_success'] . '</p>';
        unset($_SESSION['registration_success']);
    }
    if (isset($_SESSION['registration_error'])) {
        echo '<p class="message error">' . $_SESSION['registration_error'] . '</p>';
        unset($_SESSION['registration_error']);
    }
    ?>
        <label for="username">用户名：</label>
        <input type="text" id="username" name="username" required>
        <label for="password">密码：</label>
        <input type="password" id="password" name="password" required>
        <input type="submit" value="注册">

        <div class="login-link">
            <input type="button" value="已有账号？立即登录" 
                   onclick="window.location.href='admin_login.php'"
                   class="login-btn">
        </div>
    </form>
</body>

</html>