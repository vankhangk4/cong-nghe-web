<?php
require 'config.php';

$sql = "SELECT * FROM flowers ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản trị hoa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background: #f3f3f3; }
        img.thumb { width: 80px; height: 60px; object-fit: cover; }
        a { text-decoration: none; color: blue; }
    </style>
</head>
<body>

<h1>Quản trị danh sách hoa</h1>
<p>
    <a href="index.php">👤 Xem dạng khách</a> | 
    <a href="admin_add.php">➕ Thêm hoa mới</a>
</p>

<table>
    <tr>
        <th>ID</th>
        <th>Ảnh</th>
        <th>Tên hoa</th>
        <th>Mô tả</th>
        <th>Hành động</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td>
                <?php if (!empty($row['image_path'])): ?>
                    <img class="thumb" src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
            <td>
                <a href="admin_edit.php?id=<?php echo $row['id']; ?>">Sửa</a> | 
                <a href="admin_delete.php?id=<?php echo $row['id']; ?>"
                   onclick="return confirm('Xóa hoa này?');">Xóa</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
