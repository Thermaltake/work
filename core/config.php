<?

/* Кодировка */
define ("DOCUMENT_ROOT", $_SERVER["DOCUMENT_ROOT"]);
include (DOCUMENT_ROOT . "/core/core.php"); // Подключаем файл ядра сайта


// Конфигурация подключения к базе...
$MySQL = new MySQL; // Конфигурация подключения к базе
$MySQL->SetConfig('host', '127.0.0.1');
$MySQL->SetConfig('user', 'root');
$MySQL->SetConfig('pass', '');
$MySQL->SetConfig('db', 'site');
$MySQL->SetConfig('charset', 'utf8');
$MySQL->ConnectToDB ();
// Окончание конфигурации подключения к базе...


include (DOCUMENT_ROOT . "/core/params.php"); // Подключаем переменные сайта

