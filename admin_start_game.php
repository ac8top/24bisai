<?php
// 设置时区为中国时区
date_default_timezone_set('Asia/Shanghai');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
}

$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 检查 POST 数据中是否存在 start_time 和 end_time 键
    $start_time_str = isset($_POST['start_time']) ? $_POST['start_time'] : '';
    $end_time_str = isset($_POST['end_time']) ? $_POST['end_time'] : '';

    // 将时间字符串转换为时间戳
    $start_time = strtotime($start_time_str);
    $end_time = strtotime($end_time_str);

    if ($start_time && $end_time && $end_time > $start_time) {
        // 检查 game_settings 表中是否有记录
        $checkSql = "SELECT id FROM game_settings LIMIT 1";
        $checkResult = $conn->query($checkSql);
        if ($checkResult->num_rows > 0) {
            // 如果有记录，更新开始和结束时间
            $sql = "UPDATE game_settings SET start_time = '$start_time', end_time = '$end_time'";
        } else {
            // 如果没有记录，插入一条新记录
            $sql = "INSERT INTO game_settings (start_time, end_time) VALUES ('$start_time', '$end_time')";
        }
        if ($conn->query($sql) === TRUE) {
            // 重置比赛记录
            $resetSql = "DELETE FROM game_records";
            if ($conn->query($resetSql) === FALSE) {
                echo '<script>alert("重置比赛记录失败: '. $conn->error. '");</script>';
                exit;
            }
            // 设置会话中的开始和结束时间
            $_SESSION['start_time'] = $start_time;
            $_SESSION['end_time'] = $end_time;
            echo '<script>alert("比赛时间设置成功"); window.location.href = "admin_start_game.php";</script>';
        } else {
            echo '<script>alert("设置比赛时间失败: '. $conn->error. '");</script>';
        }
    } else {
        echo '<script>alert("输入的时间格式不正确或结束时间早于开始时间，请重新输入。");</script>';
    }
}

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

$conn->close();

// 计算剩余时间
$remaining_time = 0;
$current_time = time();
if ($start_time && $end_time && $current_time >= $start_time && $current_time < $end_time) {
    $remaining_time = $end_time - $current_time;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>开始比赛</title>
    <style>
        /* 设置根元素字体大小，方便使用 rem 单位 */
        html {
            font-size: 16px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        nav {
            background-color: #333;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1;
            display: flex;
            justify-content: center;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
            flex-wrap: wrap;
        }

        nav a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 0.875rem 1rem;
            text-decoration: none;
            transition: background-color 0.3s ease;
            flex: 1 0 auto;
        }

        nav a:hover {
            background-color: #ddd;
            color: black;
        }

        .container {
            background-color: #fff;
            padding: 1.875rem;
            border-radius: 0.625rem;
            box-shadow: 0 0 1.25rem rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 37.5rem;
            margin-top: 3.125rem;
        }

        h1 {
            color: #333;
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.625rem;
            font-weight: 600;
            color: #555;
        }

        input[type="datetime-local"] {
            width: 100%;
            padding: 0.625rem;
            margin-bottom: 1.25rem;
            border: 1px solid #ccc;
            border-radius: 0.3125rem;
            font-size: 1rem;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 0.75rem 1.5625rem;
            border: none;
            border-radius: 0.3125rem;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        #countdown-section {
            margin-top: 1.875rem;
            padding: 1.25rem;
            background-color: #f9f9f9;
            border-radius: 0.3125rem;
        }

        #admin-countdown {
            font-size: 1.5rem;
            font-weight: 600;
            color: #007BFF;
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
                margin-top: 4.375rem;
            }
        }
    </style>
    <script>
        function startGame() {
            if (confirm('确定开始比赛吗？')) {
                document.getElementById('start-form').submit();
            }
        }

        window.onload = function () {
            var remainingTime = <?php echo $remaining_time; ?>;
            if (remainingTime > 0) {
                var countdownInterval = setInterval(function () {
                    if (remainingTime <= 0) {
                        clearInterval(countdownInterval);
                        alert('比赛结束');
                    } else {
                        remainingTime--;
                        document.getElementById('admin-countdown').innerHTML = remainingTime;
                    }
                }, 1000);
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
        <h1>设置比赛时间</h1>
        <form id="start-form" action="admin_start_game.php" method="post">
            <label for="start_time">比赛开始时间：</label>
            <input type="datetime-local" id="start_time" name="start_time"
                value="<?php echo $start_time ? date('Y-m-d\TH:i', $start_time) : ''; ?>">
            <label for="end_time">比赛结束时间：</label>
            <input type="datetime-local" id="end_time" name="end_time"
                value="<?php echo $end_time ? date('Y-m-d\TH:i', $end_time) : ''; ?>">
            <button type="button" onclick="startGame()">开始比赛</button>
        </form>
        <?php if ($remaining_time > 0): ?>
            <div id="countdown-section"><p>注意：正式开赛前，请先清除用户数据！！！</p>
                <p>剩余时间：<span id="admin-countdown"><?php echo $remaining_time; ?></span> 秒</p>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>