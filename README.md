# Документация API-клиента DummyJSON

## Содержание
- [Документация API-клиента DummyJSON](#документация-api-клиента-dummyjson)
  - [Содержание](#содержание)
  - [Общее описание](#общее-описание)
  - [Установка](#установка)
  - [Примечания](#примечания)

## Общее описание

Система для работы с API [DummyJSON](https://dummyjson.com) через PHP-скрипты. Позволяет:
- Загружать данные в локальную базу SQLite
- Добавлять новые записи
- Просматривать сохраненные данные

## Установка

1. **Требования:**
   - PHP 7.4+
   - Включенный модуль SQLite3
   - `allow_url_fopen=On`

2. **Копирование файлов:**
   ```bash
   https://github.com/Lumetas/SMH-test.git
   cd SMH-test

## Скрипты

### init_db.php
Инициализирует базу данных.

php init_db.php

Создает:
- Файл базы данных dummyjson.db
- Таблицу api_data с индексами

### load_data.php
Загружает данные из API в базу.

Синтаксис:
```
php load_data.php <тип> [запрос] [лимит] [категория]
```
Пример:
```
php load_data.php products iPhone 20 smartphones
```
### add_item.php
Добавляет новый элемент через API.

Синтаксис:
```
php add_item.php <тип> [параметры] [--save]
```
Параметры:
- Формат: ключ=значение
- Значения с пробелами в кавычках
- --save - сохранить в базу

Пример:
```
php add_item.php products title="Новый телефон" price=599 --save
```
### show_data.php
Показывает данные из базы.

Синтаксис:
```
php show_data.php <тип> [запрос] [категория] [лимит]
```
Пример:
```
php show_data.php products iPhone smartphones 5
```
## Примеры использования
### Полный цикл работы:

```
php init_db.php


php load_data.php products phone 50


php add_item.php products title="Телефон" price=299 brand=Test --save


php show_data.php products phone
```
### Работа с пользователями:
```
php load_data.php users

php add_item.php users firstName=Иван lastName=Иванов age=30 --save

php show_data.php users
```
## Структура базы данных
Таблица api_data:
```
CREATE TABLE api_data (
    id INTEGER PRIMARY KEY,
    type TEXT NOT NULL,        -- Тип данных
    data TEXT NOT NULL,        -- JSON-данные
    search_query TEXT,         -- Поисковый запрос
    category TEXT,             -- Категория
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
Индексы:
- idx_api_data_type
- idx_api_data_query
- idx_api_data_category

## Примечания
- API в тестовом режиме не сохраняет изменения на сервере.
- Локальная база создается в файле dummyjson.db.
- Для значений с пробелами используйте кавычки: "значение с пробелами".