

<?php
function getDatabaseConnection() {
    $servername = "sql210.infinityfree.com";
    $username = "if0_38529372"; // 替换为你的数据库用户名
    $password = "twnhnciFNP"; // 替换为你的数据库密码
    $dbname = "if0_38529372_24"; // 替换为你的数据库名

    // 创建连接
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 检查连接
    if ($conn->connect_error) {
        die("数据库连接失败: ". $conn->connect_error);
    }

    return $conn;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("数据库连接失败: " . $e->getMessage());
    }

}
?>