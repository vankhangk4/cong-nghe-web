<?php
require 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $image_path = null;

    if ($name === '') {
        $errors[] = "Tên hoa không được để trống.";
    }

if (!empty($_FILES['image']['name'])) {

    $realUploadDir = "/home/khang/Documents/congngheweb/php/thuc_hanh/BTTH-26_11/BTTH/images/";

    $dbImagePath = "images/";

    $fileName = time() . "_" . basename($_FILES['image']['name']);

    $targetRealPath = $realUploadDir . $fileName;

    $image_path = $dbImagePath . $fileName;

    $tmpPath = $_FILES['image']['tmp_name'];

    $allowedExt = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        $errors[] = "Chỉ cho phép file ảnh (jpg, jpeg, png, gif, webp).";
    } else {
        if (!move_uploaded_file($tmpPath, $targetRealPath)) {
            $errors[] = "Upload ảnh thất bại.";
        }
    }
}


    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO flowers (name, description, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $desc, $image_path);
        if ($stmt->execute()) {
            header("Location: admin_list.php");
            exit;
        } else {
            $errors[] = "Lỗi khi lưu vào DB: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm hoa mới</title>
</head>
<body>
<h1>Thêm hoa mới</h1>
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
        <input type="text" name="name" style="width: 300px;" required>
    </p>
    <p>
        Mô tả:<br>
        <textarea name="description" rows="4" style="width: 400px;"></textarea>
    </p>
    <p>
        Ảnh (upload từ máy):<br>
        <input type="file" name="image" accept="image/*">
    </p>
    <p>
        <button type="submit">Lưu</button>
    </p>
</form>

</body>
</html>
