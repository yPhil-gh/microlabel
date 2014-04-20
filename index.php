<?php

    if (!isset($_GET['debug']) && !isset($_SERVER['QUERY_STRING'])) {
        $cachefile = 'cache/'.basename($_SERVER['SCRIPT_URI']);
        if ($_SERVER['QUERY_STRING']!='') {
            $cachefile .= '_'.base64_encode($_SERVER['QUERY_STRING']);
        }
        /* $cachetime = 120 * 60; // 2 hours */
        $cachetime = 120 * 60 * 10;
        // Serve from the cache if it is younger than $cachetime
        if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile))) {
            $thisPageIsCached = TRUE;
            include($cachefile);
            exit;
        }
        else {
            $thisPageIsCached = FALSE;
            // continue;
        }
    }


if (isset($_GET['lang'])) {
    if ($_GET['lang']=='en') {
        $lang = 'en';
        include('lang-en.php');
        setcookie("lang", $lang, time() + 365*24*3600);
        //           header("Location: $HTTP_REFERER");
        // header('Location: '. $_SERVER['PHP_SELF']);
        //    exit();
    }
    if ($_GET['lang']=='fr') {
        $lang = 'fr';
        include('lang-fr.php');
        setcookie("lang", $lang, time() + 365*24*3600);
    }
    if ($_GET['lang']=='es') {
        $lang = 'es';
        include('lang-es.php');
        setcookie("lang", $lang, time() + 365*24*3600);
    }
}

ob_start(); // C'est parti !

/////////////////////////////////////////////////////////////////
//  Listen & Download copyleft Philippe - pX - Coatmeur-Marin  //
//               <hallucinet@online.fr>                        //
//  licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html   //
//          Because all music should be free                   //
//  Please don't harm nobody w/ this code even if they ask to  //
/////////////////////////////////////////////////////////////////

require_once('libs/getID3/getid3/getid3.php');


//$labelName = 'beldigital';

function getRoot() {
    $rootMusicDir = 'MUSIC';
    return $rootMusicDir;
}

////////////////////////////// no user-serviceable parts below //

$rootMusicDir = trim(getRoot());

/* Exclude directories from iterator */
class ExcludeDotDirsFilterIterator extends FilterIterator {
    public function accept()  {
        $fileinfo = $this->getInnerIterator()->current();
        /* if (preg_match("/^[.]/", $fileinfo)) { */
        if (preg_match(":/tmp/:", $fileinfo)) {
            return false;
        }
        return true;
    }
}

function getInfo($startPath, $element) {
    //    $musicdirs = array();
    $thisAlbumSleeves = array();
    $getID3 = new getID3;
    $sp = '&nbsp;';
    $ch = 'comments_html';
    //    $labelName=getLabelName();
    if (is_file($startPath)) {
        $fs = pathinfo($startPath);
        if ($fs['extension'] == 'ogg') {
            $f = $getID3->analyze($startPath);
            getid3_lib::CopyTagsToComments($f);
            $thisFileTitleTag = (!empty($f[$ch]['title']) ? implode($sp, $f[$ch]['title'])  : $sp);
            $thisFileArtistTag = (!empty($f[$ch]['artist']) ? implode($sp, $f[$ch]['artist'])  : $sp);
            $thisFileAlbumTag = (!empty($f[$ch]['album']) ? implode($sp, $f[$ch]['album'])  : $sp);
            $thisFileYearTag = (!empty($f[$ch]['year']) ? implode($sp, $f[$ch]['year'])  : $sp);
            /* $thisFileTrackTag = (!empty($f[$ch]['track']) ? implode($sp, $f[$ch]['track'])  : $sp); */

            if (empty($f[$ch]['track'])) {
                $thisFileTrackTag =  (!empty($f[$ch]['tracknumber']) ? implode($sp, $f[$ch]['tracknumber'])  : "plop");
            } else {
                $thisFileTrackTag = (!empty($f[$ch]['track']) ? implode($sp, $f[$ch]['track'])  : "plip");
            }

            /* $thisFileTrackNumberTag = (!empty($f[$ch]['tracknumber']) ? implode($sp, $f[$ch]['tracknumber'])  : $sp); */
            $thisFileGenreTag = (!empty($f[$ch]['genre']) ? implode($sp, $f[$ch]['genre'])  : $sp);
            $thisFileOrganizationTag = (!empty($f[$ch]['organization']) ? implode($sp, $f[$ch]['organization'])  :$sp);
            $thisFileCommentTag = (!empty($f[$ch]['comment']) ? implode($sp, $f[$ch]['comment'])  : $sp);
            $thisFilePlayTime = (!empty($f['playtime_string']) ? $f['playtime_string'] : $sp);
            $thisFileSize = (!empty($f['filesize']) ? bytestostring( $f['filesize'], 2 ) : $sp);
            $thisFileBitRate = (!empty($f['audio']['bitrate']) ? round($f['audio']['bitrate'] / 1000).' kbps' : $sp);

            $thisFileTags = array('artist' => $thisFileArtistTag, 'title' => $thisFileTitleTag, 'album' => $thisFileAlbumTag, 'year' => $thisFileYearTag, 'track' => $thisFileTrackTag, 'genre' => $thisFileGenreTag, 'comment' => $thisFileCommentTag, 'playtime' => $thisFilePlayTime, 'size' => $thisFileSize, 'bitrate' => $thisFileBitRate, 'organization' => $thisFileOrganizationTag);
        }
    } else {
        $iterator = new ExcludeDotDirsFilterIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($startPath), RecursiveIteratorIterator::SELF_FIRST));

        $pathNames = array();
        $thisAlbumTags = array();
        foreach ($iterator as $key => $fileObj) {
            $pathNames[] = $fileObj->getPathname();
            $fileNames[] = $fileObj->getFilename();
            $fileTypes[] = $fileObj->getType();
        }

        foreach ($pathNames as $key => $filePath) {
            $infos = pathinfo($filePath);


            if ($infos['extension'] == 'ogg') {
                /* $thisMusicFileinfos = pathinfo($filePath); */
                $musicDirs[] = $infos['dirname'];

                $f = $getID3->analyze($filePath);
                getid3_lib::CopyTagsToComments($f);
                $thisAlbumTitleTag = (!empty($f[$ch]['title']) ? implode($sp, $f[$ch]['title'])  : $sp);
                $thisAlbumTitleTags[] = $thisAlbumTitleTag;
                $thisAlbumArtistTag = (!empty($f[$ch]['artist']) ? implode($sp, $f[$ch]['artist'])  : $sp);
                $thisAlbumAlbumTag = (!empty($f[$ch]['album']) ? implode($sp, $f[$ch]['album'])  : $sp);
                $thisAlbumYearTag = (!empty($f[$ch]['year']) ? implode($sp, $f[$ch]['year'])  : $sp);
                $thisAlbumYearTags[] = $thisAlbumYearTag;

                if (empty($f[$ch]['track'])) {
                    $thisAlbumTrackTag =  (!empty($f[$ch]['tracknumber']) ? implode($sp, $f[$ch]['tracknumber'])  : "plop");
                } else {
                    $thisAlbumTrackTag = (!empty($f[$ch]['track']) ? implode($sp, $f[$ch]['track'])  : "plip");
                }

                /* $thisAlbumTrackTag = (!empty($f[$ch]['track']) ? implode($sp, $f[$ch]['track'])  : $sp); */
                $thisAlbumTrackTags[] = $thisAlbumTrackTag;
                /* $thisAlbumTrackNumberTag = (!empty($f[$ch]['tracknumber']) ? implode($sp, $f[$ch]['tracknumber'])  : $sp); */
                /* $thisAlbumTrackNumberTags[] = $thisAlbumTrackNumberTag; */
                $thisAlbumGenreTag = (!empty($f[$ch]['genre']) ? implode($sp, $f[$ch]['genre'])  : $sp);
                $thisAlbumGenreTags[] = $thisAlbumGenreTag;
                $thisAlbumCommentTag = (!empty($f[$ch]['comment']) ? implode($sp, $f[$ch]['comment'])  : $sp);
                $thisAlbumCommentTags[] = $thisAlbumCommentTag;
                $thisAlbumOrganizationTags = (!empty($f[$ch]['organization']) ? implode($sp, $f[$ch]['organization']) : $sp);
                $thisAlbumGenreTags[] = $thisAlbumGenreTag;
                $musicFiles[$filePath] = $thisMusicFileinfos['basename'];

                $thisAlbumTags[title] = $thisAlbumTitleTags;
                $thisAlbumTags[artist] = $thisAlbumArtistTag;
                $thisAlbumTags[album] = $thisAlbumAlbumTag;
                $thisAlbumTags[year] = $thisAlbumYearTags;
                $thisAlbumTags[track] = $thisAlbumTrackTags;
                /* $thisAlbumTags[tracknumber] = $thisAlbumTrackNumberTags; */
                $thisAlbumTags[genre] = $thisAlbumGenreTags;
                $thisAlbumTags[organization] = $thisAlbumOrganizationTags;
                $thisAlbumTags[comment] = $thisAlbumCommentTags;
                //                sort($musicDirs);
            }

            elseif ($infos['extension'] == 'jpg' || $infos['extension'] == 'png'  || $infos['extension'] == 'gif') {
                $pos = strpos($infos['filename'], 'bg-');
                if ($pos === false) {
                    $thisAlbumSleeve = $filePath;
                    $thisAlbumSleeves[] = $thisAlbumSleeve;
                } else {
                    $thisAlbumBackgroundImages[] = $filePath;
                }
            }
        }
    }

    sort($thisAlbumSleeves);

    switch ($element) {
    case 'titles':
        return $titleTags;
        break;
    case 'numberOfSongs':
        return count($titleTags);
        break;
    case 'musicDirs':
        return array_values(array_unique($musicDirs));
        break;
    case 'musicFiles':
        return $musicFiles;
        break;
    case 'thisFileTags':
        return $thisFileTags;
        break;
    case 'thisAlbumTags':
        return $thisAlbumTags;
        break;
    case 'thisAlbumSleeves':
        //        $thisAlbumSleeves;
        if(!empty($thisAlbumSleeve)) {
            return $thisAlbumSleeves;
        }
        else {
            $thisAlbumSleeves[0] = 'img/beldigital_logo_off.png';
            return $thisAlbumSleeves;
            //            return 'img/beldigital_logo_off.png';
        }
        break;

    case 'thisAlbumBackgroundImages':
        return $thisAlbumBackgroundImages;
        break;


        break;
    case 'thisAlbumSleeve':

        if(!empty($thisAlbumSleeve)) {
            return $thisAlbumSleeves[0];
        }
        else {
            return 'img/beldigital_logo_off.png';
        }
        break;
    }
}

$myDumbVar=getInfo($rootMusicDir, musicDirs);

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$starttime = $mtime;

// Permet la lecture du source a volee, cool huh ?
if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); }

$browserPrefs = substr($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'],0,2);
$cookiePrefs = $HTTP_COOKIE_VARS['lang'];

// $expire = 365*24*3600;

if (isset($cookiePrefs)) {
    if ($cookiePrefs == 'en') {
        $lang = 'en';
        include('lang-en.php');
    }
    if ($cookiePrefs == 'fr') {
        $lang = 'fr';
        include('lang-fr.php');
    }
    if ($cookiePrefs == 'es') {
        $lang = 'es';
        include('lang-es.php');
    }
}

else {
    if (isset($browserPrefs)) {
        if ($browserPrefs == 'en') {
            $lang = 'en';
            include('lang-en.php');
        }
        if ($browserPrefs == 'fr') {
            $lang = 'fr';
            include('lang-fr.php');
        }
        if ($browserPrefs == 'es') {
            $lang = 'es';
            include('lang-es.php');
        }
    }
    include('lang-fr.php');
}

////////////////////////////////

$script = $SCRIPT_NAME;

function xmlInfos($element) {
    $thisAlbumPath = browse(current, mean);

    $file = $thisAlbumPath.'/info.xml';
    $xml = simplexml_load_file($file);

    if (count($xml->video) > 0) {
        foreach ($xml->video as $video) {
            $videoObjects[] = $video;
        }
    }

    if (count($xml->musicien) > 0) {
        foreach ($xml->musicien as $musicien) {
            foreach ($musicien->twitter as $twitter) {
                $myTwitters[] = $twitter;
            }
        }
    }

    /* echo $myTwitters[0]; */

    switch ($element) {
    case 'first_twitter':
        echo $myTwitters[0];
        break;
    case 'all_twitters':
        return $myTwitters;
        break;
    case 'videos_object':
        return $videoObjects;
        break;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
    <style type="text/css" media="screen">@import "css/player.css";</style>
<link type='text/css' href='css/osx.css' rel='stylesheet' media='screen' />

    <style type="text/css" media="screen">@import "css/style.css";</style>
<style type="text/css" media="screen">@import "css/colorbox.css";</style>
<link rel="alternate stylesheet" type="text/css" href="css/msk_RED.css" title="RED" />

    <script src="libs/msk_css_switcher.js"></script>
    <script type="text/javascript" src="libs/jquery-1.5.1.min.js"></script>
    <script type="text/javascript" src="libs/jquery.livetwitter.min.js"></script>
    <script src="libs/jquery.colorbox-min.js"></script>

    <script src='libs/jquery.simplemodal.js'></script>
    <script src='libs/osx.js'></script>

    <!--[if lt IE 9]>
    <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
    <![endif]-->

    <script src="libs/player.js"></script>


    <script>

    function loadjscssfile(filename, filetype){
        if (filetype=="js"){ //if filename is a external JavaScript file
            var fileref=document.createElement('script')
            fileref.setAttribute("type","text/javascript")
            fileref.setAttribute("src", filename)
        }
        else if (filetype=="css"){ //if filename is an external CSS file
            var fileref=document.createElement("link")
            fileref.setAttribute("rel", "stylesheet")
            fileref.setAttribute("type", "text/css")
            fileref.setAttribute("href", filename)
        }
        if (typeof fileref!="undefined")
            document.getElementsByTagName("head")[0].appendChild(fileref)
                }

var test_audio= document.createElement("audio") //try and create sample audio element
    var test_video= document.createElement("video") //try and create sample video element
    var mediasupport={audio: (test_audio.play)? true : false, video: (test_video.play)? true : false}

    if(mediasupport.audio == false) {
        loadjscssfile("css/msk-no-audio.css", "css")
    }

if (songToPlay !== undefined) {
    /* nextClicked() */
    load_track(songToPlay);
    plop = songToPlay;
    playAudio();
}

var songToPlay = "<?php
$zong = strip_tags($_GET['s']);
if (!empty($zong)) {
    echo $zong-1;
} else {
    echo "undefined";
}

?>";

$(document).ready(function() {
    $(".youtube").colorbox({iframe:true, innerWidth:425, innerHeight:344});
    var plopA = '<? echo TXT_NO_AUDIO_TXT ?>';

    var noAudioMsg = '<h2 class="no-audio-alert">Wow.</h2>'+
        '<hr /><h4 class="no-audio-alert">Audio : '+mediasupport.audio+'</h4>' +
        '<h4 class="no-audio-alert">Video : '+mediasupport.video+'</h4>' +
        ' <h3 class="no-audio-alert">'+navigator.appName+' '+navigator.appVersion+'</h3>' +
        plopA +
        '<hr /><a href="http://www.mozilla.com/firefox/"><img src="img/logo-ffox.jpg" /></a> <img src="img/logo-opera.png" /> <img src="img/logo-safari.png" /> <img src="img/logo-chrome.jpg" /></p>'

    if (mediasupport.audio == false) {
        $.prompt(noAudioMsg);
    }

    var twittoz = "<?php
xmlInfos(first_twitter);
//echo 'plop';
?>";

    /* alert(twittoz); */
    /* $("#tweets").liveTwitter("Azer0o0", {limit: 5, imageSize: 32}); */

    $('#tweets').liveTwitter(twittoz, {limit: 5, imageSize: 32}, function(){
        $('#tweets .loading').remove();
    });

    $('#searchLinks a').each(function(){
        var query = $(this).text();
	$(this).click(function(){
            // Update the search
            $('#tweets').liveTwitter(query).each(function(){
                this.twitter.clear();
            });
	    // Update the header
	    $('#searchTerm').text(query);
	    return false;
        });
    });

    $('a.sleeve').colorbox({
        rel:"group1",
        slideshow:true,
        transition:"elastic",
        speed:"450",
        maxWidth:"95%",
        maxHeight:"95%",
        current:"{current} / {total}",
        slideshowSpeed:30000,
        slideshowStart:"start",
        slideshowStop:"stop",
        close:"esc"
    });

    function pulse() {
        $('.fadeAlbums').fadeIn(210);
        $('.fadeAlbums').fadeOut(3000);
    }
    setInterval(pulse, 8000);


    onsimoPlayerLoad();

    var txt_play = "<?php
echo TXT_PLAY
?>";

    if (songToPlay !== undefined) {
        /* nextClicked() */
        load_track(songToPlay);
        //            loaded_index = songToPlay;
        plop = songToPlay;
        playAudio();
    }

});

$(document).keydown(function(e){
    if(e.which == 32) {
        e.preventDefault();
        pauseClicked();
    }
    if(e.which == 37) {
        e.preventDefault();
        previousClicked();
    }
    if(e.which == 39) {
        e.preventDefault();
        nextClicked();
    }
});


</script>

<link rel="shortcut icon" href="favicon.ico" />

<?php


    $dirList = getInfo($rootMusicDir, musicDirs);

//spitTitle($dirList, $fileList)////////////////////////////////////////
// Print album Sleeve if any
// Affiche la pochette de l'album si on trouve une image, et le titre de l'album

function spitTitle($dirList, $fileList) {

    $thisAlbumPath = browse(current, mean);
    $thisAlbumTags = getInfo($thisAlbumPath, thisAlbumTags);
    $thisAlbumSleeves = getInfo($thisAlbumPath, thisAlbumSleeves);

    $artistName = $thisAlbumTags[artist];
    $albumName = $thisAlbumTags[album];
    $albumGenre = $thisAlbumTags[genre];
    $albumYear = $thisAlbumTags[year];
    $albumLabel = $thisAlbumTags[organization];

    $genres = array_unique($albumGenre);
    $years = array_unique($albumYear);

    foreach ($genres as $key => $value) {
        if (is_null($value) || $value == "" || $value == " " || $value == "&nbsp;") {
            unset($genres[$key]);
        }
    }

    if (is_array($genres)) {
        $genre = implode(", ", $genres);
    } else {
        $genre = $albumGenre;
    }

    if (count($years) > 1) {
        sort($years);
        foreach ($years as $key) {
            $earliest = $years[0];
            $latest =  $years[count($years)-1];
            $year = $earliest.' => '.$latest;
        }
    } else {
        $year = $years[0];
    }

    $thisAlbumBackgroundImages = getInfo($thisAlbumPath, thisAlbumBackgroundImages);

    echo '
<title>'.$artistName. ' "'.$albumName.'" ('.$albumLabel.')</title>
<link rel="shortcut icon" type="image/x-icon" href="'.str_replace(" ", "%20", $thisAlbumSleeves[0]).'" />


<style type="text/css">
html, body {
    background: #000528 url('.$thisAlbumBackgroundImages[0].') repeat fixed 0 0;
}
</style>

        ';


    echo '
</head>
<body>
<div id="main">
  <div class="content">

    <div id="header" class="main extremities">
      <div class="left">
        <div class="artistName">'.$artistName.'</div>
        <div class="albumName">'.$albumName.'</div>
        <div class="index_meta">
          <!-- <p class="index_meta not_important">'.TAGS_GENRE.' : '.$genre.' '.TAGS_YEAR.' = '.$year.'</p> -->
          <!--p>'.$presentation.'</p-->
          <p id="trackComment"></p>
        </div>
      </div>
      <div class="middle" style="font-size:8px;">
';

    echo '&nbsp;</div>
      <div id="sleeve">
        <div class="right">
          <div class="sleeves">
';

    if (count($thisAlbumSleeves) > 1) {
        foreach ($thisAlbumSleeves as $key => $value) {
            $thisAlbumSleeve = str_replace(" ", "%20", $value);
            $path_parts = pathinfo($thisAlbumSleeve);
            $thisAlbumSleeveFileName = $path_parts['filename'].'.'.$path_parts['extension'];
            echo '
<a href="'.$thisAlbumSleeve.'" title="'.$thisAlbumSleeveFileName.'" class="sleeve"><img src="'.$thisAlbumSleeve.'" alt="'.$thisAlbumSleeveFileName.'" /></a>
';
        }
    } else {
        $path_parts = pathinfo($thisAlbumSleeves[0]);
        $thisAlbumSleeveFileName = $path_parts['filename'].'.'.$path_parts['extension'];
        echo '
        <a href="'.str_replace(" ", "%20", $thisAlbumSleeves[0]).'" title="'.TXT_DOWNLOAD.' '.$thisAlbumSleeveFileName.'" class="sleeve"><img style="height:100%" src="'.str_replace(" ", "%20", $thisAlbumSleeves[0]).'" alt="'.$thisAlbumSleeveFileName.'" /></a>
';

    }
    echo '
          </div>
        </div>
      </div>
    </div>
';
    echo "\n";
    echo "\n";
}


function getTinyUrl($url) {
    $tinyurl = file_get_contents("http://tinyurl.com/api-create.php?url=".$url);
    return $tinyurl;
    //     return "plop";
}

// audioList($fileList) ////////////////////////////////////////
// Build audio content table
// Tableau de chansons

function audioList($fileList, $albumPath) {

    //    Pour le browse() dans le player
    //   $dirList = getInfo("MUSIC/", musicDirs);

    $script = $_SERVER["SCRIPT_URI"];

    $i = 0;
    $z = $i+1;
    $numberOfSongs = 0;

    $thisAlbumTags = getInfo($albumPath, thisAlbumTags);

    foreach ($fileList as $fullFileName => $myFileName) {
        $thisFileTags = getInfo($fullFileName, thisFileTags);
        $trackNumbers[] = $thisFileTags[track];
        $trackTitles[$fullFileName] = $thisFileTags[title];
        $track['url'] = $fullFileName;
        $numberOfSongs++;
    }

    $genres = array_unique($albumGenre);
    $years = array_unique($albumYear);

    foreach ($genres as $key => $value) {
        if (is_null($value) || $value == "" || $value == " " || $value == "&nbsp;") {
            unset($genres[$key]);
        }
    }

    if (is_array($genres)) {
        $genre = implode(", ", $genres);
    } else {
        $genre = $albumGenre;
    }

    if (count($years) > 1) {
        sort($years);
        foreach ($years as $key) {
            $earliest = $years[0];
            $latest =  $years[count($years)-1];
            $year = $earliest.' => '.$latest;
        }
    } else {
        $year = $years[0];
    }


    echo '
        <ul id="simoPlayer">
        ';


    ksort($trackTitles);

    foreach ($trackTitles as $fullFileName => $trackTitle) {
        $thisFileTags = getInfo($fullFileName, thisFileTags);

        $artistName = $thisAlbumTags[artist];
        $albumName = $thisAlbumTags[album];
        $albumGenre = $thisAlbumTags[genre];
        $albumRecordLabel = $thisFileTags[organization];
        $albumYear = $thisAlbumTags[year];

        $artistName = $thisFileTags[artist];
        $albumName = $thisFileTags[album];
        $albumGenre = $thisFileTags[genre];
        $albumYear = $thisFileTags[year];
        $trackTitle = $thisFileTags[title];
        $trackPlayTime = $thisFileTags[playtime];
        $trackFileSize = $thisFileTags[size];
        $trackBitRate = $thisFileTags[bitrate];
        $trackNumber = $thisFileTags[track];
        $trackComment = $thisFileTags[comment];

        $thisFilePathElements = explode("/", $fullFileName);
        $thisFileNicePath = $thisFilePathElements[count($thisFilePathElements)-3].",".$thisFilePathElements[count($thisFilePathElements)-2];
        $thisFileObfuscatedPath = $thisFilePathElements[count($thisFilePathElements)-3]."/".$thisFilePathElements[count($thisFilePathElements)-2]."/".$thisFilePathElements[count($thisFilePathElements)-1];

        $fileName = $thisFilePathElements[count($thisFilePathElements)-1];

        /* $safeLink = urlencode('http://'.$host.$script.'?a='.$thisFileNicePath.'&s='.$z); */

        $host = $_SERVER["HTTP_HOST"];
        $script = $_SERVER["PHP_SELF"];

        $pos = strpos($_SERVER["REQUEST_URI"], "/?");

        $myScriptPath = pathinfo($_SERVER["SCRIPT_NAME"]);
        $myDir = $myScriptPath['dirname'];
        // Note our use of ===.  Simply == would not work as expected
        // because the position of 'a' was the 0th (first) character.
        if ($pos === false) {
            $unSafeLink = 'http://'.$host.$script.'?a='.$thisFileNicePath.'&s='.$z++;
        } else {
            $unSafeLink = 'http://'.$host.$myDir.'/?a='.$thisFileNicePath.'&s='.$z++;
        }

        /* $unSafeLink = 'http://'.$host.$script.'?a='.$thisFileNicePath.'&s='.$z++; */
        /* $safeLink = urlencode($unSafeLink); */
        $shortLink = getTinyUrl($unSafeLink);

        echo '
        <li>
          <h3>'.$trackTitle.'</h3>
          <h4 class="albumName">'.$artistName.' - '.$albumName.'</h4>
	  <p class="invisible_if_no_audio">
	    <span class="opaque invisible_if_no_audio"></span>
            <span class="albumName desc">'.$artistName.' - '.$albumName.'</span>
	    <span class="leftSpan desc">'.TAGS_RECORD_LABEL.' : </span><span class="rightSpan desc">'.$albumRecordLabel.'</span><br />
	    <span class="leftSpan desc">'.TAGS_TRACK.'s : </span><span class="rightSpan desc">'.$numberOfSongs.'</span>
	    <span class="leftSpan desc">'.TAGS_YEAR.' : </span><span class="rightSpan desc">'.$albumYear.'</span>
	    <span class="leftSpan desc">'.TAGS_GENRE.' : </span><span class="rightSpan desc">'.$albumGenre.'</span>
          </p>
          <h5>'.$script.'?a='.browse(next, nice).'</h5>
          <h1 class="invisible_if_no_audio">'.$trackComment.'</h1>

<div class="invisible_if_no_audio">
	    <span class="opaque invisible_if_no_audio"></span>
            <h4 class="albumName">'.$artistName.' - '.$albumName.'</h4>
            <p>'.$trackTitle.'</p>
	    <p><span class="leftSpan">'.TAGS_YEAR.' : </span><span class="rightSpan">'.$albumYear.'</span></p>
            <p><span class="leftSpan">'.TAGS_PLAYTIME.' : </span><span class="rightSpan">'.$trackPlayTime.'</span></p>
            <p><span class="leftSpan">'.TAGS_BITRATE.' : </span><span class="rightSpan">'.$trackBitRate.'</span></p>
            <p><span class="leftSpan">'.TAGS_SIZE.' : </span><span class="rightSpan">'.$trackFileSize.'</span></p>
            <p><span class="leftSpan">'.TAGS_RECORD_LABEL.' : </span><span class="rightSpan">'.$albumRecordLabel.'</span></p>
';

        $tes = rtrim( $trackComment );

        if (!empty($tes) && ($trackComment !== "&nbsp;" )) {
            echo '<blockquote><span class="bqstart">“</span>'.$trackComment.'<span class="bqend">”</span></blockquote>';
        }

        echo '
            <form class="urlFields">
              <input type="text" class="loongURL" size="20" name="'.TAGS_SHARE.'" onClick="this.select();" value="'.$unSafeLink.'"><br/>
              <input type="text" class="shortURL" size="20" onClick="this.select();" value="'.$shortLink.'" />
            </form>
            <div class="songMenu">
              <div class="div1"><img src="img/icon_download.png" alt="'.TXT_DOWNLOAD.'" /><a href="'. $fullFileName .'" title="' . $trackTitle.' ('.TXT_DOWNLOAD.')">'.TXT_DOWNLOAD.'</a></div>
              <div class="div2"><img src="img/icon_love.png" alt="'.TXT_BUY.'" /><a href="#">'.TXT_BUY.'</a></div>
            </div>
          </div>
          <a class="downloadTuneLink" href="'.$fullFileName.'">'.TXT_DOWNLOAD.' '.$trackTitle .'</a>
        </li>
            ';

        // Feed the tracklist
        $audioFile[$trackTitle] = $fileName;

    } // End foreach
    echo '
        </ul>
    <div id="tweets"></div>
<p id="searchLinks">
';

    $tweeters = xmlInfos(all_twitters);

    foreach ($tweeters as $tweeter) {
        echo '<a href="#">'.$tweeter.'</a> ';
    }


    echo '
</p>
</div>
';



    echo '
        <script type="text/javascript">
          //set some PagePlaer variables
          audio_volume=0.85; //default is 0.7

          simoPlayer("simoPlayer");
          /*what happens when the page is loaded?
          please see the "body" tag
          */
        </script>
        <div id="message"></div>
    ';
}

function videoList($fileList, $albumPath) {

    $videos_objects = xmlInfos(videos_object);

    foreach($videos_objects as $videos_object) {
        $videoName = $videos_object->name;
        $videoID = $videos_object->youtubeid;
        echo '
<div id="videos">
<p class="video"><a class="youtube" href="http://youtube.com/embed/'.$videoID.'" title="'.$videoName.'">'.$videoName.'</a>
<img class="video_thumbnail" src="http://img.youtube.com/vi/'.$videoID.'/2.jpg" />
</p>
</div>

';
    }
}


// index($dirList) ////////////////////////////////////////
// Build label "home" index page with all the CD Sleeves
// Construit la page d'accueil en listant tous les albums

function index($dirList, $labelName) {
    $numberOfAlbums = count($dirList);
    echo '<title>'.$labelName.' - Free Music</title>
    <style type="text/css" media="screen">@import "css/contentflow.css";</style>
    <script>

var numberOfAlbums = '.$numberOfAlbums.'
var randomNumber=Math.floor(Math.random()*numberOfAlbums);

var myNewFlow = new ContentFlow("albumsRotator", {
    //reflectionHeight: 5,
    //circularFlow: false,
    startItem:randomNumber
});
        </script>
    </head>
    ';

    echo '
    <body>
   <div class="maincontent">
<div id="albumsRotator" class="ContentFlow">
        <!-- should be place before flow so that contained images will be loaded first -->
        <div class="loadIndicator"><div class="indicator"></div></div>
          <div class="flow">
    ';

    foreach ($dirList as $key => $albumPath) {
        $thisAlbumTags = getInfo($albumPath, thisAlbumTags);

        $artistName = $thisAlbumTags[artist];
        $albumName = $thisAlbumTags[album];
        $albumGenre = $thisAlbumTags[genre];
        $albumYear = $thisAlbumTags[year];
        $albumYear = $thisAlbumTags[year];

        $labelName = $thisAlbumTags[organization];

        $album = ltrim(ltrim($album, '.'), '/');
        $newAlbumSexyUrlElements = explode("/", $dirList[$key]);
        $newAlbumSexyUrl = $newAlbumSexyUrlElements[1].",".$newAlbumSexyUrlElements[2];
        $thisAlbumSleeve = getInfo($albumPath, thisAlbumSleeve);

        echo '
            <div class="item">
                <img class="content" src="'.$thisAlbumSleeve.'" href="'.$script.'?a='.$newAlbumSexyUrl.'"/>
                     <a style="display:none" href="'.$script.'?a='.$newAlbumSexyUrl.'">'.$albumName.'</a>
                <div class="caption">'.$artistName.' - '.$albumName.' ('.$labelName.')</div>
            </div>

                <!--a class="item" href="'.$newAlbumSexyUrl.'"><img class="content" src="'.$thisAlbumSleeve.'"/></a-->
        ';
    }

    echo '
        </div>
        <div class="globalCaption"></div>
        <!--div class="scrollbar">
            <div class="slider"><div class="position"></div></div>
        </div-->
    </div>
    ';
}

// browse($dirList, $position) ////////////////////////////////////////
// Construit la logique précédent <= courant => suivant
// Navigate through $dirList items

function browse($position, $pathStyle) {

    $rootMusicDir = getRoot();
    //$plop
    $dirList = getInfo($rootMusicDir, musicDirs);

    $nicePath = strip_tags($_GET['a']);
    $slash = "/";
    $dash = ",";

    $meanPath = $rootMusicDir.'/'.str_replace($dash, $slash, $nicePath);
    $directoryToScan = $meanPath;

    $dirListSize = count($dirList);

    $firstDir = trim(rtrim($dirList[0], $slash), $slash);
    $lastDir = trim(rtrim($dirList[$dirListSize-1], $slash), $slash);

    $currentDir = trim(rtrim($directoryToScan, $slash), $slash);
    $dirListKey = array_search($currentDir, $dirList);

    $prevPathElements = explode($slash, $dirList[$dirListKey-1]);
    $herePathElements = explode($slash, $dirList[$dirListKey]);
    $nextPathElements = explode($slash, $dirList[$dirListKey+1]);

    $firstDirPathElements = explode($slash, $dirList[0]);
    $lastDirPathElements = explode($slash, $dirList[$dirListSize-1]);

    $niceFirst = $firstDirPathElements[1].$dash.$firstDirPathElements[2];
    $niceLast = $lastDirPathElements[1].$dash.$lastDirPathElements[2];

    $nicePrev = $prevPathElements[1].$dash.$prevPathElements[2];
    $niceHere = $herePathElements[1].$dash.$herePathElements[2];
    $niceNext = $nextPathElements[1].$dash.$nextPathElements[2];

    $meanPrev = $prevPathElements[1].$slash.$prevPathElements[2];
    $meanNext = $nextPathElements[1].$slash.$nextPathElements[2];

    switch ($pathStyle) {
    case 'mean':
        $prev = ltrim(ltrim($dirList[$dirListKey-1], '.'), $slash);
        $here = ltrim(ltrim($dirList[$dirListKey], '.'), $slash);
        $next = ltrim(ltrim($dirList[$dirListKey+1], '.'), $slash);
        break;
    case 'nice':
        $prev = $nicePrev;
        $here = $niceHere;
        $next = $niceNext;
        $firstDir = $niceFirst;
        $lastDir = $niceLast;
        break;
    case 'extraNice':
        $prev = strtr($nicePrev, "_", " ");
        $here = strtr($niceHere, "_", " ");
        $next = strtr($niceNext, "_", " ");
        $firstDir = strtr($niceFirst, "_", " ");
        $lastDir = strtr($niceLast, "_", " ");

        $prev = str_replace(",", " - ", $prev);
        $here = str_replace(",", " - ", $here);
        $next = str_replace(",", " - ", $next);
        $firstDir = str_replace(",", " - ", $firstDir);
        $lastDir = str_replace(",", " - ", $lastDir);

        break;
    }

    switch ($position) {
    case 'prev':
        if ($dirListKey === 0) {
            return $lastDir;
        }
        else {
            return $prev;
        }
        break;
    case 'current':
        return $here;
        break;
    case 'next':
        if ($dirListKey === $dirListSize-1) {
            return $firstDir;
        }
        else {
            return $next;
        }
        break;
    }
}

// bytestostring($size, $precision) ////////////////////////////////////////
// Présentation de la taille du fichier pour les humains
// Human-readable filesize

function bytestostring($size, $precision = 0) {
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'kB', 'B');
    $total = count($sizes);
    while($total-- && $size > 1024) $size /= 1024;
    return round($size, $precision).$sizes[$total];
}

// footer() ////////////////////////////////////////
// Pied de page avec navigation précédent/suivant
// Build page footer and prev/next browser

function albumBrowser($labelName) {

    $prevAlbumSleeve = getInfo(browse(prev, mean), thisAlbumSleeve);
    $nextAlbumSleeve = getInfo(browse(next, mean), thisAlbumSleeve);

    echo '
<div id="albumBrowser" class="main" style="position: relative;">
<div class="invisible_if_no_audio" id="semiTransparentDiv" style="position: absolute; background-color: black; filter:alpha(opacity=55);-moz-opacity:.55;opacity:.55; height: 100%; width: 100%; z-index: 1;"></div>
    <div class="left" style="position: relative; z-index: 2;">
        <a title="'.TXT_PREVIOUS_ALBUM.' = '.browse(prev, extraNice).'" href="'.$script.'?a='.browse(prev, nice).'">
        <img class="thumb" src="'.$prevAlbumSleeve.'" alt="'.TXT_PREVIOUS_ALBUM.' = '.browse(prev, nice).'" /></a>
    </div>
    <div class="right" style="position: relative; z-index: 2;">
        <div class="fadeAlbums"><strong>'.TXT_NEXT_ALBUM.'</strong><br />'.browse(next, extraNice).'</div>
        <a title="'.TXT_NEXT_ALBUM.' = '.browse(next, extraNice).'" href="'.$script.'?a='.browse(next, nice).'">
        <img class="thumb" src="'.$nextAlbumSleeve.'" alt="'.TXT_NEXT_ALBUM.' = '.browse(next, nice).'" /></a>
    </div>
    <div class="middle" style="position: relative; z-index: 2;">
        <a title="'.$labelName.', '.TXT_BASELINE.'" href="'.$_SERVER[PHP_SELF].'"><img style="width:60px" class="rollover" src="img/beldigital_logo_off.png" alt="beldigital_logo_on.png" /></a>
    </div>
</div>
<p id="footBr">&nbsp;</p>
    ';
}


// indexFooter($totaltime) ////////////////////////////////////////
// Temps approx. d'éxecution
// Script exec. time

function indexFooter($totaltime) {
    echo '
    <div id="debug" class="main">
        <table class="footdown">
            <tr>
                <td>
                ';
    echo $songs.' songs in '.$albums.' albums&nbsp;|&nbsp;'.$script.' v0.6 Rendered in: ' . round($totaltime, 4) . ' seconds.' . 'by PHP v'.phpversion().'&nbsp;|&nbsp;<a href="'.$script.'?code">SOURCE [code]</a>&nbsp;|&nbsp;VALIDATE <a href="http://validator.w3.org/check?uri=referer">XHTML</a>&nbsp;|&nbsp;
                <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>&nbsp;|&nbsp;<a rel="license" href="http://creativecommons.org/licenses/by-sa/2.0/fr/">CC LICENSE</a>&nbsp;|&nbsp;<a href="?lang=fr" onClick="history.go(0)">'.TXT_FRENCH.'</a>&nbsp;|&nbsp;<a href="?lang=en" onClick="history.go(0)">'.TXT_ENGLISH.'</a>
                </td>
            </tr>
        </table>
    </div>
    ';
}


// debugFooter($totaltime) ////////////////////////////////////////
// Temps approx. d'éxecution
// Script exec. time

function debugFooter($totaltime, $albums, $songs) {
    echo '
    <div id="debug" class="main">
        <table class="footdown">
            <tr>
                <td>
                ';
    echo $songs.' songs in '.$albums.' albums&nbsp;|&nbsp; '.$script. ' v0.7.1 Rendered in: ' . round($totaltime, 4) . ' seconds.' . 'by PHP v'.phpversion().'&nbsp;|&nbsp;<a href="'.$script.'?code">SOURCE [code]</a>&nbsp;|&nbsp;<a title="'.$cachefile.'" href="'.$cachefile.'">Cached</a>&nbsp;|&nbsp;VALIDATE <a href="http://validator.w3.org/check?uri=referer">XHTML</a>&nbsp;|&nbsp;
                <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>&nbsp;|&nbsp;<a rel="license" href="'.URL_CCLICENSE.'">'.TXT_LICENSE.'</a>&nbsp;|&nbsp;<a title="stats" href="https://logs.ovh.net/beldigital.net/">stats</a>&nbsp;|&nbsp;<a title="log" href="https://logs.ovh.net/beldigital.net/osl/">acces log</a>&nbsp;|&nbsp;<a title="log" href="https://logs.ovh.net/beldigital.net/osl/error/">error log</a>
                </td>
            </tr>
        </table>
    </div>
    ';
}

// fixedFooter($dirList) ////////////////////////////////////////
// Fixed Footer pane

function fixedFooter($dirList) {
    echo '
   </div> <!--end content div-->

   <div id="osx-modal-content">
     <div id="osx-modal-title">Help</div>
     <div class="close"><a href="#" class="simplemodal-close">x</a></div>
     <div id="osx-modal-data">
       <h2>MicroLabel1.5</h2>
       '.TXT_DEBUG_HELP_TXT.'
     </div>
   </div>


   <div id="controlFooter" class="zindex-one">
     <div id="controlFooter-left">
       <a title="'.TXT_FRENCH.'" href="'.$script.'?a='.browse(current, nice).'&amp;lang=fr">
         <img class="buttons" alt="'.TXT_FRENCH.'" src="img/flags/fr.png" /></a>
       <a title="'.TXT_ENGLISH.'" href="'.$script.'?a='.browse(current, nice).'&amp;lang=en">
         <img class="buttons" alt="'.TXT_ENGLISH.'" src="img/flags/uk.png" /></a>
       <a title="'.TXT_SPANISH.'" href="'.$script.'?a='.browse(current, nice).'&amp;lang=es">
         <img class="buttons" alt="'.TXT_SPANISH.'" src="img/flags/es.png" /></a>
       <a title="'.TXT_HELP.'" class="osx" href="#">
         <img id="helpButton" class="buttons" src="img/icon_help.png" alt="'.TXT_HELP.'" /></a>
     </div>
   </div>
    ';
}

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime[1] + $mtime[0];
$endtime = $mtime;
$totaltime = ($endtime - $starttime);

// Query string
$friendlyPath = strip_tags($_GET['a']);
$slash = "/";
$dash = ",";
$meanPath = $rootMusicDir.$slash.str_replace($dash, $slash, $friendlyPath);
$directoryToScan = $meanPath;
$directoryToScan = trim($directoryToScan, $slash);
$fileList = getInfo($directoryToScan, musicFiles);

if (isset($_GET['a']) && isset($_GET['s'])) {
    $songToPlay = strip_tags($s);
}

// Main block
if (!isset($_GET['a'])) {
    index($dirList, $labelName);
}

else {
    spitTitle($dirList, $fileList);
    audioList($fileList, $directoryToScan);
    videoList($fileList, $directoryToScan);
    albumBrowser($labelName);
    if (isset($_GET['debug'])) {
        debugFooter($totaltime, $albums, $songs);
    }
    fixedFooter($dirList);
}

if (isset($_GET['debug'])) {
    echo '$safelink : ['.$safelink.']<br />';
    echo '$thisAlbumTags : ['.$thisAlbumTags.']<br />';
    echo '<pre style="text-align:left;">$GLOBALS : ';
    var_dump($GLOBALS);
    echo '</pre>';
    var_dump($_SERVER["SCRIPT_URI"]);
    echo '<br />';
}

if (!$thisPageIsCached) {
    echo '<!-- Cached on '.date('jS F Y H:i:s').' -->';
} else {
    echo '<!-- Un-Cached -->';
}

echo '

<!--a accesskey="2" href="?style=RED" onclick="setActiveStyleSheet(\'RED\'); return false;">
red</a> | <a accesskey="2" href="?style=BLUE" onclick="setActiveStyleSheet(\'BLUE\'); return false;">blue</a>

<br />
<a class="tagger" href="#">plop</a-->



</body>
</html>
';

if (!isset($_GET['debug'])) {

    $fp = fopen($cachefile, 'w'); // open the cache file for writing
    fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file
    fclose($fp); // close the file
}
ob_end_flush(); // Send the output to the browser

?>
