<?php

$db_host	= 'localhost';
$db_name	= '222';
$db_user	= 'root';
$db_pass	= '';
$tb_prefix	= 'media_';
$refreshType = 1;
$setCookieType = 2;

if (!defined('IN_MEDIA')) die("Hacking attempt");
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ob_start();
header("Content-Type: text/html; charset=UTF-8");
if (!session_id()) session_start();

if (!ini_get('register_globals')) {
	@$_GET = $HTTP_GET_VARS;
	@$_POST = $HTTP_POST_VARS;
	@$_COOKIE = $HTTP_COOKIE_VARS;
	extract($_GET);
	extract($_POST);
}

if ($_GET['refresh']) {
	m_refresh();
}


define('NOW',time());
define('IP',$_SERVER['REMOTE_ADDR']);
define('USER_AGENT',$_SERVER['HTTP_USER_AGENT']);
define('CURRENT_LINK',$_SERVER["REQUEST_URI"]);

if (!USER_AGENT || !IP) exit();

function m_refresh() {
	global $refreshType;
	if ($refreshType == 1) {
		if (!$_SESSION['current_page']) $_SESSION['current_page'] = 'Home';
		$_SESSION['is_refresh'] = 1;
		header("Location: ./#".$_SESSION['current_page']);
	}
	else header("Location: ./");
	exit();
}

function m_setcookie($name, $value = '', $permanent = true) {
	global $mainURL, $setCookieType;
	
	$expire = ($permanent)?(time() + 60 * 60 * 24 * 365):0;
	
	if ($setCookieType == 1) {
		$url = $mainURL;
		if ($url[strlen($url)-1] != '/') $url .= '/';
		$secure = (($_SERVER['HTTPS'] == 'on' OR $_SERVER['HTTPS'] == '1') ? true : false);
		$p = parse_url($url);
		$path = !empty($p['path']) ? $p['path'] : '/';
		$domain = $p['host'];
		if (substr_count($domain, '.') > 1) {
			while (substr_count($domain, '.') > 1)
			{
				$pos = strpos($domain, '.');
				$domain = substr($domain, $pos + 1);
			}
			
		}
		else $domain = '';
		@setcookie($name, $value, $expire, $path, $domain, $secure);
	}
	else @setcookie($name,$value,$expire);
}

function m_get_config($name) {
	global $mysql,$tb_prefix,$cached;
	if (!$cached['config'][$name]) {
		$r = $mysql->fetch_array($mysql->query("SELECT config_value FROM ".$tb_prefix."config WHERE config_name = '".$name."'"));
		$cached['config'][$name] = stripslashes($r['config_value']);
	}
	return $cached['config'][$name];
}

include('dbconnect.php');

$mainURL	= m_get_config('web_url');
if ($mainURL[strlen($mainURL)-1] == '/') $mainURL = substr($mainURL,0,-1);
$webTitle	= m_get_config('web_title');
$cached = array();
$q = '';
$mediaFolder = m_get_config('server_url').'/'.m_get_config('server_folder');


if (!$_COOKIE['SID']) {
	$sid = md5(session_id());
	m_setcookie('SID',$sid);
	define('SID',$sid);
	unset($sid);
}
else define('SID',$_COOKIE['SID']);

?>