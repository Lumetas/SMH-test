<?php
require 'init_db.php';

function addItem($type, $data, $saveToDb = false) {
    $url = "https://dummyjson.com/$type/add";
    
    // Специальные преобразования данных
    if ($type === 'products' && isset($data['price'])) {
        $data['price'] = (float)$data['price'];
    }
    
    // Отправка запроса
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        die("Ошибка при добавлении элемента\n");
    }
    
    $newItem = json_decode($response, true);
    
    // Вывод информации о созданном элементе
    echo "Успешно создан $type:\n";
    echo "ID: {$newItem['id']}\n";
    
    foreach ($data as $key => $value) {
        if (isset($newItem[$key])) {
            echo ucfirst($key) . ": $value\n";
        }
    }
    
    // Сохранение в базу
    if ($saveToDb) {
        $db = new SQLite3(DB_FILE);
        
        $stmt = $db->prepare('INSERT INTO api_data 
                            (id, type, data, category) 
                            VALUES (:id, :type, :data, :category)');
        
        $stmt->bindValue(':id', $newItem['id']);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':data', json_encode($newItem));
        $stmt->bindValue(':category', $newItem['category'] ?? '');
        
        if ($stmt->execute()) {
            echo "\nСохранено в локальную базу (ID: {$newItem['id']})\n";
        } else {
            echo "\nОшибка сохранения в базу: " . $db->lastErrorMsg() . "\n";
        }
    }
}

// Парсинг аргументов командной строки
$args = array_slice($argv, 1);
$saveFlag = in_array('--save', $args);
$cleanArgs = array_diff($args, ['--save']);

if (count($cleanArgs) < 1) {
    die("Использование: php add_item.php <тип> [параметры] [--save]\nПример: php add_item.php products title=iPhone price=999 brand=Apple --save\n");
}

$type = array_shift($cleanArgs);
$data = [];

foreach ($cleanArgs as $arg) {
    if (strpos($arg, '=') !== false) {
        list($key, $value) = explode('=', $arg, 2);
        $data[$key] = $value;
    }
}

// Валидация обязательных полей
$requiredFields = [
    'products' => ['title', 'price'],
    'users' => ['firstName', 'lastName'],
    'posts' => ['title', 'body', 'userId']
];

if (isset($requiredFields[$type])) {
    foreach ($requiredFields[$type] as $field) {
        if (!isset($data[$field])) {
            die("Обязательное поле '$field' отсутствует\n");
        }
    }
}

addItem($type, $data, $saveFlag);
?>