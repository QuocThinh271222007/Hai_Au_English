<?php
require_once 'php/db.php';
$pdo = getDBConnection();

// Kiểm tra bảng admin_notifications
echo "=== TABLE STRUCTURE ===\n";
try {
    $result = $pdo->query('DESCRIBE admin_notifications');
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . ' - ' . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Table doesn't exist: " . $e->getMessage() . "\n";
}

echo "\n=== DATA ===\n";
try {
    $data = $pdo->query('SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5');
    $count = 0;
    while ($row = $data->fetch(PDO::FETCH_ASSOC)) {
        $count++;
        echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
    }
    if ($count == 0) echo "(No data)\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST INSERT ===\n";
try {
    $stmt = $pdo->prepare('INSERT INTO admin_notifications (type, title, message) VALUES (?, ?, ?)');
    $stmt->execute(['system', 'Test notification', 'This is a test notification']);
    echo "Success! ID: " . $pdo->lastInsertId() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
