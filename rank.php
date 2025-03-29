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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>实时排名</title>
    <style>
        /* 基础样式 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 2rem;
        }

        .dashboard-container {
            background-color: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin: 2rem;
            width: 100%;
            max-width: 1200px;
        }

        h1 {
            color: #007BFF;
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        /* 表格样式 */
        .rank-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
            overflow-x: auto; /* 移动端允许横向滚动 */
        }

        .rank-table th,
        .rank-table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            word-wrap: break-word; /* 防止长文本溢出 */
        }

        .rank-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
        }

        .rank-table td:first-child {
            color: #007BFF;
            font-weight: 700;
        }

        /* 按钮样式 */
        .action-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .action-btn {
            background-color: #007BFF;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* 响应式设计 */
        @media (max-width: 1024px) {
            .rank-table th,
            .rank-table td {
                padding: 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .rank-table th,
            .rank-table td {
                padding: 0.8rem;
                font-size: 0.85rem;
            }

            .action-btn {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .rank-table {
                margin-top: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>实时排名</h1>
        
        <table class="rank-table">
            <thead>
                <tr>
                    <th>排名</th>
                    <th>学校</th>
                    <th>年级</th>
                    <th>班级</th>
                    <th>姓名</th>
                    <th>正确数量</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankings as $ranking): ?>
                <tr>
                    <td><?= $ranking['rank'] ?></td>
                    <td><?= $ranking['school'] ?></td>
                    <td><?= $ranking['grade'] ?></td>
                    <td><?= $ranking['class'] ?></td>
                    <td><?= $ranking['name'] ?></td>
                    <td><?= $ranking['correct_count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="action-buttons">
            <a href="game.php" class="action-btn">
                <i class="fas fa-play"></i>
                返回答题页面
            </a>
            <button onclick="window.location.reload()" class="action-btn">
                <i class="fas fa-sync-alt"></i>
                刷新排名
            </button>
        </div>
    </div>
</body>
</html>