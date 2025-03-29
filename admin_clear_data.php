<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
}

$conn = getDatabaseConnection();

// 获取最新的开始和结束时间
$sql = "SELECT start_time, end_time FROM game_settings";
$result = $conn->query($sql);
if ($result === false) {
    die("查询失败: ". $conn->error);
}
$start_time = null;
$end_time = null;
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $start_time = $row['start_time'];
    $end_time = $row['end_time'];
}

// 计算剩余时间
$remaining_time = 0;
$current_time = time();
if ($start_time && $end_time && $current_time >= $start_time && $current_time < $end_time) {
    $remaining_time = $end_time - $current_time;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($remaining_time > 0) {
        echo "比赛还未结束，不能清除数据，请在比赛结束后再尝试。";
    } else {
        $clear_users = isset($_POST['clear_users']);
        $clear_answers = isset($_POST['clear_answers']);

        if ($clear_users) {
            $sql_users = "DELETE FROM users";
            if ($conn->query($sql_users) === TRUE) {
                echo "用户数据已清除<br>";
            } else {
                echo "清除用户数据失败: ". $conn->error. "<br>";
            }
        }

        if ($clear_answers) {
            $sql_answers = "DELETE FROM answers";
            if ($conn->query($sql_answers) === TRUE) {
                $sql_user_answers = "DELETE FROM user_answers";
                if ($conn->query($sql_user_answers) === TRUE) {
                    echo "用户答题记录和正确题号记录已清除<br>";
                } else {
                    echo "清除用户答题正确题号记录失败: ". $conn->error. "<br>";
                }
                echo "用户答题记录已清除<br>";
            } else {
                echo "清除用户答题记录失败: ". $conn->error. "<br>";
            }
        }

        if (!$clear_users &&!$clear_answers) {
            echo "请选择要清除的数据<br>";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>清除数据</title>
    <style>
        /* 设置根元素字体大小，方便使用 rem 单位 */
        html {
            font-size: 16px;
        }

        /* 全局样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        /* 导航栏样式 */
        nav {
            background-color: #333;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1;
            display: flex;
            justify-content: center;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1); /* 2px 4px 转换为 rem */
            flex-wrap: wrap;
        }

        nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 0.875rem 1rem; /* 14px 16px 转换为 rem */
            text-decoration: none;
            transition: background-color 0.3s ease;
            flex: 1 0 auto;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }

        /* 内容容器样式 */
        .container {
            max-width: 75rem; /* 1200px 转换为 rem */
            margin: 5rem auto 1.25rem; /* 80px 20px 转换为 rem */
            padding: 1.875rem; /* 30px 转换为 rem */
            background-color: #fff;
            border-radius: 0.625rem; /* 10px 转换为 rem */
            box-shadow: 0 0 1.25rem rgba(0, 0, 0, 0.1); /* 20px 转换为 rem */
            text-align: center;
            box-sizing: border-box;
        }

        /* 标题样式 */
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.25rem; /* 20px 转换为 rem */
        }

        /* 表单样式 */
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            display: block;
            margin-bottom: 0.625rem; /* 10px 转换为 rem */
            font-weight: 600;
            color: #555;
        }

        input[type="checkbox"] {
            margin-right: 0.3125rem; /* 5px 转换为 rem */
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 0.75rem 1.5625rem; /* 12px 25px 转换为 rem */
            border: none;
            border-radius: 0.3125rem; /* 5px 转换为 rem */
            cursor: pointer;
            font-size: 1rem; /* 16px 转换为 rem */
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* 媒体查询，当屏幕宽度小于 768px 时的样式调整 */
        @media (max-width: 768px) {
            html {
                font-size: 14px;
            }

            nav a {
                width: 50%;
            }

            .container {
                margin: 7rem auto 1.25rem;
            }

            .container {
                margin-top: 17rem;
            }
        }
    </style>
    <script>
        window.onload = function () {
            var remainingTime = <?php echo $remaining_time; ?>;
            var clearButton = document.querySelector('button[type="submit"]');

            if (remainingTime > 0) {
                clearButton.disabled = true;
                clearButton.style.backgroundColor = '#ccc';
                clearButton.style.cursor = 'not-allowed';
                alert('比赛还未结束，不能清除数据，请在比赛结束后再尝试。');
            }
        }
    </script>
</head>

<body>
    <nav>
    <a href="admin_add_announcement.php">添加比赛名称</a>
        <a href="admin_add_question.php">添加题目</a>
        <a href="admin_start_game.php">开始比赛</a>
        <a href="admin_rank.php">实时排名</a>
        <a href="admin_clear_data.php">清除数据</a>
        <a href="admin_logout.php">退出登录</a>
    </nav>
    <div class="container">
        <h1>清除数据</h1>
        <form action="admin_clear_data.php" method="post">
            <label for="clear_users">
                <input type="checkbox" id="clear_users" name="clear_users">
                清除注册用户数据（会删除所有注册用户）重新开始比赛时使用
            </label><br>
            <label for="clear_answers">
                <input type="checkbox" id="clear_answers" name="clear_answers">
                清除用户答题记录
            </label><br>
            <button type="submit" onclick="return confirm('确认要清除选中的数据吗？')">清除数据</button>
        </form>
    </div>
</body>

</html>