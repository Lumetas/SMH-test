<?php
require 'init_db.php';

function createItem($type, $fields, $saveToDb = false) {
    $url = "https://dummyjson.com/$type/add";
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($fields)
        ]
    ];
    
    $response = file_get_contents($url, false, stream_context_create($options));
    $newItem = json_decode($response, true);
    
    echo "Создана запись:\n";
    print_r($newItem);
    
    if ($saveToDb) {
        $db = new SQLite3(DB_FILE);
        $stmt = $db->prepare('INSERT INTO api_data (id, type, data) 
                             VALUES (:id, :type, :data)');
        
        $stmt->bindValue(':id', $newItem['id']);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':data', json_encode($newItem));
        
        if ($stmt->execute()) {
            echo "Сохранено в базу (ID: {$newItem['id']})\n";
        }
    }
}

// Парсинг аргументов
$args = array_slice($argv, 1);
$saveFlag = in_array('--save', $args);
$cleanArgs = array_values(array_diff($args, ['--save']));

$type = $cleanArgs[0] ?? 'products';
$fields = [
    'title' => $cleanArgs[1] ?? 'Новый товар',
    'price' => (float)($cleanArgs[2] ?? 100),
    'brand' => $cleanArgs[3] ?? 'No brand',
    'category' => $cleanArgs[4] ?? 'Other'
];

createItem($type, $fields, $saveFlag);
?>