<?php
require 'init_db.php';

function showData($type, $params = []) {
    $db = new SQLite3(DB_FILE);
    
    $sql = 'SELECT data FROM api_data WHERE type = :type';
    $queryParams = [':type' => $type];
    
    if (!empty($params['query'])) {
        $sql .= ' AND search_query = :query';
        $queryParams[':query'] = $params['query'];
    }
    
    if (!empty($params['category'])) {
        $sql .= ' AND category = :category';
        $queryParams[':category'] = $params['category'];
    }
    
    $sql .= ' ORDER BY created_at DESC LIMIT :limit';
    $queryParams[':limit'] = $params['limit'] ?? 10;
    
    $stmt = $db->prepare($sql);
    foreach ($queryParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $result = $stmt->execute();
    
    echo "Последние записи ($type):\n";
    if (!empty($params['query'])) echo "Поиск: '{$params['query']}'\n";
    if (!empty($params['category'])) echo "Категория: '{$params['category']}'\n";
    echo str_repeat('-', 50) . "\n";
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $item = json_decode($row['data'], true);
        echo "ID: {$item['id']}\n";
        echo "Title: {$item['title']}\n";
        if (isset($item['category'])) echo "Category: {$item['category']}\n";
        if (isset($item['price'])) echo "Price: \${$item['price']}\n";
        if (isset($item['brand'])) echo "Brand: {$item['brand']}\n";
        echo "\n";
    }
}

// Обработка аргументов
$type = $argv[1] ?? 'products';
$params = [
    'query' => $argv[2] ?? '',
    'category' => $argv[3] ?? '',
    'limit' => $argv[4] ?? 10
];

showData($type, $params);
?>