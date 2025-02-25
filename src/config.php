<?php

set_time_limit(0);

if(ini_get("date.timezone") == ""){ //No Timezone set
	date_default_timezone_set("GMT");
	if(str_contains(" " . strtoupper(php_uname("s")), " WIN")){
		$time = time();
		$time -= $time % 60;
		//Example: USA
		exec("time.exe /T", $hour);
		$i = array_map("intval", explode(":", trim($hour[0])));
		exec("date.exe /T", $date);
		$j = array_map("intval", explode(substr($date[0], 2, 1), trim($date[0])));
		$offset = round((mktime($i[0], $i[1], 0, $j[1], $j[0], $j[2]) - $time) / 60) * 60;
	}else{
		exec("date +%s", $t);
		$offset = round((intval(trim($t[0])) - time()) / 60) * 60;
	}

	$daylight = (int) date("I");
	$d = timezone_name_from_abbr("", $offset, $daylight);
	@ini_set("date.timezone", $d);
	date_default_timezone_set($d);
}else{
	$d = @date_default_timezone_get();
	if(!str_contains($d, "/")){
		$d = timezone_name_from_abbr($d);
		@ini_set("date.timezone", $d);
		date_default_timezone_set($d);
	}
}

gc_enable();
error_reporting(E_ALL | E_STRICT);
ini_set("allow_url_fopen", 1);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
ini_set("default_charset", "utf-8");
if(defined("POCKETMINE_COMPILE") and POCKETMINE_COMPILE === true){
	define("FILE_PATH", realpath(dirname(__FILE__)) . "/");
}else{
	define("FILE_PATH", realpath(dirname(__FILE__) . "/../") . "/");
}
set_include_path(get_include_path() . PATH_SEPARATOR . FILE_PATH);

ini_set("memory_limit", "256M"); //Default
define("LOG", true);
define("START_TIME", microtime(true));
define("MAJOR_VERSION", "1.1.0_01");
define("CODENAME", "懐かしさ (Nostalgia)"); //i'm not very creative - kotyaralih
define("CURRENT_MINECRAFT_VERSION", "v0.8.1 alpha");
define("CURRENT_API_VERSION", '12.1');
define("CURRENT_PHP_VERSION", "8.0");
$gitsha1 = false;
if(file_exists(FILE_PATH . ".git/refs/heads/master")){ //Found Git information!
	define("GIT_COMMIT", strtolower(trim(file_get_contents(FILE_PATH . ".git/refs/heads/master"))));
}else{ //Unknown :(
	define("GIT_COMMIT", str_repeat("00", 20));
}
