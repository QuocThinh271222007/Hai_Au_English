<?php
require 'backend/php/db.php';

echo "=== Test Site Content ===\n";
$stmt = $pdo->query('SELECT page, section, content_key, content_value FROM site_content LIMIT 15');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "!!! DATABASE IS EMPTY - No content found !!!\n";
} else {
    echo "Found " . count($rows) . " rows:\n\n";
    foreach ($rows as $row) {
        echo "Page: {$row['page']}, Section: {$row['section']}, Key: {$row['content_key']}\n";
        echo "Value: " . substr($row['content_value'], 0, 50) . "...\n\n";
    }
}

echo "\n=== Total Count ===\n";
$count = $pdo->query('SELECT COUNT(*) FROM site_content')->fetchColumn();
echo "Total content items: $count\n";
