<?php

if (!defined("PATH_SEPARATOR")) {
    if (strpos($_ENV[ "OS" ], "Win") !== false )
        define("PATH_SEPARATOR", ";");
    else define("PATH_SEPARATOR", ":");
}

date_default_timezone_set('UTC');

set_include_path('TEXT'.PATH_SEPARATOR.'../TEXT'.PATH_SEPARATOR.'libs'.PATH_SEPARATOR.'libs/getid3');

include('microlabel-config-EDIT_ME.php');

$httpVars= isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']) ? $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'] : '';

$browserPrefs = substr($httpVars,'0','2');
$cookiePrefs = htmlspecialchars($_COOKIE["lang"]);

$cache = true;

global $nocache;

if (isset($_GET['nocache'])) {
    $cache = false;
}

if (!isset($lang) || !empty($lang)) {
    if (isset($_GET['lang']) && !empty($_GET['lang'])) {
        $lang = $_GET['lang'];
        $method = 'get';
        setcookie('lang', $lang, strtotime( '+30 days' ), '/', '', 0);
    } elseif (isset($browserPrefs) && !empty($browserPrefs)) {
        $lang = $browserPrefs;
        $method = 'browser';
    } elseif (isset($cookiePrefs) && !empty($cookiePrefs)) {
        $lang = $cookiePrefs;
        $method = 'cookie';
    } else {
        $method = 'default';
        $lang = 'en';
    }
} else {
    $method = 'fallback';
    $lang = 'en';
}

$textFile = 'lang-'.$lang.'.php';

include($textFile);

$cachefile = './CACHE/'.basename($_SERVER['PHP_SELF'].'-lang-'.$lang.'.'.$_SERVER['QUERY_STRING']);

$cachetime = 365 * 24 * 3600;
// Serve from the cache if it is younger than $cachetime

if ($cache && file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
    include($cachefile);
    echo "<!-- Cached ".date('jS F Y H:i', filemtime($cachefile))." -->";
    exit;
}
ob_start(); // start the output buffer

function microlabelError($text, $suggestion) {
echo '
        <p id="suggestion">'.$suggestion.'</p>
		<div id="horizon">
			<div id="error">
			<img src="/microlabel/img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                '.$text.' :(
			    <div id="back_home">
                <a href="'.MICROLABEL_ROOT_DIR.'">&#8962;</a>
			    </div>
			</div>
		</div>
';
}

?>
