<?php
header('Content-Type: application/json; charset=utf-8');

// 限制仅 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => '不支持的请求方法']);
    exit;
}

// 获取并净化输入
$name   = isset($_POST['name']) ? trim($_POST['name']) : '';
$email  = isset($_POST['email']) ? trim($_POST['email']) : '';
$msg    = isset($_POST['message']) ? trim($_POST['message']) : '';

// 字段验证
if (empty($name) || empty($email) || empty($msg)) {
    echo json_encode(['success' => false, 'message' => '请完整填写姓名、邮箱和留言内容']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => '请填写有效的电子邮箱地址']);
    exit;
}
if (strlen($name) > 100 || strlen($msg) > 5000) {
    echo json_encode(['success' => false, 'message' => '内容超出长度限制（姓名≤100，留言≤5000）']);
    exit;
}

// 基础防注入（HTML转义）
$name_safe  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email_safe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$msg_safe   = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');

// 邮件配置 =================================
$to      = '3871323450@QQ.COM';   // 替换为您的真实接收邮箱
$subject = "【原心联创】来自 {$name_safe} 的合作咨询";
$body    = "姓名：{$name_safe}\n邮箱：{$email_safe}\n\n留言内容：\n{$msg_safe}\n\n提交时间：" . date('Y-m-d H:i:s');
$headers = "From: noreply@yuanxin520.top\r\n";
$headers.= "Reply-To: {$email_safe}\r\n";
$headers.= "Content-Type: text/plain; charset=UTF-8\r\n";

$mail_sent = @mail($to, $subject, $body, $headers);

if ($mail_sent) {
    // 成功则记录到日志（可选）
    file_put_contents('contact_success.log', date('Y-m-d H:i:s') . " | {$name_safe} | {$email_safe}\n", FILE_APPEND);
    echo json_encode(['success' => true, 'message' => '留言已发送！原心联创团队会尽快与您联系。']);
} else {
    // 邮件失败时保存到本地备用文件
    $fallback = date('Y-m-d H:i:s') . " | {$name_safe} | {$email_safe} | " . str_replace(["\r","\n"], ' ', $msg_safe) . PHP_EOL;
    file_put_contents('contact_fallback.log', $fallback, FILE_APPEND);
    echo json_encode(['success' => false, 'message' => '邮件发送暂时失败，但您的留言已被记录，我们会手动处理。感谢理解！']);
}
?>