<?php

// Путь к файлу servers.php
// Path to servers.php
$server_path = 'http://mysite.com/stats/server.php';

// Путь к файлу avatar.php
// Path fo avatar.php
$avatar_path = 'http://mysite.com/avatar.php';

// Использовать ли БД для получения списка пользователей. Иначе будет использоваться текстовый файл.
// Should we use a database to get a players list. Text file will be used instead.
$use_database = true;

// Путь к файлу со списком пользователей. Будет использоваться если $use_database == false
// Path fo text file with list of logins. Will be used if $use_database == false
$players_list_filename = 'players_list.txt';

// Интервал между запросами статистики к server.php, в секундах
// Interval between updates of all stats with query to server.php, in seconds
$update_time = 3500;

// Сколько игроков отображать на одной странице
// How many players should be displayed at one page
$players_at_page = 20;

// Количество кнопок для постраничной навигации. Нечётное число большее или равное 7.
// A number of buttins in page navigation. Must be an odd number greater or equal to 7.
$buttons_count = 9;

// Расположение директирии с иконками
// A location of directory with images
$img_dir = '/stats/img/';

// Имя файла кеша списка пользователей
// Players toplist cache file name
$cache_toplist = 'client_data_cache_toplist.json';

// Имя файла кеша времени
// Time cache file name
$time_cache = 'client_time_cache.json';

// Имя файла кеша данных
// Data cache file name
$cache_stats = 'client_data_cache_stats.json';

// === Настройки БД ===
// === Database Settings ===

// Название базы данных
// Database name
$db_name = 'mydatabase';

// Имя пользователя
// Username to connect
$db_username = 'myusername';

// Пароль
// Password to connect
$db_password = 'mypassword';

// Адрес
// DB Address
$db_host = 'localhost';

// SQL-запрос на получение списка пользователей
// SQL-query for getting a players list
$user_list_query = 'SELECT username FROM users';

?>
