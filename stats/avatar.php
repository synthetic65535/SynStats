<?php
/*
	Скрипт для генерации аватарок "на-лету" из скинов Minecraft. (c) synthetic.
	Пример запроса: http://mysite.ru/getavatar.php?login=synthetic
*/

// ========== Настройки ===========
$skins_path = 'fmx/Skins/'; // Путь к папке со скинами. Слеш в конце обязателен.
$default_skin = 'Default.png'; // Имя файла дефолтного скина
$variable_size = true; // Возвращать ли разные размеры аватарок, в зависимости от размера скина?
// Если variable_size = true, то загрузка аватарок быстрее, но нужно будет править CSS: https://www.google.ru/search?q=css+disable+resize+antialiasing
// Если variable_size = false, то аватарки будут раздуты до одного размера, загружаться медленнее, но не будет проблем с CSS.
$constant_size = 64; // Единый размер аватарок для случая variable_size = false
// ================================
// Техническая спецификация скинов: https://github.com/minotar/skin-spec

$login = filter_input(INPUT_GET, 'login', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);

$filename = $skins_path.$login.'.png';

if (!file_exists($filename)) {
	$filename = $skins_path.$default_skin;
	if (!file_exists($filename)) {
		http_response_code(404);
		exit;
	}
}

header('Content-type: image/png');
header('Content-Disposition: inline; filename="'.$login.'.png" ');
header('Cache-control: public, max-age=3600');

$skin = imagecreatefrompng($filename);

// Если некорректный png файл
if ($skin === false) {
	http_response_code(404);
	exit;
}

$width = imagesx ($skin);
$height = imagesy ($skin);

// Проверяем размеры скина на корректность
if ($width < 64 || $height != $width && $height * 2 != $width) {
	http_response_code(404);
	exit;
}

// Проверяем, содержит ли скин прозрачные пиксели
$transparency = false;
for($i = 0; $i < $width; $i++) {
    for($j = 0; $j < $height; $j++) {
        $rgba = imagecolorat($skin, $i, $j);
        if(($rgba & 0x7F000000) >> 24) {
            $transparency = true;
            break 2;
        }
    }
}

$size_src = intval($width / 64) * 8;
$size_dst = $variable_size ? $size_src : $constant_size;

// Создаём изображение с чёрным фоном (по-умолчанию тело Стива чёрное)
$img = imagecreatetruecolor($size_dst, $size_dst);
imagefill($img, 0, 0, imagecolorallocate($img, 0, 0, 0));

// Копируем лицо
imagecopyresized ($img, $skin, 0, 0, $size_src, $size_src, $size_dst, $size_dst, $size_src, $size_src);
// Если прозрачность поддерживается, накладываем шлем
if ($transparency)
	imagecopyresized ($img, $skin, 0, 0, $size_src * 5, $size_src, $size_dst, $size_dst, $size_src, $size_src);

imagepng($img);
imagedestroy($img);

?>
