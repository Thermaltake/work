<?

/* Кодировка */


define("DEFAULT_MODULE", "catalog"); // Модуль запускаемый по умолчанию, если запрашеваемый не найден, или другой не запрашивается
define("MODULE_DIR",     "modules"); // Директория с модулями
define("FILEMO_NAME",    "mo.php"); // Файл, открывающий модуль
define("COOKIE_TIME",    "2629743"); // Время хранения куки, в секундах.

$db           = new foo_mysqli($MCCfg->host, $MCCfg->user, $MCCfg->pass, $MCCfg->db); // Класс взаимодействия с базой MySQL через библиотеку MySQLI
$SP           = new SessionParams; // Глобальный класс с данными о сессии
$CP           = new CompanyData;
$CB           = new CB;
$Summary      = new Summary;
$Pages        = new Pages;
$Admins       = new Admins;
$Apps         = new Apps;


$SP->phpsesid = $_SESSION['phpsesid']; // ID одноразовой сессии
$SP->cookieid = $_COOKIE['cookieid']; // ID кук в браузере
$SP->ip       = $_SERVER['HTTP_X_REAL_IP']; // IP адрес пользователя
$SP->host     = $_SERVER['HTTP_HOST']; // IP адрес пользователя

$protocol = $_SERVER['HTTPS'] ? 'https' : 'http';
$host = $protocol ."://".$_SERVER['HTTP_HOST'];

$SP->url_param  = $_SERVER['REQUEST_URI'];
$SP->GetFullURL = $host . $SP->url_param;


