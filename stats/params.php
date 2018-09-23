<?php
		//Количество ачивок
$interesting_params[] = array(
		'id' => 'achievement_counter',
		'name' => 'Выполнено достижений:',
		'mul' => 100,
		'premul' => 1,
		'img' => $img_dir.'diamonds.png',
		'achievement' => true
		);
		//прошел расстояние
		/*
$interesting_params[] = array(
		'id' => 'stat.walkOneCm',
		'name' => 'Прошел расстояние:',
		'mul' => 0.01,
		'premul' => 0.01,
		'img' => $img_dir.'walk.png',
		'achievement' => false
		);
		//на лодке
$interesting_params[] = array(
		'id' => 'stat.boatOneCm',
		'name' => 'Проплыл на лодке:',
		'mul' => 0.1,
		'premul' => 0.01,
		'img' => $img_dir.'boat.png',
		'achievement' => false
		);
		//урона нанесено
$interesting_params[] = array(
		'id' => 'stat.damageDealt',
		'name' => 'Нанёс урона при атаке:',
		'mul' => 0.001,
		'premul' => 1,
		'img' => $img_dir.'damage.png',
		'achievement' => false
		);
		//урона поглощено
$interesting_params[] = array(
		'id' => 'stat.damageTaken',
		'name' => 'Поглотил урона:',
		'mul' => 0.001,
		'premul' => 1,
		'img' => $img_dir.'hp.png',
		'achievement' => false,
		'achievement_counter' => false
		);*/
		//мобов убил
$interesting_params[] = array(
		'id' => 'stat.mobKills',
		'name' => 'Убил мобов:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'mob.png',
		'achievement' => false
		);
		//игроков убил
$interesting_params[] = array(
		'id' => 'stat.playerKills',
		'name' => 'Убил игроков:',
		'mul' => 10,
		'premul' => 1,
		'img' => $img_dir.'player.png',
		'achievement' => false
		);
		//умер
$interesting_params[] = array(
		'id' => 'stat.deaths',
		'name' => 'Количество смертей:',
		'mul' => -10,
		'premul' => 1,
		'img' => $img_dir.'dead.png',
		'achievement' => false
		);
		//добыл алмазов
$interesting_params[] = array(
		'id' => 'stat.mineBlock.56',
		'name' => 'Добыл алмазов:',
		'mul' => 3,
		'premul' => 1,
		'img' => $img_dir.'diamond_ore.png',
		'achievement' => false,
		'achievement_counter' => false
		);
		//добыл железа
$interesting_params[] = array(
		'id' => 'stat.mineBlock.15',
		'name' => 'Добыл железа:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'iron_ore.png',
		'achievement' => false
		);
		//добыл золота
$interesting_params[] = array(
		'id' => 'stat.mineBlock.14',
		'name' => 'Добыл золота:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'gold_ore.png',
		'achievement' => false,
		'achievement_counter' => false
		);
		//добыл лазурита
$interesting_params[] = array(
		'id' => 'stat.mineBlock.21',
		'name' => 'Добыл лазурита:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'lapis_ore.png',
		'achievement' => false
		);
		//добыл редстоуна
$interesting_params[] = array(
		'id' => 'stat.mineBlock.73',
		'name' => 'Добыл редстоуна:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'redstone_ore.png',
		'achievement' => false
		);
		//добыл кварца
$interesting_params[] = array(
		'id' => 'stat.mineBlock.153',
		'name' => 'Добыл кварца:',
		'mul' => 1,
		'premul' => 1,
		'img' => $img_dir.'quartz_ore.png',
		'achievement' => false,
		'achievement_counter' => false
		);
		//добыл угля
$interesting_params[] = array(
		'id' => 'stat.mineBlock.16',
		'name' => 'Добыл угля:',
		'mul' => 0.5,
		'premul' => 1,
		'img' => $img_dir.'coal_ore.png',
		'achievement' => false
		);
		//добыл камня
$interesting_params[] = array(
		'id' => 'stat.mineBlock.1',
		'name' => 'Выкопал камня:',
		'mul' => 0.01,
		'premul' => 1,
		'img' => $img_dir.'stone.png',
		'achievement' => false
		);

?>
