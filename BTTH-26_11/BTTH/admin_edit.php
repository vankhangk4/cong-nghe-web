<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID không hợp lệ");
}

// Lấy dữ liệu hiện tại
$stmt = $conn->prepare("SELECT * FROM flowers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$current = $stmt->get_result()->fetch_assoc();
if (!$current) {
    die("Không tìm thấy bản ghi");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $image_path = $current['image_path']; // mặc định giữ ảnh cũ

    if ($name === '') {
        $errors[] = "Tên hoa không được để trống.";
    }

    // Nếu chọn ảnh mới
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename($_FILES['image']['name']);
        $targetPath = $uploadDir . time() . '_' . $fileName;
        $tmpPath = $_FILES['image']['tmp_name'];

        $allowedExt = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $errors[] = "Chỉ cho phép file ảnh (jpg, jpeg, png, gif, webp).";
        } else {
            if (!move_uploaded_file($tmpPath, $targetPath)) {
                $errors[] = "Upload ảnh thất bại.";
            } else {
                // Xóa ảnh cũ nếu muốn
                if (!empty($current['image_path']) && file_exists($current['image_path'])) {
                    @unlink($current['image_path']);
                }
                $image_path = $targetPath;
            }
        }
    }

    if (empty($errors)) {
        $stmt2 = $conn->prepare("UPDATE flowers SET name = ?, description = ?, image_path = ? WHERE id = ?");
        $stmt2->bind_param("sssi", $name, $desc, $image_path, $id);
        if ($stmt2->execute()) {
            header("Location: admin_list.php");
            exit;
        } else {
            $errors[] = "Lỗi khi cập nhật DB: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa hoa</title>
</head>
<body>

<h1>Sửa hoa: <?php echo htmlspecialchars($current['name']); ?></h1>
<p><a href="admin_list.php">← Quay lại danh sách</a></p>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <p>
        Tên hoa:<br>
        <input type="text" name="name" style="width: 300px;"
               value="<?php echo htmlspecialchars($current['name']); ?>" required>
    </p>
    <p>
        Mô tả:<br>
        <textarea name="description" rows="4" style="width: 400px;"><?php 
            echo htmlspecialchars($current['description']); ?></textarea>
    </p>
    <p>
        Ảnh hiện tại:<br>
        <?php if (!empty($current['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($current['image_path']); ?>" 
                 alt="" style="width:150px; height:110px; object-fit:cover;">
        <?php else: ?>
            (Chưa có ảnh)
        <?php endif; ?>
    </p>
    <p>
        Chọn ảnh mới (nếu muốn thay):<br>
        <input type="file" name="image" accept="image/*">
    </p>
    <p>
        <button type="submit">Cập nhật</button>
    </p>
</form>

</body>
</html>
