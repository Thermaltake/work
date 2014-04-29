<?

/* Кодировка */


class SessionParams
{
    protected $IsBanned;
    
    function __construct ()
    {
        $this->IsBanned = false;
        $this->CheckBan();
    }
    
    private function CheckBan ()
    {
        global $_DB;
        global $MCCfg;
        $ip     = $this->GetIP();
        
        $result = $MCCfg->query("SELECT guid FROM $_DB.bans WHERE ip='$ip'");
        while($row = $result->fetch_array(MYSQL_ASSOC))
        {
            $this->SetBanned(true);
        }
        $result->free();
    }
    
    private function SetBanned ($status)
    {
        $this->IsBanned = $status;
    }

    function IsBan ()
    {
        return $this->IsBanned;
    }

    function GetURIId ($id = 0)
    {
        $uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
        return Text::ScreeningURL($uri[$id]);
    }

    function GetIP ()
    {
        return Text::ScreeningURL($_SERVER['HTTP_X_REAL_IP']); // return REAL IP
    }

    function GetHost ()
    {
        return Text::ScreeningURL($_SERVER['HTTP_HOST']); // return host (www.domain.ru)
    }

    function GetCookie ($id = 'cookieid')
    {
        return Text::ScreeningURL($_COOKIE[$id]); // return screening cookie from cookie id
    }

    function GetProtocol ()
    {
        return $_SERVER['HTTPS'] ? 'https' : 'http'; // return protocol (http or https)
    }

    function GetURI ()
    {
        return Text::ScreeningURL($_SERVER['REQUEST_URI']); // return uri (/status/index.php?type=html&error=0)
    }

    function GetSiteUrl ()
    {
        return $this->GetProtocol() . "://" . $this->GetHost (); // return url site (http://www.domain.ru)
    }

    function GetURL ()
    {
        return $this->GetSiteUrl() . $this->GetURI (); // return full now url (http://www.domain.ru/status/index.php?type=html&error=0)
    }
    
    function SetCookie ($name, $hash)
    {
        setcookie($name, $hash, time()+COOKIE_TIME, "/"); // set cookie
    }
}

class Admins
{
    protected $Rank;
    protected $IsAdmin;
    
    function __construct ()
    {
        $this->Rank = 0;
        $this->There = false;
        $this->IsAdminConstruct ();
    }
    
    private function SetRank ($rank)
    {
        $this->Rank = $rank; // set admin rank (not use other code,) only for class
    }
    
    function GetRank ()
    {
        return $this->Rank; // return admin rank
    }

    function IsAdmin()
    {
        return $this->IsAdmin; // return true or false (is admin ?)
    }

    private function SetIsAdmin ($_IsAdmin)
    {
        $this->IsAdmin = $_IsAdmin; // set Is Admin
    }

    function Join ($username, $password)
    {
        $hash = sha_password($username, $password);
        $this->IsAdminConstruct($hash);
        return $hash;
    }

    private function IsAdminConstruct ($cookie = null)
    {
        global $db;
        global $_DB;
        global $MCCfg;
        
        $cookie = $cookie != null ? $cookie : SessionParams::GetCookie();

        if ($cookie)
        {
            $result = $MCCfg->query("SELECT username, rank FROM $_DB.admins WHERE pass_hash='$cookie'");
            while($row = $result->fetch_array(MYSQL_ASSOC))
            {
                $this->SetRank($row['rank']);
                $this->SetIsAdmin(true);
                $this->SetLastVisit();
                SessionParams::SetCookie ('cookieid', $cookie);
            }
            $result->free();
        }
    }

    private function SetLastVisit()
    {
        global $db;
        global $_DB;
        global $MCCfg;

        $cookie = SessionParams::GetCookie();
        $ip     = SessionParams::GetIP();
        $time   = time();

        if ($this->IsAdmin())
        {
            $MCCfg->query("UPDATE $_DB.admins SET time='$time', ip='$ip' WHERE pass_hash='$cookie'");
        }
    }
}

class Apps
{

    protected $ModuleDir;
    protected $ModuleFileName;

    function __construct ()
    {
        $uri = SessionParams::GetURIId(0);
        $ModuleName = DOCUMENT_ROOT . "/" . MODULE_DIR . "/" . ($uri ? $uri : DEFAULT_MODULE) . "/";
        $this->ModuleDir = $ModuleName;
        $this->ModuleFileName = FILEMO_NAME;
    }

    function OpenModule ()
    {
        $uri = SessionParams::GetURIId(0);
        $ModuleFile = $this->GetPathModuleFile ();
        $There = FileDir::CheckFile($ModuleFile);

        if ($There)
        {
            return ($ModuleFile);
        }
        else
        {
            return (DOCUMENT_ROOT . "/" . MODULE_DIR . "/" . DEFAULT_MODULE . "/" . FILEMO_NAME);
        }
    }

    function GetModuleFile ()
    {
        return $this->ModuleFileName;
    }

    function GetModuleDir ()
    {
        return $this->ModuleDir;
    }

    function GetPathModuleFile ()
    {
        return $this->GetModuleDir() . "" . $this->GetModuleFile();
    }
}


class FileDir
{
    function CheckFile ($file)
    {
        return file_exists($file);
    }
}


class Text
{
    function Screening ($text)
    {
        $text = str_replace( "&"				, "&amp;"         , $text );
        $text = str_replace( ">"				, "&gt;"          , $text );
        $text = str_replace( "<"				, "&lt;"          , $text );
        $text = str_replace( "\\\\"		  , "&#092;"        , $text );
        $text = str_replace( "\'"			  , "&#39;"         , $text );
        $text = str_replace( "\""		  	, "&quot;"        , $text );
        $text = str_replace( '"'				, "&quot;"        , $text );
        $text = str_replace( "$"				, "&#036;"        , $text );
        $text = str_replace( "!"				, "&#33;"         , $text );
        $text = str_replace( "'"				, "&#39;"         , $text );
        $text = str_replace( "\&#39;"   , "&#39;"         , $text );
        $text = str_replace( "\&quot;"  , "&quot;"        , $text );

        return $text;
    }

    function ScreeningURL ($text)
    {
        $text = str_replace( "&"				, "&amp;"         , $text );
        $text = str_replace( ">"				, "&gt;"          , $text );
        $text = str_replace( "<"				, "&lt;"          , $text );
        $text = str_replace( "\\\\"		  , "&#092;"        , $text );
        $text = str_replace( "\'"			  , "&#39;"         , $text );
        $text = str_replace( "\""		  	, "&quot;"        , $text );
        $text = str_replace( '"'				, "&quot;"        , $text );
        $text = str_replace( '/'				, "&#47;"         , $text );
        $text = str_replace( "$"				, "&#036;"        , $text );
        $text = str_replace( "!"				, "&#33;"         , $text );
        $text = str_replace( "'"				, "&#39;"         , $text );
        $text = str_replace( "\&#39;"   , "&#39;"         , $text );
        $text = str_replace( "\&quot;"  , "&quot;"        , $text );

        return $text;
    }

    function ScreeningBB($message, $br = true)
    {
        /* Поехали танцы с бубном...
            Танцы с регулярками */

        if ($br)
            $message = nl2br($message);

        $message = str_replace( "[/p]<br />"				, "[/p]"          , $message );
        $message = str_replace( "[/li]<br />"				, "[/li]"          , $message );
        $message = str_replace( "[ul]<br />"				, "[ul]"          , $message );
        $message = str_replace( "[/ul]<br />"				, "[/ul]"          , $message );

        $message = str_replace( "[left]<br />"				, "[left]"          , $message );
        $message = str_replace( "[/left]<br />"				, "[/left]"          , $message );

        $message = str_replace( "[right]<br />"				, "[right]"          , $message );
        $message = str_replace( "[/right]<br />"				, "[/right]"          , $message );

        $message = str_replace( "[center]<br />"				, "[center]"          , $message );
        $message = str_replace( "[/center]<br />"				, "[/center]"          , $message );

        $message = str_replace( "[justify]<br />"				, "[justify]"          , $message );
        $message = str_replace( "[/justify]<br />"				, "[/justify]"          , $message );



        $message = preg_replace("#\[code\](.+)\[\/code\]#isU", '\\hack_left \\1', $message);


        $message = preg_replace("#\[left\](.+)\[\/left\]#isU", '<div align="left">\\1</div>', $message);
        $message = preg_replace("#\[right\](.+)\[\/right\]#isU", '<div align="right">\\1</div>', $message);
        $message = preg_replace("#\[center\](.+)\[\/center\]#isU", '<div align="center">\\1</div>', $message);
        $message = preg_replace("#\[justify\](.+)\[\/justify\]#isU", '<div align="justify">\\1</div>', $message);

        $message = preg_replace("#\[style\](.+)\[\/style\]#isU", '<div class="Style contacts_block">\\1</div>', $message);
        $message = preg_replace("#\[db\](.+)\[\/db\]#isU", '<div class="phone_table_two">\\1</div>', $message);


        $message = preg_replace("#\[ul\](.+)\[\/ul\]#isU", '<ul class="Pages">\\1</ul>', $message);
        $message = preg_replace("#\[li\](.+)\[\/li\]#isU", '<li class="Pages">\\1</li>', $message);
        $message = preg_replace("#\[p\](.+)\[\/p\]#isU", '<p>\\1</p>', $message);
        $message = preg_replace("#\[b\](.+)\[\/b\]#isU", '<b>\\1</b>', $message);
        $message = preg_replace("#\[i\](.+)\[\/i\]#isU", '<i>\\1</i>', $message);
        $message = preg_replace("#\[u\](.+)\[\/u\]#isU", '<u>\\1</u>', $message);
        $message = preg_replace("#\[s\](.+)\[\/s\]#isU", '<s>\\1</s>', $message);
        $message = preg_replace("#\[quote\](.+)\[\/quote\]#isU",'<div class="quoteHead">Цитата</div><div class="quoteContent">\\1</div>',$message);
        $message = preg_replace("#\[quote=&quot;([- 0-9a-zа-яА-Я]{1,30})&quot;\](.+)\[\/quote\]#isU", '<div class="quoteHead">\\1 пишет:</div><div class="quoteContent">\\2</div>', $message);
        $message = preg_replace("#\[url\][\s]*([\S]+)[\s]*\[\/url\]#isU",'<a href="\\1" target="_blank">\\1</a>',$message);

        $message = preg_replace("#\[url=(.+)\](.+)\[\/url\]#isU", '<a href="\\1" target="_blank">\\2</a>', $message);

        $message = preg_replace("#\[img\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<div class="ImageInText Style" style="background: url(\\1) no-repeat scroll center center / contain transparent;"><a target="_blank" class="img" href="\\1"></a></div>',$message);

        $message = preg_replace("#\[img left\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<div class="ImageInText_left Style" style="background: url(\\1) no-repeat scroll center center / contain transparent;"><a target="_blank" class="img" href="\\1"></a></div>',$message);
        $message = preg_replace("#\[img right\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<div class="ImageInText_right Style" style="background: url(\\1) no-repeat scroll center center / contain transparent;"><a target="_blank" class="img" href="\\1"></a></div>',$message);

        $message = preg_replace("#\[color=(.+)\](.+)\[\/color\]#isU", '<span style="color: \\1">\\2</span>', $message);
        $message = preg_replace("#\[size=(.+)\](.+)\[\/size\]#isU", '<span style="font-size: \\1px">\\2</span>', $message);
        return $message;
    }
}


class MySQL_Config
{
    protected $queries;

    function __construct ()
    {
        $this->queries = 0;
    }
    
    function query ($sql, $use_result = false)
    {
        global $db;

        if ($use_result)
            $result = $db->query($sql, MYSQLI_USE_RESULT);
        else
            $result = $db->query($sql);

        $this->queries++;
        return $result;
    }
    
    function GetQueries ()
    {
        return $this->queries;
    }
}







function status ($id, $type = 0)
{
   if ($type == 0)
   {
      switch ($id)
      {
         case '0': return "<div class='open'>Заявка открыта</div>"; break;
         case '1': return "<div class='close'>Заявка закрыта</div>"; break;
         case '2': return "<div class='wait'>Заявка ожидает</div>"; break;
         case '3': return "<div class='ns'>Не выполнено</div>"; break;
         default: return "<div class='unknown_status'>Ошибка</div>";
      }
   }
   else
   {
      switch ($id)
      {
         case '0': return "open"; break;
         case '1': return "close"; break;
         case '2': return "wait"; break;
         case '3': return "ns"; break;
         default: return "unknown_status";
      }
   }
}

function NormalTime ($time)
{
   return date ("G:i-s j-n-Y", $time);
}

function NowDay ($time)
{
   return date ("j", $time);
}

function NowMonth ($time)
{
   return date ("n", $time);
}

function NowYear ($time)
{
   return date ("Y", $time);
}


function selected ($id1, $id2)
{
   if ($id1 == $id2)
      return "selected='selected'";
}

function sha_password($email,$pass)
{
   $email = strtoupper($email);
   $pass = strtoupper($pass);
   return SHA1($email.':'.$pass);
}

class foo_mysqli extends mysqli
{
    public function __construct($host, $user, $pass)
    {
        parent::init();

        if (!parent::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0'))
        {
            die('Установка MYSQLI_INIT_COMMAND завершилась провалом');
        }

        if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 0))
        {
            die('Установка MYSQLI_OPT_CONNECT_TIMEOUT завершилась провалом');
        }

        if (!parent::real_connect($host, $user, $pass))
        {
            die('Ошибка подключения (' . mysqli_connect_errno() . ') '. mysqli_connect_error());
        }
    }
}



class CompanyData
{
}


class CB {
}

class Summary {
}

class Pages {
}


function UnScreeningTextBB ($text)
{
   $text = str_replace( "[b]"				, "<b>"          , $text );
   $text = str_replace( "[/b]"			, "</b>"         , $text );

   $text = str_replace( "[i]"				, "<i>"          , $text );
   $text = str_replace( "[/i]"			, "</i>"         , $text );

   $text = str_replace( "[u]"				, "<u>"          , $text );
   $text = str_replace( "[/u]"			, "</u>"         , $text );

   $text = str_replace( "[p]"				, "<p>"          , $text );
   $text = str_replace( "[/p]"			, "</p>"         , $text );

   $text = str_replace( "[ul]"				, "<ul>"          , $text );
   $text = str_replace( "[/ul]"			, "</ul>"         , $text );

   $text = str_replace( "[li]"				, "<li>"          , $text );
   $text = str_replace( "[/li]"			, "</li>"         , $text );

   $text = str_replace( "[img_left]"			, "<img class='ImageInText_left Style' src='"   , $text );
   $text = str_replace( "[/img_left]"		, "' />"         , $text );

   $text = str_replace( "[img_right]"			, "<img class='ImageInText_right Style' src='"   , $text );
   $text = str_replace( "[/img_right]"		, "' />"         , $text );

   $text = str_replace( "[br]"	  	, "<br>"         , $text );
   return $text;

}



/*	static public function UNhtmlspecialchars($t="")
	{
		$t = str_replace( "&amp;" , "&", $t );
		$t = str_replace( "&lt;"  , "<", $t );
		$t = str_replace( "&gt;"  , ">", $t );
		$t = str_replace( "&quot;", '"', $t );
		$t = str_replace( "&#039;", "'", $t );
		$t = str_replace( "&#39;" , "'", $t );
		$t = str_replace( "&#33;" , "!", $t );
		$t = str_replace( "&#34;" , '"', $t );
		$t = str_replace( "&#036;", '$', $t );
		
		return $t;
	}*/


function DelBR ($text)
{
   $hack_null_mess = '
';
   $text = str_replace( "<br />"				, $hack_null_mess         , $text );
   return $text;
}













function print_page($message, $br = true)
{
   /* Поехали танцы с бубном...
   Танцы с регулярками */

   if ($br)
      $message = nl2br($message);

   $message = str_replace( "[/p]<br />"				, "[/p]"          , $message );
   $message = str_replace( "[/li]<br />"				, "[/li]"          , $message );
   $message = str_replace( "[ul]<br />"				, "[ul]"          , $message );
   $message = str_replace( "[/ul]<br />"				, "[/ul]"          , $message );

   $message = str_replace( "[left]<br />"				, "[left]"          , $message );
   $message = str_replace( "[/left]<br />"				, "[/left]"          , $message );

   $message = str_replace( "[right]<br />"				, "[right]"          , $message );
   $message = str_replace( "[/right]<br />"				, "[/right]"          , $message );

   $message = str_replace( "[center]<br />"				, "[center]"          , $message );
   $message = str_replace( "[/center]<br />"				, "[/center]"          , $message );

   $message = str_replace( "[justify]<br />"				, "[justify]"          , $message );
   $message = str_replace( "[/justify]<br />"				, "[/justify]"          , $message );



   $message = preg_replace("#\[code\](.+)\[\/code\]#isU", '\\hack_left \\1', $message);


   $message = preg_replace("#\[left\](.+)\[\/left\]#isU", '<div align="left">\\1</div>', $message);
   $message = preg_replace("#\[right\](.+)\[\/right\]#isU", '<div align="right">\\1</div>', $message);
   $message = preg_replace("#\[center\](.+)\[\/center\]#isU", '<div align="center">\\1</div>', $message);
   $message = preg_replace("#\[justify\](.+)\[\/justify\]#isU", '<div align="justify">\\1</div>', $message);

   $message = preg_replace("#\[style\](.+)\[\/style\]#isU", '<div class="Style contacts_block">\\1</div>', $message);
   $message = preg_replace("#\[db\](.+)\[\/db\]#isU", '<div class="phone_table_two">\\1</div>', $message);


   $message = preg_replace("#\[ul\](.+)\[\/ul\]#isU", '<ul class="Pages">\\1</ul>', $message);
   $message = preg_replace("#\[li\](.+)\[\/li\]#isU", '<li class="Pages">\\1</li>', $message);
   $message = preg_replace("#\[p\](.+)\[\/p\]#isU", '<p>\\1</p>', $message);
   $message = preg_replace("#\[b\](.+)\[\/b\]#isU", '<b>\\1</b>', $message);
   $message = preg_replace("#\[i\](.+)\[\/i\]#isU", '<i>\\1</i>', $message);
   $message = preg_replace("#\[u\](.+)\[\/u\]#isU", '<u>\\1</u>', $message);
   $message = preg_replace("#\[s\](.+)\[\/s\]#isU", '<s>\\1</s>', $message);
   $message = preg_replace("#\[quote\](.+)\[\/quote\]#isU",'<div class="quoteHead">Цитата</div><div class="quoteContent">\\1</div>',$message);
   $message = preg_replace("#\[quote=&quot;([- 0-9a-zа-яА-Я]{1,30})&quot;\](.+)\[\/quote\]#isU", '<div class="quoteHead">\\1 пишет:</div><div class="quoteContent">\\2</div>', $message);
   $message = preg_replace("#\[url\][\s]*([\S]+)[\s]*\[\/url\]#isU",'<a href="\\1" target="_blank">\\1</a>',$message);

   $message = preg_replace("#\[url=(.+)\](.+)\[\/url\]#isU", '<a href="\\1" target="_blank">\\2</a>', $message);

   $message = preg_replace("#\[img\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<a target="_blank" href="\\1"><img class="ImageInText Style" src="\\1" alt="" /></a>',$message);
   $message = preg_replace("#\[img left\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<a target="_blank" href="\\1"><img class="ImageInText_left Style" src="\\1" alt="" /></a>',$message);
   $message = preg_replace("#\[img right\][\s]*([\S]+)[\s]*\[\/img\]#isU",'<a target="_blank" href="\\1"><img class="ImageInText_right Style" src="\\1" alt="" /></a>',$message);

   $message = preg_replace("#\[color=(.+)\](.+)\[\/color\]#isU", '<span style="color: \\1">\\2</span>', $message);
   $message = preg_replace("#\[size=(.+)\](.+)\[\/size\]#isU", '<span style="font-size: \\1px">\\2</span>', $message);
   return $message;
}

function CheckBan ()
{
   global $SP;
   global $db;
   global $MCCfg;
   $result = $db->query("SELECT * FROM ".($MCCfg->db).".bans WHERE ip='".($SP->ip)."'");
   $cnt = $result->num_rows;

   if ($cnt > 0)
      return true;
   else
      return false;
}


function SendEmailAdmins ($topick, $code)
{
   global $db;
   global $MCCfg;
   $i = 0;
   $result = $db->query("SELECT email FROM ".($MCCfg->db).".admins GROUP BY email", MYSQLI_USE_RESULT);
   while ($row = $result->fetch_array(MYSQLI_ASSOC))
   {
      if ($i > 0)
         $emails .= ", ";

      $emails .= $row['email'];
      $i++;
   }

   SendEmail ($emails, $topick, $code);
}

function SendEmail ($email, $topick, $code)
{
   global $SP;
   $status = mail ($email, $topick, $code, 'From: support@'.($SP->host).'' . "\r\n" .
              'Reply-To: support@'.($SP->host).'' . "\r\n" .
              'Content-type: text/html; charset=UTF-8' . "\r\n" .
              'X-Mailer: PHP/' . phpversion());
   if ($status == true)
      echo "
<!-- Письмо на $email успешно отправлено -->";
   else
      echo "
<!-- Письмо на $email не отправлено -->";
}


function GetURLImage ($name, $ip = '192.168.1.100', $ref = 'http://xn---11-6cdyqb3adjc4adnpen.xn--p1ai/')
{
    global $SP;
    $ip = $SP->ip;
    set_time_limit (0);
    $key = urlencode($name);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://ajax.googleapis.com/ajax/services/search/images?v=1.0&rsz=1&imgsz=large&q=$key&start=0&imgtype=photo&as_filetype=jpg&userip=$ip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_REFERER, $ref);
    $data = curl_exec($ch);
    curl_close($ch);
    $test = json_decode($data, true);    

    return $test['responseData']['results'][0]['tbUrl'];
}

function SetUrlImage ($url, $guid)
{
    global $db, $_DB;
    $db->query("UPDATE $_DB.price SET img='$url', img_l_up='".(time())."' WHERE guid='$guid'");    
}


function SplitAStringOnArray ($text)
{
    $stop = false;
    $i = 0;
    $array_search = array();
    $cnt_start = 0;
    $cnt_stop = 0;

    while (!$stop && $i < 1000)
    {
        $cnt_text = strlen ($text); // Всего букв
        $cnt_stop = stripos ($text, ' '); // счет пробела

        if ($cnt_stop === false)
        {
            if ($text && $text != ' ')
                $array_search[$i] = $text;
            $stop = true;
        }
        else
        {
            $buff_text = substr ($text, $cnt_start, $cnt_stop);;
            if ($buff_text && $buff_text != ' ')
                $array_search[$i] = $buff_text; // Записали мы слово первое в массив
        }
        $text = substr ($text, $cnt_stop + 1, $cnt_text);
        $i++;
    }
    return $array_search;
}

function GetStringSearchMySQL ($text)
{
    $array_search = SplitAStringOnArray ($text);
    if (!$array_search)
        return;

    $text = '';

    foreach ($array_search AS $key => $name)
    {
        $text .= ($key > 0 ? ' OR ' : '') . "name LIKE '%".$array_search[$key]."%' OR about LIKE '%".$array_search[$key]."%'";
    }
    return $text;
}
