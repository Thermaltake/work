<?

/* Кодировка */

include ($core_dir."core.php"); // Подключаем файл ядра сайта


$MCCfg       = new MySQL_Config; // Конфигурация подключения к базе
// Конфигурация подключения к базе...
$MCCfg->host = 'localhost';
$MCCfg->user = 'root';
$MCCfg->pass = '';
$MCCfg->db = 'site';
$_DB = $MCCfg->db;

// Окончание конфигурации подключения к базе...
include ($core_dir."params.php"); // Подключаем переменные сайта
$db->autocommit(true); // Автоматическое применение изменений. Не удалять и не изменять ни в коем случае.
$db->set_charset("utf8");

$limit_items = 30;