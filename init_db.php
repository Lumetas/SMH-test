<?php
define('DB_FILE', 'dummyjson.db');

function initDatabase() {
    $db = new SQLite3(DB_FILE);
    
    $db->exec('CREATE TABLE IF NOT EXISTS api_data (
        id INTEGER PRIMARY KEY,
        type TEXT NOT NULL,
        data TEXT NOT NULL,
        search_query TEXT,
        category TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    
    // Добавляем индекс для категорий
    $db->exec('CREATE INDEX IF NOT EXISTS idx_api_data_category ON api_data (category)');
    
    echo "База инициализирована. Файл: " . DB_FILE . "\n";
    return $db;
}

initDatabase();
?>