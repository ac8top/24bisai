<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>后台管理主页</title>
</head>
<body>
    <h1>后台管理主页</h1>
    <nav>
        <a href="add_question.php">添加题目</a>
        <a href="start_game.php">开始比赛</a>
        <a href="rank.php">实时排名</a>
    </nav>
</body>
</html>