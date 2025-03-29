<?php
session_start();
require_once 'config.php';

// 防止重复提交
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_login.php');
    exit;
}

// 输入验证
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

// 验证输入完整性
if (empty($username) || empty($password)) {
    $_SESSION['error'] = '请填写完整的登录信息';
    header('Location: admin_login.php');
    exit;
}

// 防止暴力破解（尝试次数限制）
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SESSION['login_attempts'] >= 5) {
    $_SESSION['error'] = '登录尝试过多，请15分钟后再试';
    header('Location: admin_login.php');
    exit;
}

// 数据库查询
try {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // 登录成功
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['login_attempts'] = 0; // 重置尝试次数
            header('Location: admin_dashboard.php');
            exit;
        }
    }

    // 登录失败处理
    $_SESSION['login_attempts']++;
    $_SESSION['error'] = '用户名或密码错误';
    header('Location: admin_login.php');
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = '系统错误，请联系管理员';
    header('Location: admin_login.php');
    exit;
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}

$_SESSION['login_time'] = time();

$stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
?>