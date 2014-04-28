<?php

////////////////////////////////////////////////////////////////////
// Microlabel copyright 2010-2014 Phil CM <xaccrocheur@gmail.com> //
// licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html       //
// Change the MUSIC directory                                     //
// Change the root label /directory                               //
////////////////////////////////////////////////////////////////////

function getMusicRoot() {
    return 'MUSIC';
}

function getLabelRoot() {
    return '/microlabel';
}

///////////////////////////////////////////////////////////////// no user-serviceable parts below

$httpVars= isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']) ? $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'] : '';

$browserPrefs = substr($httpVars,'0','2');

$cookiePrefs = htmlspecialchars($_COOKIE["lang"]);

$timeFormula = 3600;

if (!isset($lang) || !empty($lang)) {
    if (isset($_GET['lang']) && !empty($_GET['lang'])) {
        $lang = $_GET['lang'];
    } elseif (isset($browserPrefs) && !empty($browserPrefs)) {
        $lang = $browserPrefs;
    } elseif (isset($cookiePrefs) && !empty($cookiePrefs)) {
        $lang = $cookiePrefs;
    } else {
        $lang = 'en';
    }
} else {
    $lang = 'en';
}

if (isset($lang)) {
    $textFile = 'lang-'.$lang.'.php';
    setcookie("lang", $lang, time() + $timeFormula);
} else {
    $textFile = 'lang-en.php';
}

include($textFile);


if (!defined("PATH_SEPARATOR")) {
    if ( strpos( $_ENV[ "OS" ], "Win" ) !== false )
        define( "PATH_SEPARATOR", ";" );
    else define( "PATH_SEPARATOR", ":" );
}

if (!defined("FILE_SEPARATOR")) {
    if ( strpos( $_ENV[ "OS" ], "Win" ) !== false )
        define( "FILE_SEPARATOR", "\\" );
    else define( "FILE_SEPARATOR", "/" );
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
                <a href="'.getLabelRoot().'/">&#8962;</a>
			    </div>
			</div>
		</div>
';
}

?>
