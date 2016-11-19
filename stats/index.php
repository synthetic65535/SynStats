<!DOCTYPE HTML>
<html lang="en-US">
<head>
<meta charset="utf-8">
<title>Статистика</title>
</head>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<?php

//Подключаем настройки
include 'settings.php';

//Подключаем языковой файл
include 'lang.php';

// =======================================

//Вычисляем время, прошедшее с последнего обновления кеша
$cur_time = time();
$last_check = file_get_contents($time_cache);
$interval = $cur_time - $last_check;
$max_age = $update_time - $interval;
if ($max_age < 0) $max_age = 0;

// =======================================

//Добавляем заголовки
header('Content-type: text/html; charset=utf-8');
header('Cache-control: public, max-age='.$max_age);

// =======================================

//Интересующая нас статистика, время игры учитывается обязательно
$interesting_params = array(
	//Время игры, в тиках
	array(
		'id' => 'stat.playOneMinute',			//ID параметра в json-файлах статистики minecraft-а
		'name' => $lang['seconds_ingame'],		//Название параметра
		'mul' => 0.01,							//Множитель для преобразования в очки
		'premul' => 0.05,						//Чтобы получить количество секунд надо предварительно поделить на 20
		'img' => $img_dir.'time.png',			//Иконка
		'achievement' => false					//Является ли параметр достижением
		));

//Дополнительная статистика
include 'params.php';

// =======================================

//Получаем переменные из GET-запроса
$login = filter_input(INPUT_GET, 'login', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);

//Максимальная длина логина 16 символов
if (strlen($login) > 16) $login = substr($login, 0, 16);

// =======================================

// Генерация UUID по версии Spigot
// Взято отсюда: https://github.com/synthetic65535/WebFMX3/blob/e70918c74e8cfc12fffe66ac0c664601916ad00f/webUtils/auxUtils.php#L25
// То же самое здесь: https://github.com/alexandrage/Fix-Sashok/blob/master/site/uuid.php
function generate_uuid ($login) {
    $val = md5('OfflinePlayer:'.$login, true);
    $byte = array_values(unpack('C16', $val));
    $tLo = ($byte[0] << 24) | ($byte[1] << 16) | ($byte[2] << 8) | $byte[3];
    $tMi = ($byte[4] << 8) | $byte[5];
    $tHi = ($byte[6] << 8) | $byte[7];
    $csLo = $byte[9];
    $csHi = $byte[8] & 0x3f | (1 << 7);
	
    if (pack('L', 0x6162797A) == pack('N', 0x6162797A)) {
        $tLo = (($tLo & 0x000000ff) << 24) | (($tLo & 0x0000ff00) << 8) | (($tLo & 0x00ff0000) >> 8) | (($tLo & 0xff000000) >> 24);
        $tMi = (($tMi & 0x00ff) << 8) | (($tMi & 0xff00) >> 8);
        $tHi = (($tHi & 0x00ff) << 8) | (($tHi & 0xff00) >> 8);
    }
    $tHi &= 0x0fff;
    $tHi |= (3 << 12);
	
    $uuid = sprintf('%08x%04x%04x%02x%02x%02x%02x%02x%02x%02x%02x', $tLo, $tMi, $tHi, $csHi, $csLo, $byte[10], $byte[11], $byte[12], $byte[13], $byte[14], $byte[15]);
    return $uuid;
}

// =======================================

// Функция получения списка игроков из базы данных
// Взято отсюда: https://github.com/synthetic65535/WebFMX3/blob/e70918c74e8cfc12fffe66ac0c664601916ad00f/webUtils/dbUtils.php
function get_players_list_mysql(&$playerslist)
{
	global $db_name, $db_username, $db_password, $db_host, $user_list_query;
	
	$dbHandle = null;
	
	try {
        $dbHandle = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password);
    } catch (PDOException $pdoException) {
        return;
    }
    
	$arguments = array();
	$preparedRequest = null;
    try {
	    $preparedRequest = $dbHandle->prepare($user_list_query);
	    if ($preparedRequest === null) return;
	    if (!$preparedRequest->execute($arguments)) return;
    } catch (PDOException $pdoException) {
        return;
    }
	
	$playerslist = array();
	while ($login = $preparedRequest->fetchColumn())
	{
		$uuid = generate_uuid($login);
		$playerslist[$uuid] = $login;
	}
	
    $preparedRequest = null;
	$dbHandle = null;
}

// =======================================

//Получить список игроков из файла
function get_players_list_fromfile(&$playerslist)
{
	global $players_list_filename;
	$player_names = file($players_list_filename, FILE_IGNORE_NEW_LINES);
	
	$playerslist = array();
	foreach ($player_names as $login)
	{
		$uuid = generate_uuid($login);
		$playerslist[$uuid] = $login;
	}
}

// =======================================

//Подгружаем основной массив $stats со статистикой
$stats = array();
$toplist = array();
if ($interval >= $update_time) //Если кеш устарел, то обновляем его
{
	//Получаем список игроков
	$playerslist = array();
	if ($use_database == true)
		get_players_list_mysql($playerslist); else
		get_players_list_fromfile($playerslist);
	
	//Проверяем есть ли у нас список пользователей
	if (count($playerslist) == 0)
		echo '<p>'.$lang['err_no_users'].'</p>';
	
	//Получаем от сервера всю информацию
	$statstext = file_get_contents($server_path);
	$stats = json_decode($statstext, true);
	
	//Проверяем вернул ли server.php информацию
	if (count($stats) == 0)
		echo '<p>'.$lang['err_no_stats'].'</p>';
	
	//Пробегаемся по игрокам, добавляем логины в массив
	$number = 0;
	foreach ($stats as $thekey => $field)
	{
		$shorten_uuid = str_replace('-','',$field['u']);
		if (isset($playerslist[$shorten_uuid]))
		{
			$number += 1;
			//Добавляем имя пользователя:
			$stats[$thekey]['l'] = $playerslist[$shorten_uuid];
			//Вычисляем место в рейтинге:
			$stats[$thekey]['n'] = $number;
		} else {
			//Удаляем не найденных игроков:
			unset($stats[$thekey]); 
		}
	}
	
	//Проверяем осталась ли информация после сопоставления логинов и uuid
	if (count($stats) == 0)
		echo '<p>'.$lang['err_uuid'].'</p>';
	
	//Преобразовываем массив в строку
	$stats_string = json_encode($stats);
	//Записываем в кеш данные
	file_put_contents($cache_stats, $stats_string);
	
	//Формируем список игроков, состоящий только из логина, кол-ва очков и номера в рейтинге
	foreach ($stats as $onestat)
		$toplist[] = array('l' => $onestat['l'], 'p' => $onestat['p'], 'n' => $onestat['n']);
	//Преобразовываем массив в строку
	$toplist_string = json_encode($toplist);
	//Записываем в кеш данные
	file_put_contents($cache_toplist, $toplist_string);
	
	//Записываем в кеш время, если данные не пустые
	if (strlen($stats_string) > 10) //Если закодированный json занимает меньше 10 символов, то он наверняка пустой
		file_put_contents($time_cache , $cur_time);
} else {
	//Если кеш ещё свежий, то достаем данные из кеша
	if ($login !== null) //Если указан логин, то нас просят показать конкретного пользователя
	{
		//Если собираемся выводить информацию о конкретном пользователе, то нужно подгружать большой кеш, для поиска по логину
		$stats_string = file_get_contents($cache_stats);
		$stats = json_decode($stats_string, true);
	} else {
		//Если собираемся печатать список всех пользователей, то подгружаем маленький кеш
		$toplist_string = file_get_contents($cache_toplist);
		$toplist = json_decode($toplist_string, true);
	}
}

unset($toplist_string);
unset($stats_string);

// =======================================

//Вывод основного списка игроков, если не указан логин
if ($login === null)
{
	//Отображение в формате html
?>
<table class="tbl">
<tr><td colspan="4" id="sch"><form class="sfm" action="<?php echo $_SERVER["SCRIPT_NAME"]; ?>" method=get>
<?php echo $lang['search_nick']; ?><input id="sbr" type="text" name="login" maxlength="16" />
<input type="submit" value="<?php echo $lang['search']; ?>">
</form></td></tr>
<tr><th><?php echo $lang['rank']; ?></th><th><?php echo $lang['avatar']; ?></th><th><?php echo $lang['nick']; ?></th><th><?php echo $lang['points']; ?></th><tr>
<?php
	//Проверяем номер страницы, не выходит ли он за пределы разумного
	$toplist_size = count($toplist);
	$all_buttons_count = ceil($toplist_size / $players_at_page);
	if ($page == null || $page < 1 || $page > $all_buttons_count)
		$page = 1;
	//Печатаем таблицу
	for ($i=0; $i < $players_at_page; $i++)
	{
		$thekey = $i + ($page - 1) * $players_at_page;
		if ($thekey >= $toplist_size) break;
		?>
<tr><td class="num"><?php echo $toplist[$thekey]['n']; ?></td><td class="avs"><a href="<?php echo $_SERVER["SCRIPT_NAME"].'?login='.$toplist[$thekey]['l']; ?>"><img src="<?php echo $avatar_path.'?login='.$toplist[$thekey]['l']; ?>" class="ais" /></a></td><td><a href="<?php echo $_SERVER["SCRIPT_NAME"].'?login='.$toplist[$thekey]['l']; ?>"><div><?php echo $toplist[$thekey]['l']; ?></div></a></td><td class="pts"><?php echo $toplist[$thekey]['p']; ?></td><tr>
<?php
	}
?>
</table>
<?php
	// --------------- Начало создания кнопок навигации ---------------
	//
	if ($toplist_size <= 9 * $players_at_page) //если отображать не более 9 страниц
	{
?>
<table class="nav"><tr>
<?php
		for ($i = 1; $i <= $all_buttons_count; $i++)
		{
?>
<td><a href="<?php echo $_SERVER["SCRIPT_NAME"].'?page='.$i; ?>"><div><?php echo $i; ?></div></a></td>
<?php
		}
?>
</tr></table>
<?php
	} else
	{
?>
<table class="nav"><tr>
<?php
		$half_buttons_count = floor($buttons_count / 2);
		for ($i = 1; $i <= $buttons_count; $i++)
		{
			//
			$left_side = $page - $half_buttons_count;
			$right_side = $page + $half_buttons_count;
			//
			$new_page = $page;
			//
			if ($left_side <= 1)
				$new_page = $half_buttons_count + 1;
			if ($right_side >= $all_buttons_count)
				$new_page = $all_buttons_count - $half_buttons_count;
			//
			$button = $new_page + $i - $half_buttons_count - 1;
			//
			if ($left_side > 1)
			{
				if ($i == 1) $button = 1;
				if ($i == 2) $button = 0;
			}
			if ($right_side < $all_buttons_count)
			{
				if ($i == $buttons_count-1) $button = 0;
				if ($i == $buttons_count) $button = $all_buttons_count;
			}
			//
			if ($button == 0)
			{
?>
<td><div>...</div></td>
<?php
			} else
			{
?>
<td><a href="<?php echo $_SERVER["SCRIPT_NAME"].'?page='.$button; ?>"><div<?php if ($button == $page) echo ' class="cur"'; ?>><?php echo $button; ?></div></a></td>
<?php
			}
		}
?>
</tr></table>
<?php
	}
	// --------------- Конец создания кнопок навигации ---------------
}

// =======================================

//Если логин указан, то отображаем информацию о пользователе
if ($login !== null)
{
	//Линейно пробегаясь по всем игрокам (TODO: бинарный поиск), находим нужную информацию об этом игроке
	$found = false;
	if (strlen($login) > 0)
	{
		$login_lowercase = strtolower($login);
		foreach ($stats as $onestat)
			if (strpos(strtolower($onestat['l']),$login_lowercase) !== false)
			{
				$playerdata = $onestat;
				$found = true;
				break;
			}
	}
	
	//Если информация об игроке найдена, выводим её в формате html
	if ($found)
	{
		//Переводим время в удобный формат
		$seconds = $playerdata['s0']; //Короткое имя, вместо stat.playOneMinute
		$hours = floor($seconds / 3600);
		$mins = floor($seconds / 60 % 60);
		$secs = floor($seconds % 60);
		$timeformat = sprintf('%d:%02d:%02d', $hours, $mins, $secs);
		$playerdata['time'] = $timeformat;

?>
<table class="tbl">
<tr><td rowspan="4" colspan="2" id="avc"><img src="<?php echo $avatar_path.'?login='.$playerdata['l']; ?>" id="avi" /></td>
<td colspan="4" id="lgn"><?php echo $playerdata['l']; ?></td></tr>
<tr><td colspan="3"><?php echo $lang['position']; ?></td><td class="res"><?php echo $playerdata['n']; ?></td></tr>
<tr><td colspan="3"><?php echo $lang['points2']; ?></td><td class="res"><?php echo $playerdata['p']; ?></td></tr>
<tr><td colspan="3"><?php echo $lang['time_ingame']; ?></td><td class="res"><?php echo $playerdata['time']; ?></td></tr>
<tr><th><?php echo $lang['icon']; ?></th><th colspan="2"><?php echo $lang['param_name']; ?></th><th><?php echo $lang['quantity']; ?></th><th><?php echo $lang['multipiler']; ?></th><th><?php echo $lang['points']; ?></th><tr>
<?php
			foreach ($interesting_params as $thekey => $field)
			{
?>
<tr><td class="icn"><img src="<?php echo $field['img']; ?>" class="iim <?php echo ($field['achievement'] == true) ? 'ach': 'int'; ?>" /></td><td colspan="2"><?php echo $field['name']; ?></td><td class="val"><?php echo (($field['achievement'] == true) ? ($playerdata['s'.$thekey] > 0 ? 'Выполнено' : 'Нет') : ($playerdata['s'.$thekey])); ?></td><td class="mul"><?php echo $field['mul']; ?></td><td class="res"><?php echo round($field['mul'] * $playerdata['s'.$thekey]); ?></td><tr>
<?php
			}

?>
<tr><td colspan="5"><?php echo $lang['summary']; ?></td></td><td class="res"><?php echo $playerdata['p']; ?></td><tr>
</table>
<table class="nav"><tr>
<td><a href="<?php echo $_SERVER["SCRIPT_NAME"].'?page='.(ceil($playerdata['n'] / $players_at_page)); ?>"><div><?php echo $lang['back']; ?></div></a></td>
</tr><table>
<?php
		} else {
		//Если игрок не найден, выводим ошибку
?>
<table class="tbl"><tr>
<tr><td id="msg"><?php echo $lang['not_found']; ?></td></tr>
<tr><td><a href="<?php echo $_SERVER["SCRIPT_NAME"]; ?>"><div><?php echo $lang['back']; ?></div></a></td></tr>
</tr><table>
<?php
		}
}

// =======================================

?>
</body>

