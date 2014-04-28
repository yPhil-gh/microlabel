<?php

////////////////////////////////////////////////////////////////////
// Microlabel copyright 2010-2014 Phil CM <xaccrocheur@gmail.com> //
// licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html       //
// Change the MUSIC directory                                     //
// Change the root label /directory                               //
////////////////////////////////////////////////////////////////////

define('MICROLABEL_MUSIC_DIR', 'MUSIC');
define('MICROLABEL_ROOT_DIR', '/microlabel');

if (!defined("PATH_SEPARATOR")) {
    if (strpos($_ENV[ "OS" ], "Win") !== false )
        define("PATH_SEPARATOR", ";");
    else define("PATH_SEPARATOR", ":");
}

///////////////////////////////////////////////////////////////// no user-serviceable parts below

set_include_path('TEXT'.PATH_SEPARATOR.'../TEXT'.PATH_SEPARATOR.'libs'.PATH_SEPARATOR.'libs/getid3');

$httpVars= isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']) ? $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'] : '';

$browserPrefs = substr($httpVars,'0','2');
$cookiePrefs = htmlspecialchars($_COOKIE["lang"]);

if (!isset($lang) || !empty($lang)) {
    if (isset($_GET['lang']) && !empty($_GET['lang'])) {
        $lang = $_GET['lang'];
        $method = 'get';
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

if (isset($lang)) {
    $textFile = 'lang-'.$lang.'.php';
    setcookie('lang', $lang, strtotime( '+30 days' ), '/', '', 0);
} else {
    $textFile = 'lang-en.php';
}

function microlabelError($text, $suggestion) {
echo '
        <p id="suggestion">'.$suggestion.'</p>
		<div id="horizon">
			<div id="error">
			<img src="/microlabel/img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                '.$text.' :(
			    <div id="back_home">
                <a href="/">&#8962;</a>
			    </div>
			</div>
		</div>
';
}

include($textFile);
?>
