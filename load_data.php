<?php
require 'init_db.php';

function loadData($type, $params = []) {
    $db = new SQLite3(DB_FILE);
    
    // Базовый URL
    $url = "https://dummyjson.com/$type";
    
    // Определяем endpoint в зависимости от типа данных
    if (in_array($type, ['products', 'users'])) {
        $url .= '/search';
    }
    
    // Добавляем параметры запроса
    $queryParams = [];
    if (!empty($params['query'])) {
        $queryParams['q'] = $params['query'];
    }
    if (!empty($params['limit'])) {
        $queryParams['limit'] = $params['limit'];
    }
    if ($type === 'products' && !empty($params['category'])) {
        $queryParams['category'] = $params['category'];
    }
    
    if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
    }
    
    // Выполняем запрос
    $response = file_get_contents($url);
    if (!$response) {
        die("Ошибка при запросе к API\n");
    }
    
    $data = json_decode($response, true);
    $items = $data[$type] ?? $data['products'] ?? [];
    
    $count = 0;
    foreach ($items as $item) {
        // Проверяем категорию (если указана)
        if (!empty($params['category']) && $type === 'products') {
            if (strtolower($item['category']) !== strtolower($params['category'])) {
                continue;
            }
        }
        
        $stmt = $db->prepare('INSERT OR REPLACE INTO api_data 
                            (id, type, data, search_query, category) 
                            VALUES (:id, :type, :data, :query, :category)');
        
        $stmt->bindValue(':id', $item['id']);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':data', json_encode($item));
        $stmt->bindValue(':query', $params['query'] ?? '');
        $stmt->bindValue(':category', $item['category'] ?? '');
        
        if ($stmt->execute()) {
            $count++;
        }
    }
    
    echo "Загружено $count записей типа '$type'\n";
    if (!empty($params['query'])) {
        echo "Поисковый запрос: '{$params['query']}'\n";
    }
    if (!empty($params['category'])) {
        echo "Категория: '{$params['category']}'\n";
    }
}

// Обработка аргументов командной строки
$type = $argv[1] ?? 'products';
$params = [
    'query' => $argv[2] ?? '',
    'limit' => $argv[3] ?? 10,
    'category' => $argv[4] ?? ''
];

loadData($type, $params);
?>