<?php
require 'config.php';

$sql = "SELECT * FROM flowers ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>14 loài hoa xuân hè</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .flower-list { display: flex; flex-wrap: wrap; gap: 20px; }
        .flower-item {
            width: 260px;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
        }
        .flower-item img {
            max-width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 4px;
        }
        .flower-item h3 { margin: 8px 0 5px; font-size: 18px; }
        .flower-item p { margin: 0; font-size: 14px; text-align: justify; }
    </style>
</head>
<body>

<h1>14 loài hoa tuyệt đẹp dịp xuân hè</h1>
<p>Dữ liệu demo lấy từ DB, ảnh lưu trong thư mục <code>images/</code>.</p>

<div class="flower-list">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="flower-item">
            <?php if (!empty($row['image_path'])): ?>
                <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($row['name']); ?>">
            <?php endif; ?>
            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
        </div>
    <?php endwhile; ?>
</div>

<p><a href="admin_list.php">🛠 Vào trang quản trị</a></p>

</body>
</html>
