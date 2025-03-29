<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理登录</title>
    <style>
        /* 重置默认样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        form {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            padding: 1rem;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 1.5rem;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .register-link a {
            color: #007BFF;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* 响应式设计 */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            form {
                padding: 1.5rem;
            }

            input[type="text"],
            input[type="password"] {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            input[type="submit"] {
                padding: 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            form {
                border-radius: 10px;
                padding: 1rem;
            }

            label {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>
    <form action="admin_login_process.php" method="post">
        <?php if (isset($_SESSION['error'])): ?>
            <div style="color: #dc3545; margin-bottom: 1rem; font-size: 0.875rem;">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <label for="username">用户名：</label>
        <input type="text" id="username" name="username" required>

        <label for="password">密码：</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="登录">
        
        <div class="register-link">
            没有管理员权限？联系系统管理员
        </div>
    </form>
</body>
</html>