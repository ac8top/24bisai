<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
}

$conn = getDatabaseConnection();

// 查询比赛名称
$sql_game_name = "SELECT * FROM game_name LIMIT 1";
$stmt_game_name = $conn->prepare($sql_game_name);
$stmt_game_name->execute();
$result_game_name = $stmt_game_name->get_result();
$gameName = "";
if ($result_game_name->num_rows > 0) {
    $row_game_name = $result_game_name->fetch_assoc();
    $gameName = $row_game_name['name'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['clear'])) {
        $deleteSql = "DELETE FROM game_name";
        $stmt_delete = $conn->prepare($deleteSql);
        if ($stmt_delete->execute()) {
            $gameName = "";
        } else {
            echo "清空比赛名称失败: ". $stmt_delete->error;
        }
    } elseif (isset($_POST['save'])) {
        $newGameName = $_POST['game_name'];
        if ($result_game_name->num_rows > 0) {
            $updateSql = "UPDATE game_name SET name = ?";
            $stmt_update = $conn->prepare($updateSql);
            $stmt_update->bind_param("s", $newGameName);
            if ($stmt_update->execute()) {
                $gameName = $newGameName;
            } else {
                echo "更新比赛名称失败: ". $stmt_update->error;
            }
        } else {
            $insertSql = "INSERT INTO game_name (name) VALUES (?)";
            $stmt_insert = $conn->prepare($insertSql);
            $stmt_insert->bind_param("s", $newGameName);
            if ($stmt_insert->execute()) {
                $gameName = $newGameName;
            } else {
                echo "插入比赛名称失败: ". $stmt_insert->error;
            }
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
    <title>设置比赛名称 - <?php echo htmlspecialchars($gameName); ?></title>
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


        /* 内容容器样式 */
        .container {
            max-width: 75rem;
            margin: 5rem auto 1.25rem;
            padding: 1.25rem;
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0 0.625rem rgba(0, 0, 0, 0.1);
        }

        /* 标题样式 */
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1.25rem;
        }

        /* 表单样式 */
        form {
            margin-bottom: 1.25rem;
        }

        input[type="text"],
        input[type="submit"] {
            padding: 0.625rem 0.9375rem;
            border: 1px solid #ccc;
            border-radius: 0.3125rem;
            font-size: 1rem;
            margin: 0.3125rem;
        }

        input[type="text"] {
            width: calc(100% - 1.25rem);
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* 媒体查询，当屏幕宽度小于 768px 时的样式调整 */
        @media (max-width: 768px) {
            html {
                font-size: 14px;
            }

            nav {
                flex-wrap: wrap;
            }

            nav a {
                width: 50%;
            }

            input[type="text"] {
                width: calc(100% - 0.625rem);
            }

            .container {
                margin-top: 17rem;
            }
        }
    </style>
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
        <h1>设置比赛名称 - <?php echo htmlspecialchars($gameName); ?></h1>
        <form action="admin_add_announcement.php" method="post">
            <input type="text" id="game_name" name="game_name" value="<?php echo htmlspecialchars($gameName); ?>" placeholder="请输入比赛名称">
            <input type="submit" name="clear" value="清空">
            <input type="submit" name="save" value="保存">
        </form>
        <p>可供前台直接调用比赛名称的 HTML 代码：</p>
       <!--<xmp> <center><strong> <p style="font-size: 40px; font-family: Arial, sans-serif;">  <?php echo htmlspecialchars($gameName); ?></p></strong></center></xmp>-->
    </div>
</body>

</html>    