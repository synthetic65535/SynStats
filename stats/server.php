<?php

// =======================================

$statsdir = './stats'; //Путь к папке со статистикой
$cache_file = 'server_data_cache.json'; //Имя файла кеша данных
$time_file = 'server_time_cache.json'; //Имя файла кеша времени
$check_time = 3600; //Обновление кэша каждый час
$max_records = 1000; //Максимальное количество записей в статистике
$img_dir = '';

// =======================================

//Интересующая нас статистика, время игры учитывается обязательно
$interesting_params = array(
	//Время игры, в тиках
	array(
		'id' => 'stat.playOneMinute',			//ID параметра в json-файлах статистики minecraft-а
		'name' => 'Количество секунд в игре:',	//Название параметра
		'mul' => 0.01,							//Множитель для преобразования в очки
		'premul' => 0.05,						//Чтобы получить количество секунд надо предварительно поделить на 20
		'img' => 'img/time.png',				//Иконка
		'achievement' => false					//Является ли параметр достижением
		));

include 'params.php';

// =======================================

//Вычисляем время, прошедшее с последнего обновления кеша
$cur_time = time();
$last_check = file_get_contents($time_file);
$interval = $cur_time-$last_check;
$max_age = $check_time - $interval;
if ($max_age < 0) $max_age = 0;

// =======================================

//Добавляем заголовок
header('Content-type: application/json; charset=utf-8');
header('Cache-control: public, max-age='.$max_age);

// =======================================

//Копируем нужные поля из jsondata в playerdata
function extract_player_data($jsondata, &$playerdata)
{
	global $interesting_params;
	$points = 0;
	foreach ($interesting_params as $thekey => $field)
	{
		if (isset($jsondata[$field['id']]))
		{
			if ($field['achievement'] == true) {
				$playerdata['s'.$thekey] = ($jsondata[$field['id']] > 0 ? 1 : 0);
			} else {
				if (strpos($field['id'], 'mineBlock') !== false)
				{
					//Используем разницу между количеством добытых и поставленных блоков
					$playerdata['s'.$thekey] = max(0, round(($jsondata[$field['id']] - $jsondata[str_replace('mineBlock', 'useItem', $field['id'])]) * $field['premul']));
				} else {
					$playerdata['s'.$thekey] = round($jsondata[$field['id']] * $field['premul']);
				}
			}
		} else {
			$playerdata['s'.$thekey] = 0;
		}
		//Вычисляем количество очков
		$points += round($field['mul'] * $playerdata['s'.$thekey]);
	}
	//Добавляем количество очков
	$playerdata['p'] = $points;
}

// =======================================

if ($interval >= $check_time) //Если кеш устарел, то обновляем его
{
	//Имя файла это UUID пользователя
	$uuidlist = array();
	$myfileslist = scandir($statsdir);
	foreach ($myfileslist as $myfilename)
		if (strpos($myfilename, '.json') !== false)
			$uuidlist[] = substr($myfilename, 0, -5);
	
	//Пробегаемся по файлам
	$stats = array();
	foreach ($uuidlist as $uuid)
	{
		$jsonfile = $statsdir.'/'.$uuid.'.json';
		if (file_exists($jsonfile))
		{
			$jsonstr = file_get_contents($jsonfile);
			if ($jsonstr !== false)
			{
				$jsondata = json_decode($jsonstr, true);
				
				//Копируем нужные поля из jsondata в playerdata
				$playerdata = array('u' => $uuid);
				extract_player_data($jsondata, $playerdata);
				
				//Добавляем игрока в массив со статистикой
				$stats[] = $playerdata;
			}
		}
	}
	
	//Сортируем игроков по очкам
	usort($stats, create_function('$a, $b', "return \$a['p'] < \$b['p'];"));
	
	//Применим ограничение на количество записей, чтобы уберечь клиента от перенапряжения
	$stats = array_slice($stats, 0, $max_records);
	
	$echodata = json_encode($stats);
	echo $echodata;
	
	//Записываем в кеш данные
	file_put_contents($cache_file, $echodata);
	//Записываем в кеш время, если данные не пустые
	if (strlen($echodata) > 10) //Если закодированный json занимает меньше 10 символов, то он наверняка пустой
		file_put_contents($time_file , $cur_time);
} else {
	//Если кеш еще свежий, отправляем его
	$echodata = file_get_contents($cache_file);
	echo $echodata;
}

// =======================================

?>
