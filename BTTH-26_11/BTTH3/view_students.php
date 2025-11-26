<?php
require_once "config.php";

// Lấy file_id từ GET
if (!isset($_GET['file_id']) || $_GET['file_id'] === '') {
    die("❌ Thiếu tham số file_id.<br><a href='index.php'>Quay lại</a>");
}

$fileId = (int)$_GET['file_id'];

// Lấy thông tin file
$fileStmt = $pdo->prepare("SELECT * FROM uploaded_files WHERE id = :id");
$fileStmt->execute([':id' => $fileId]);
$file = $fileStmt->fetch();

if (!$file) {
    die("❌ Không tìm thấy file với ID này.<br><a href='index.php'>Quay lại</a>");
}

// Lấy danh sách sinh viên thuộc file này
$stuStmt = $pdo->prepare("
    SELECT * FROM students 
    WHERE uploaded_file_id = :file_id
    ORDER BY id ASC
");
$stuStmt->execute([':file_id' => $fileId]);
$students = $stuStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách sinh viên - <?= htmlspecialchars($file['filename']) ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 6px; font-size: 14px; }
        th { background: #ddd; }
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>

<h1>📋 Danh sách sinh viên từ file: <?= htmlspecialchars($file['filename']) ?></h1>
<p>
    <strong>Số dòng import:</strong> <?= (int)$file['total_rows'] ?><br>
    <strong>Thời gian upload:</strong> <?= htmlspecialchars($file['uploaded_at']) ?>
</p>

<a href="index.php">⬅ Quay lại trang upload</a>

<?php if (count($students) === 0): ?>
    <p>Không có sinh viên nào được import từ file này.</p>
<?php else: ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Password</th>
            <th>Last name</th>
            <th>First name</th>
            <th>Lớp</th>
            <th>Email</th>
            <th>Course1</th>
        </tr>

        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['username']) ?></td>
                <td><?= htmlspecialchars($s['password']) ?></td>
                <td><?= htmlspecialchars($s['lastname']) ?></td>
                <td><?= htmlspecialchars($s['firstname']) ?></td>
                <td><?= htmlspecialchars($s['city']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td><?= htmlspecialchars($s['course1']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
