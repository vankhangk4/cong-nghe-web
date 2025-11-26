<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID không hợp lệ");
}

// Lấy đường dẫn ảnh để xóa file
$stmt = $conn->prepare("SELECT image_path FROM flowers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if ($row) {
    // Xóa ảnh trên ổ đĩa nếu có
    if (!empty($row['image_path']) && file_exists($row['image_path'])) {
        @unlink($row['image_path']);
    }

    // Xóa bản ghi
    $stmt2 = $conn->prepare("DELETE FROM flowers WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
}

header("Location: admin_list.php");
exit;
