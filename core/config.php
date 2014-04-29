<?

/* Кодировка */
define ("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
include (DOCUMENT_ROOT . "/core/core.php"); // Подключаем файл ядра сайта


$MySQL       = new MySQL; // Конфигурация подключения к базе
// Конфигурация подключения к базе...
$MySQL->SetConfig('host', '127.0.0.1');
$MySQL->SetConfig('user', 'root');
$MySQL->SetConfig('pass', '');
$MySQL->SetConfig('db', 'site');
$MySQL->ConnectToDB ();

//$_DB = $MySQL->db;

// Окончание конфигурации подключения к базе...
include (DOCUMENT_ROOT . "/core/params.php"); // Подключаем переменные сайта
$MySQL->connected->autocommit(true); // Автоматическое применение изменений. Не удалять и не изменять ни в коем случае.
$MySQL->connected->set_charset("utf8");

//$limit_items = 30;