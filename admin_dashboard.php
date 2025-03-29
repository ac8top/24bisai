<?php
session_start();
// 设置时区为北京时间
date_default_timezone_set('PRC');

if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <style>
        /* 基础重置 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        /* 全局样式 */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            line-height: 1.6;
        }

        /* 主容器 */
        .dashboard-container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem;
            width: 100%;
            max-width: 1200px;
            transition: transform 0.3s ease;
        }

        /* 标题 */
        h1 {
            color: #007BFF;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        /* 用户信息 */
        .user-info {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-radius: 15px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .user-info p {
            font-size: 1.1rem;
            margin: 0.5rem 0;
            color: #333;
        }

        /* 功能按钮 */
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            justify-content: center;
            align-items: center;
        }

        .action-btn {
            background-color: #007BFF;
            color: white;
            padding: 1.2rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .logout-btn {
            background-color: #dc3545;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .dashboard-container {
                padding: 1.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                margin: 1rem;
                border-radius: 15px;
            }

            .user-info {
                grid-template-columns: 1fr;
                padding: 1.2rem;
            }

            .action-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .dashboard-container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }
        }

        /* 动画增强 */
        @media (hover: hover) {
            .action-btn:hover {
                transform: translateY(-2px);
            }
        }

        /* 暗黑模式支持 */
        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%);
            }

            .dashboard-container {
                background-color: #333;
                color: white;
            }

            .user-info {
                background-color: #444;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>欢迎，管理员！</h1>
        
        <div class="user-info">
            <p>用户ID: <?= htmlspecialchars($_SESSION['admin_id']) ?></p>
            <p>登录时间: <?= date('Y年m月d日 H:i:s', $_SESSION['login_time'] ?? time()) ?></p>
        </div>

        <div class="action-buttons">
            <a href="admin_add_question.php" class="action-btn">
                进入管理页面
            </a>
            <a href="admin_logout.php" class="action-btn logout-btn">
                退出登录
            </a>
        </div>
    </div>
</body>
</html>