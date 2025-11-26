<?php
// Dùng chung kết nối DB
require_once "config.php";

// Kiểm tra file upload
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    die("❌ Có lỗi khi upload file CSV.");
}

$fileTmpPath = $_FILES['csv_file']['tmp_name'];
$fileName    = $_FILES['csv_file']['name'];

if (($handle = fopen($fileTmpPath, 'r')) === false) {
    die("❌ Không thể đọc file CSV.");
}

$line = 0;
$inserted = 0;

$sql = "INSERT INTO students 
        (username, password, lastname, firstname, city, email, course1)
        VALUES (:username, :password, :lastname, :firstname, :city, :email, :course1)";
        
$stmt = $pdo->prepare($sql);

while (($data = fgetcsv($handle, 10000, ",")) !== false) {
    $line++;

    if ($line == 1) { // bỏ header
        $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', $data[0]);
        continue;
    }

    if (count($data) < 7) continue;

    $stmt->execute([
        ':username'  => trim($data[0]),
        ':password'  => trim($data[1]),
        ':lastname'  => trim($data[2]),
        ':firstname' => trim($data[3]),
        ':city'      => trim($data[4]),
        ':email'     => trim($data[5]),
        ':course1'   => trim($data[6]),
    ]);

    $inserted++;
}

fclose($handle);

// Lấy dữ liệu để hiển thị
$students = $pdo->query("SELECT * FROM students ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả import CSV sinh viên</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #eee; }
    </style>
</head>
<body>

<h1>✔ Đã import <?php echo $inserted; ?> sinh viên từ file: <?php echo htmlspecialchars($fileName); ?></h1>
<a href="index.php">↩ Quay lại</a>

<hr>

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

</body>
</html>
