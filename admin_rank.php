<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>实时排名</title>
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

        /* 表格样式 */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 0.5rem;
            text-align: center;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* 按钮样式 */
        button {
            padding: 0.625rem 0.9375rem;
            border: none;
            border-radius: 0.3125rem;
            font-size: 1rem;
            margin: 0.3125rem;
            background-color: #007BFF;
            color: white;
            cursor: pointer;
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

            nav {
                flex-wrap: wrap;
            }

            nav a {
                width: 50%;
            }

            table {
                display: block;
                overflow-x: auto;
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
        <h1><center>实时排名</center></h1>
        <table id="rankTable">
            <thead>
                <tr>
                    <th>排名序号</th>
                    <th>学校</th>
                    <th>年级</th>
                    <th>班级</th>
                    <th>姓名</th>
                    <th>正确数量</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 包含数据库连接配置文件
                require_once 'config.php';

                // 初始化数据库连接
                $conn = getDatabaseConnection();

                // 检查数据库连接是否成功
                if (!$conn) {
                    die("数据库连接失败: ". mysqli_connect_error());
                }

                // 编写 SQL 查询语句获取排名信息
                $sql = "SELECT 
                            u.id,
                            u.school,
                            u.grade,
                            u.class,
                            u.name,
                            a.correct_count
                        FROM 
                            users u
                        JOIN 
                            answers a ON u.id = a.user_id
                        ORDER BY 
                            a.correct_count DESC";

                $result = $conn->query($sql);

                if ($result === false) {
                    die("查询失败: ". $conn->error);
                }

                // 存储排名信息的数组
                $rankings = [];
                $rank = 1;
                while ($row = $result->fetch_assoc()) {
                    $row['rank'] = $rank++;
                    $rankings[] = $row;
                }

                // 关闭数据库连接
                $conn->close();

                foreach ($rankings as $ranking):
                ?>
                    <tr>
                        <td><?php echo $ranking['rank']; ?></td>
                        <td><?php echo $ranking['school']; ?></td>
                        <td><?php echo $ranking['grade']; ?></td>
                        <td><?php echo $ranking['class']; ?></td>
                        <td><?php echo $ranking['name']; ?></td>
                        <td><?php echo $ranking['correct_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button onclick="window.location.reload()">刷新</button>
        <button onclick="copyTable()">复制表格（请先创建一个xls表格，点击复制表格后，到xls表格黏贴）</button>
        <script>
            function copyTable() {
                const table = document.getElementById('rankTable');
                const range = document.createRange();
                range.selectNode(table);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                try {
                    document.execCommand('copy');
                    alert('表格已复制到剪贴板');
                } catch (err) {
                    alert('复制失败，请检查浏览器是否支持');
                }
            }
        </script>
    </div>
</body>

</html>