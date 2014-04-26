<?php

 if (!defined("PATH_SEPARATOR")) {
    if ( strpos( $_ENV[ "OS" ], "Win" ) !== false )
        define( "PATH_SEPARATOR", ";" );
    else define( "PATH_SEPARATOR", ":" );
}

set_include_path("./TEXT:./libs/getid3");

if (isset($_GET['lang'])) {
    $timeFormula = "365*24*3600";
    if ($_GET['lang']=='en') {
        $lang = 'en';
        include('lang-en.php');
        setcookie("lang", $lang, time() + $timeFormula);
        //    exit();
    }
    if ($_GET['lang']=='fr') {
        $lang = 'fr';
        include('lang-fr.php');
        setcookie("lang", $lang, time() + $timeFormula);
    }
    if ($_GET['lang']=='de') {
        $lang = 'de';
        include('lang-de.php');
        setcookie("lang", $lang, time() + $timeFormula);
    }
    if ($_GET['lang']=='es') {
        $lang = 'es';
        include('lang-es.php');
        setcookie("lang", $lang, time() + $timeFormula);
    }
}

////////////////////////////////////////////////////////////////////
// Microlabel copyright 2010-2014 Phil CM <xaccrocheur@gmail.com> //
// licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html       //
// Because all music should be free                               //
// Please don't harm nobody w/ this code even if they ask to      //
////////////////////////////////////////////////////////////////////

require_once('getid3.php');

$labelName = 'beldigital';
global $labelName;

function getRoot() {
    $rootMusicDir = 'MUSIC';
    return $rootMusicDir;
}

///////////////////////////////////////////////////////////////// no user-serviceable parts below

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
    $thisAlbumSleeves = array();
    $getID3 = new getID3;
    $sp = '&nbsp;';
    $ch = 'comments_html';
    $thisMusicFileinfos = '';
    if (is_file($startPath)) {
        $fs = pathinfo($startPath);
        // $fsExt = $fs['extension'];

        $fsExt = isset($fs['extension']) ? $fs['extension'] : '';

        if ($fsExt == 'ogg') {
            $f = $getID3->analyze($startPath);
            getid3_lib::CopyTagsToComments($f);
            $thisFileTitleTag = (!empty($f[$ch]['title']) ? implode($sp, $f[$ch]['title'])  : $sp);
            $thisFileArtistTag = (!empty($f[$ch]['artist']) ? implode($sp, $f[$ch]['artist'])  : $sp);
            $thisFileAlbumTag = (!empty($f[$ch]['album']) ? implode($sp, $f[$ch]['album'])  : $sp);
            $thisFileYearTag = (!empty($f[$ch]['year']) ? implode($sp, $f[$ch]['year'])  : $sp);

            if (empty($f[$ch]['track'])) {
                $thisFileTrackTag =  (!empty($f[$ch]['tracknumber']) ? implode($sp, $f[$ch]['tracknumber'])  : "plop");
            } else {
                $thisFileTrackTag = (!empty($f[$ch]['track']) ? implode($sp, $f[$ch]['track'])  : "plip");
            }

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

            $infosExt = isset($infos['extension']) ? $infos['extension'] : '';

            if ($infosExt == 'ogg') {

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

                $thisAlbumTrackTags[] = $thisAlbumTrackTag;
                $thisAlbumGenreTag = (!empty($f[$ch]['genre']) ? implode($sp, $f[$ch]['genre'])  : $sp);
                $thisAlbumGenreTags[] = $thisAlbumGenreTag;
                $thisAlbumCommentTag = (!empty($f[$ch]['comment']) ? implode($sp, $f[$ch]['comment'])  : $sp);
                $thisAlbumCommentTags[] = $thisAlbumCommentTag;
                $thisAlbumOrganizationTags = (!empty($f[$ch]['organization']) ? implode($sp, $f[$ch]['organization']) : $sp);
                $thisAlbumGenreTags[] = $thisAlbumGenreTag;


                $musicFiles[$filePath] = isset($thisMusicFileinfos['basename']) ? $thisMusicFileinfos['basename'] : '';

                // $musicFiles[$filePath] = $thisMusicFileinfos['basename'];

                $thisAlbumTags['title'] = $thisAlbumTitleTags;
                $thisAlbumTags['artist'] = $thisAlbumArtistTag;
                $thisAlbumTags['album'] = $thisAlbumAlbumTag;
                $thisAlbumTags['year'] = $thisAlbumYearTags;
                $thisAlbumTags['track'] = $thisAlbumTrackTags;
                $thisAlbumTags['genre'] = $thisAlbumGenreTags;
                $thisAlbumTags['organization'] = $thisAlbumOrganizationTags;
                $thisAlbumTags['comment'] = $thisAlbumCommentTags;
            }

            elseif ($infosExt == 'jpg' || $infosExt == 'png'  || $infosExt == 'gif') {
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

        if(!empty($thisAlbumSleeve)) {
            return $thisAlbumSleeves;
        }
        else {
            $thisAlbumSleeves['0'] = 'img/beldigital_logo_off.png';
            return $thisAlbumSleeves;
        }
        break;

    case 'thisAlbumBackgroundImages':
        return $thisAlbumBackgroundImages;
        break;

        break;
    case 'thisAlbumSleeve':

        if(!empty($thisAlbumSleeve)) {
            return $thisAlbumSleeves['0'];
        }
        else {
            return 'img/beldigital_logo_off.png';
        }
        break;
    }
}

$myDumbVar=getInfo($rootMusicDir, 'musicDirs');

$mtime = microtime();
$mtime = explode(" ",$mtime);
$mtime = $mtime['1'] + $mtime['0'];
$starttime = $mtime;

// Permet la lecture du source a volee, cool huh ?
if (isset($_GET['code'])) { die(highlight_file(__FILE__, '1')); }

$httpVars= isset($HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE']) ? $HTTP_SERVER_VARS['HTTP_ACCEPT_LANGUAGE'] : '';

global $HTTP_COOKIE_VARS;

$browserPrefs = substr($httpVars,'0','2');
$cookiePrefs = $HTTP_COOKIE_VARS['lang'];

// $expire = $timeFormula;

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


function xmlInfos($element) {
    $videoObjects = '';
    $thisAlbumPath = browse('current', 'mean');

    $file = $thisAlbumPath.'/info.xml';
    $xml = simplexml_load_file($file);

    if (count($xml->video) > 0) {
        foreach ($xml->video as $video) {
            $videoObjects[] = $video;
        }
    }


    if (count($xml->musicien) > 0) {

        foreach ($xml->musicien as $musicien) {
            $myMusicians[] = $musicien;
        }
    }


    switch ($element) {
    case 'all_musicians':
        return $myMusicians;
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
    <link type='text/css' href="css/jquery.simplemodal-osx.css" rel='stylesheet' media='screen' />
    <style type="text/css" media="screen">@import "css/style.css";</style>
    <style type="text/css" media="screen">@import "css/jquery.colorbox.css";</style>

    <script src="libs/player.js"></script>

    <script src="libs/jquery-1.5.1.min.js"></script>
    <script src="libs/jquery.easing.1.3.js"></script>
    <script src="libs/jquery.colorbox-min.js"></script>
    <script src="libs/jquery.roundabout.js"></script>
    <script src="libs/jquery.roundabout-shapes.js"></script>

    <script src="libs/jquery.simplemodal.js"></script>
    <script src="libs/jquery.simplemodal-osx.js"></script>

    <!--[if lt IE 9]>
        <script src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script>
        <![endif]-->
    <script>

var test_audio= document.createElement("audio") //try and create sample audio element
var test_video= document.createElement("video") //try and create sample video element

if (songToPlay !== undefined) {
    load_track(songToPlay);
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

    $('div.musicien').hover(function () {
        $(this).stop(true,true).animate({
            width: '+=400',
            height: '+=45'
        }, 500);
    }, function () {
        $(this).stop(true,true).animate({
            width: '-=400',
            height: '-=45'
        },500)
    });

    $(".youtube").colorbox({iframe:true, innerWidth:425, innerHeight:344});
    var plopA = '<? echo TXT_NO_AUDIO_TXT ?>';

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
	$('.fadeAlbums').fadeIn(80000);
	$('.fadeAlbums').fadeOut(8000);
    }
    setInterval(pulse, 5000);

    $('ul#microlabel').roundabout({
	duration: 500,
        easing: 'easeOutQuint',
        shape: 'tearDrop'
    });

    onMlPlayerLoad();

    if (songToPlay !== undefined) {
	/* nextClicked() */
	load_track(songToPlay);
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

<link rel="shortcut icon" href="img/beldigital_logo_on.png" />

  <?php



  $dirList = getInfo($rootMusicDir, 'musicDirs');

//spitTitle($dirList, $fileList)////////////////////////////////////////
// Print album Sleeve if any
// Affiche la pochette de l'album si on trouve une image, et le titre de l'album

function spitTitle($dirList, $fileList) {

    $thisAlbumPath = browse('current', 'mean');
    $thisAlbumTags = getInfo($thisAlbumPath, 'thisAlbumTags');
    $thisAlbumSleeves = getInfo($thisAlbumPath, 'thisAlbumSleeves');

    $artistName = isset($thisAlbumTags['artist']) ? $thisAlbumTags['artist'] : '';
    $albumName = isset($thisAlbumTags['album']) ? $thisAlbumTags['album'] : '';

    $albumGenre = isset($thisAlbumTags['genre']) ? $thisAlbumTags['genre'] : '';
    $albumYear = isset($thisAlbumTags['year']) ? $thisAlbumTags['year'] : '';
    $albumLabel = isset($thisAlbumTags['organization']) ? $thisAlbumTags['organization'] : '';

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
            $earliest = $years['0'];
            $latest =  $years[count($years)-'1'];
            $year = $earliest.' => '.$latest;
        }
    } else {
        $year = $years['0'];
    }

    $thisAlbumBackgroundImages = getInfo($thisAlbumPath, 'thisAlbumBackgroundImages');

    echo '
<title>'.$artistName. ' "'.$albumName.'" ('.$albumLabel.')</title>
<link rel="shortcut icon" type="image/x-icon" href="'.str_replace(" ", "%20", $thisAlbumSleeves['0']).'" />


<style type="text/css">
html, body {
    background: #000528 url('.$thisAlbumBackgroundImages['0'].') repeat fixed 0 0;
}
</style>

        ';


    echo '
</head>
<body class="microlabel-body">
<div id="main">
  <div class="content">

    <div id="header" class="main extremities">
      <div class="left">
        <div class="artistName">'.$artistName.'</div>
        <div class="albumName">'.$albumName.'</div>
        <div class="index_meta">
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
        $path_parts = pathinfo($thisAlbumSleeves['0']);
        $thisAlbumSleeveFileName = $path_parts['filename'].'.'.$path_parts['extension'];
        echo '
        <a href="'.str_replace(" ", "%20", $thisAlbumSleeves['0']).'" title="'.TXT_DOWNLOAD.' '.$thisAlbumSleeveFileName.'" class="sleeve"><img style="height:100%" src="'.str_replace(" ", "%20", $thisAlbumSleeves['0']).'" alt="'.$thisAlbumSleeveFileName.'" /></a>
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

// $script = $_SERVER['SCRIPT_URI'];
// global $script;

function audioList($fileList, $albumPath) {

  //    Pour le browse() dans le player
  //   $dirList = getInfo("OGG/", musicDirs);

  $i = 0;
  $z = $i+1;
  $numberOfSongs = 0;

  $host = $_SERVER["HTTP_HOST"];

  $script = isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : '';

  $pos = strpos($_SERVER["REQUEST_URI"], "/?");

  $myScriptPath = pathinfo($_SERVER["SCRIPT_NAME"]);
  $myDir = $myScriptPath['dirname'];

  $numberOfSongsInThisDirectory = getInfo($albumPath, 'numberOfSongs');

  $thisAlbumTags = getInfo($albumPath, 'thisAlbumTags');

  foreach ($fileList as $fullFileName => $myFileName) {
    $thisFileTags = getInfo($fullFileName, 'thisFileTags');
    $trackNumbers[] = $thisFileTags['track'];
    $trackTitles[$fullFileName] = $thisFileTags['title'];
    $track['url'] = $fullFileName;
    $numberOfSongs++;
  }

  echo '
        <ul id="MlPlayer">
        ';

  ksort($trackTitles);

  foreach ($trackTitles as $fullFileName => $trackTitle) {
      $thisFileTags = getInfo($fullFileName, 'thisFileTags');

      $artistName = rawurldecode($thisAlbumTags['artist']);
      $albumName = rawurldecode($thisAlbumTags['album']);
      $albumGenre = rawurldecode($thisAlbumTags['genre']);
      $albumRecordLabel = rawurldecode($thisFileTags['organization']);
      $albumYear = $thisAlbumTags['year'];

      $artistName = rawurldecode($thisFileTags['artist']);
      $albumName = rawurldecode($thisFileTags['album']);
      $albumGenre = rawurldecode($thisFileTags['genre']);
      $albumYear = $thisFileTags['year'];
      $trackTitle = rawurldecode($thisFileTags['title']);
      $trackPlayTime = $thisFileTags['playtime'];
      $trackFileSize = $thisFileTags['size'];
      $trackBitRate = $thisFileTags['bitrate'];
      $trackNumber = $thisFileTags['track'];
      $trackComment = rawurldecode($thisFileTags['comment']);

      $thisFilePathElements = explode("/", $fullFileName);
      $thisFileNicePath = $thisFilePathElements[count($thisFilePathElements)-'3'].",".$thisFilePathElements[count($thisFilePathElements)-'2'];
      $thisFileObfuscatedPath = $thisFilePathElements[count($thisFilePathElements)-'3']."/".$thisFilePathElements[count($thisFilePathElements)-'2']."/".$thisFilePathElements[count($thisFilePathElements)-'1'];

      $fileName = $thisFilePathElements[count($thisFilePathElements)-'1'];

      // Note our use of ===.  Simply == would not work as expected
      // because the position of 'a' was the 0th (first) character.
      if ($pos === false) {
          $unSafeLink = 'http://'.$host.$script.'?a='.$thisFileNicePath.'&amp;s='.$z++;
      } else {
          $unSafeLink = 'http://'.$host.$myDir.'/?a='.$thisFileNicePath.'&amp;s='.$z++;
      }

      $shortLink = getTinyUrl($unSafeLink);

      // $albumYear = isset($albumYear) ? $albumYear : "N / A";

      $albumYear = (!isset($albumYear) || $albumYear == " " || $albumYear == "&nbsp;") ? 'N / A' : $albumYear;
      $trackPlayTime = (!isset($trackPlayTime) || $trackPlayTime == " " || $trackPlayTime == "&nbsp;") ? 'N / A' : $trackPlayTime;
      $trackBitRate = (!isset($trackBitRate) || $trackBitRate == " " || $trackBitRate == "&nbsp;") ? 'N / A' : $trackBitRate;
      $trackFileSize = (!isset($trackFileSize) || $trackFileSize == " " || $trackFileSize == "&nbsp;") ? 'N / A' : $trackFileSize;
      $albumRecordLabel = (!isset($albumRecordLabel) || $albumRecordLabel == " " || $albumRecordLabel == "&nbsp;") ? 'N / A' : $albumRecordLabel;

      echo '
        <li>
        <h3>'.$trackTitle.'</h3>
            <div class="visible_if_no_audio">
                <h4 class="albumName">'.$artistName.' - '.$albumName.'</h4>
                <table class="album-description">
                    <tr><td>'.TAGS_RECORD_LABEL.'</td><td class="value">'.$albumRecordLabel.'</td></tr>
                    <tr><td>'.TAGS_RECORD_LABEL.'</td><td class="value">'.$albumRecordLabel.'</td></tr>
                    <tr><td>'.TAGS_TRACK.'s</td><td class="value">'.$numberOfSongs.'</td></tr>
                    <tr><td>'.TAGS_YEAR.'</td><td class="value">'.$albumYear.'</td></tr>
                    <tr><td>'.TAGS_GENRE.'</td><td class="value">'.$albumGenre.'</td></tr>
                </table>
            </div>

            <h1 class="invisible_if_no_audio">'.$trackComment.'</h1>

            <div class="invisible_if_no_audio">
            <h4 class="albumName">'.$artistName.' - '.$albumName.'</h4>
            <p>'.$trackTitle.'</p>
                <table class="album-description">
                    <tr><td>'.TAGS_YEAR.'</td><td class="value">'.$albumYear.'</td></tr>
                    <tr><td>'.TAGS_PLAYTIME.'</td><td class="value">'.$trackPlayTime.'</td></tr>
                    <tr><td>'.TAGS_BITRATE.'</td><td class="value">'.$trackBitRate.'</td></tr>
                    <tr><td>'.TAGS_SIZE.'</td><td class="value">'.$trackFileSize.'</td></tr>
                    <tr><td>'.TAGS_RECORD_LABEL.'</td><td class="value">'.$albumRecordLabel.'</td></tr>
                </table>
      ';

      $tes = rtrim( $trackComment );

      if (!empty($tes) && ($trackComment !== "&nbsp;" )) {
          echo '<blockquote>'.$trackComment.'</blockquote>';
      }

      echo '
            <form class="urlFields">
              <input type="text" class="loongURL" size="20" name="'.TAGS_SHARE.'" onClick="this.select();" value="'.$unSafeLink.'"><br/>
              <input type="text" class="shortURL" size="20" onClick="this.select();" value="'.$shortLink.'" />
            </form>

            <table class="songMenu">
            <tr>
              <td><img src="img/icon_download.png" alt="'.TXT_DOWNLOAD.'" /><a title="' . $trackTitle.' ('.TXT_DOWNLOAD.')" href="dl.php?d='.$thisFileNicePath.','.$fileName.'">'.TXT_DOWNLOAD.'</a></td>
              <td><img src="img/icon_love.png" alt="'.TXT_BUY.'" /><a href="#">'.TXT_BUY.'</a></td>
            </tr>
            </table>
          </div>
          <a class="downloadTuneLink" href="'.$fullFileName.'">'.TXT_DOWNLOAD.' '.$trackTitle .'</a>
        </li>
            ';

      // Feed the tracklist
      $audioFile[$trackTitle] = $fileName;

  } // End foreach
  echo '
        </ul>
    <div id="musiciens">
';

  $musiciens = xmlInfos('all_musicians');

  // echo '<pre>';
  // var_dump($musiciens);
  // echo '</pre>';

  foreach ($musiciens as $zicos) {

  // $test = array_search('mail', $zicos);
  // echo '('.$test.')';

  //     if (!array_search('mail', $musiciens)) {
  //             echo "now";
  //         $email = false;
  //     } else {
  //             echo "yow";
  //         $email = true;
  //     }

      echo '
    <div class="musicien">
';
      foreach ($zicos as $key => $value) {
          if ($key == 'instrument') {
              if ($value == 'guitar') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' plays guitar on this album" alt="'.$zicos['name'].' plays guitar on this album" src="img/instruments/guitar.png">';
              }
              if ($value == 'bass') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' plays bass on this album" alt="'.$zicos['name'].' plays guitar on this album" src="img/instruments/bass.png">';
              }
              if ($value == 'drums') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' plays drums on this album" alt="'.$zicos['name'].' plays drums on this album" src="img/instruments/drums.png">';
              }
              if ($value == 'other') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' plays all kinds of stuff on this album" alt="'.$zicos['name'].' plays all kinds of stuff on this album" src="img/instruments/other.png">';
              }
              if ($value == 'vocal') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' sings on this album" alt="'.$zicos['name'].' sings on this album" src="img/instruments/vocal.png">';
              }
              if ($value == 'leadvocal') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' sings lead vocals on this album" alt="'.$zicos['name'].' sings lead vocals on this album" src="img/instruments/leadvocal.png">';
              }
              if ($value == 'research') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' helped on this album" alt="'.$zicos['name'].' helped on this album" src="img/instruments/research.png">';
              }
              if ($value == 'recording') {
                  $thisInstruments = $thisInstruments.'<img class="instrument" title="'.$zicos['name'].' recorded this album" alt="'.$zicos['name'].' recorded this album" src="img/instruments/jack.png">';
              }
          }
          if ($key == 'twitter') {
              $thisContacts = '<a href="http://twitter.com/'.$value.'"><img class="instrument" alt="Twitter" title="Twitter account of '.$zicos['name'].'" src="img/contacts/twitter.png"></a>';
          }
          if ($key == 'email') {
                  $hash = md5(strtolower(trim($value)));
                  $thisContacts = $thisContacts.'<a href="mailto:'.$value.'"><img class="contact" alt="Email" title="Email '.$zicos['name'].'" src="img/contacts/email.png"></a>';
                  $thisGravatar = '<a href="mailto:'.$value.'"><img class="gravatar" alt="Email" title="Email '.$zicos['name'].'" src="http://www.gravatar.com/avatar/'.$hash.'?d=retro"></a>';
          } else {
              $thisGravatar = '<img class="gravatar" title="'.$zicos['name'].' is a sad musician, doesn\'t have an email :(" alt="No email" src="img/contacts/nomail.png">';
          }

      }
      echo '<h5 class="musicien">'.$thisGravatar.' '.$zicos['name'].'</h5>';
      echo '<span class="instruments">'.$thisInstruments.'</span>';
      echo '<span class="contacts">'.$thisContacts.'</span>';
      echo '
    </div>
';
  }

// ⌨ ☺ ☠

  echo '
</div>
</div>
';



  echo '
<!--	<p id="searchLinks">Try changing it to <a href="#">bacon</a>, <a href="#">pasta</a> or <a href="#">celery</a>.</p> -->
        <script type="text/javascript">
          //set some PagePlaer variables
          audio_volume=0.85; //default is 0.7

          MlPlayer("MlPlayer");
          /*what happens when the page is loaded?
          please see the "body" tag
          */
        </script>
        <div id="message"></div>
    ';
}

function videoList($fileList, $albumPath) {

    $videos_objects = xmlInfos('videos_object');

    // $videos_objects = isset(xmlInfos('videos_object')) ? xmlInfos('videos_object') : '';

if (!empty($videos_objects)) {
    foreach($videos_objects as $videos_object) {
        $videoName = $videos_object->name;
        $videoID = $videos_object->youtubeid;
        echo '
<a class="youtube" href="http://youtube.com/embed/'.$videoID.'" title="'.$videoName.'">
<div class="MlPlayerListItem MlPlayerVideoItem">'.$videoName.'
<img class="video_thumbnail" alt="Video" src="http://img.youtube.com/vi/'.$videoID.'/2.jpg" />
</div>
</a>
';
    }
}


}


// index($dirList) ////////////////////////////////////////
// Build label "home" index page with all the CD Sleeves
// Construit la page d'accueil en listant tous les albums

function index($dirList, $labelName, $question) {

    if (isset($question)) {
        return $thereIsMusic;
    }

    $numberOfAlbums = count($dirList);

    $script = isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : '';

    echo '<title>'.$labelName.' - Free Music</title>
    </head>
    ';

    echo '
    <body id="microlabel-index" class="microlabel-body">
        <div class="microlabel-index">
    ';

    if ($numberOfAlbums < 1) {
        $thereIsMusic = false;
echo '
		<div id="horizon">
			<div id="error">
			<img src="img/instruments/horns.png"/>
				<h1 id="error">Uh-ho</h1>
                Something quite wrong happenned. I think you just deleted your Music directory :(
			</div>
		</div>
';

    } else {
        echo '
            <ul id="microlabel">
';
}

    foreach ($dirList as $key => $albumPath) {
        $thisAlbumTags = getInfo($albumPath, 'thisAlbumTags');

        $artistName = $thisAlbumTags['artist'];
        $albumName = $thisAlbumTags['album'];
        $albumGenre = $thisAlbumTags['genre'];
        $albumYear = $thisAlbumTags['year'];

        $labelName = isset($thisAlbumTags['organization']) ? $thisAlbumTags['organization'] : 'No Label';
        $labelName = ($labelName == " " || $labelName == "&nbsp;") ? 'No Label' : $thisAlbumTags['organization'];

        $newAlbumSexyUrlElements = explode("/", $dirList[$key]);
        $newAlbumSexyUrl = $newAlbumSexyUrlElements[1].",".$newAlbumSexyUrlElements['2'];
        $thisAlbumSleeve = getInfo($albumPath, 'thisAlbumSleeve');

        echo '
                <li>
                    <a href="?a='.$newAlbumSexyUrl.'">
                        <img class="content" src="'.$thisAlbumSleeve.'" alt="'.$artistName.' - '.$albumName.' ('.$labelName.')" />
                    </a>
                    <a href="?a='.$newAlbumSexyUrl.'">
                        <p class="caption">'.$artistName.' - '.$albumName.' ('.$labelName.')</p>
                    </a>
                </li>
';
    }

    if ($numberOfAlbums > 1) {
    echo '
            </ul>
';
    }

    echo '
        </div>
    ';
}

// browse($dirList, $position) ////////////////////////////////////////
// Construit la logique précédent <= courant => suivant
// Navigate through $dirList items

function browse($position, $pathStyle) {

    $rootMusicDir = getRoot();
    $dirList = getInfo($rootMusicDir, 'musicDirs');

    $nicePath = strip_tags($_GET['a']);
    $slash = "/";
    $dash = ",";

    $meanPath = $rootMusicDir.'/'.str_replace($dash, $slash, $nicePath);
    $directoryToScan = $meanPath;

    $dirListSize = count($dirList);

    $firstDir = trim(rtrim($dirList['0'], $slash), $slash);
    $lastDir = trim(rtrim($dirList[$dirListSize-'1'], $slash), $slash);

    $currentDir = trim(rtrim($directoryToScan, $slash), $slash);
    $dirListKey = array_search($currentDir, $dirList);

    $prevPathElements = explode($slash, $dirList[$dirListKey-'1']);
    $herePathElements = explode($slash, $dirList[$dirListKey]);
    $nextPathElements = explode($slash, $dirList[$dirListKey+'1']);

    $firstDirPathElements = explode($slash, $dirList['0']);
    $lastDirPathElements = explode($slash, $dirList[$dirListSize-'1']);

    $niceFirst = $firstDirPathElements[1].$dash.$firstDirPathElements['2'];
    $niceLast = $lastDirPathElements[1].$dash.$lastDirPathElements['2'];

    $nicePrev = $prevPathElements['1'].$dash.$prevPathElements['2'];
    $niceHere = $herePathElements['1'].$dash.$herePathElements['2'];
    $niceNext = $nextPathElements['1'].$dash.$nextPathElements['2'];

    $meanPrev = $prevPathElements['1'].$slash.$prevPathElements['2'];
    $meanNext = $nextPathElements['1'].$slash.$nextPathElements['2'];

    switch ($pathStyle) {
    case 'mean':
        $prev = ltrim(ltrim($dirList[$dirListKey-'1'], '.'), $slash);
        $here = ltrim(ltrim($dirList[$dirListKey], '.'), $slash);
        $next = ltrim(ltrim($dirList[$dirListKey+'1'], '.'), $slash);
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
        if ($dirListKey === $dirListSize-'1') {
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

    $prevAlbumSleeve = getInfo(browse('prev', 'mean'), 'thisAlbumSleeve');
    $nextAlbumSleeve = getInfo(browse('next', 'mean'), 'thisAlbumSleeve');

    echo '
<div id="albumBrowser" class="main transparent" style="position: relative;">
    <div class="left" style="position: relative; z-index: 2;">
        <a title="'.TXT_PREVIOUS_ALBUM.' = '.browse('prev', 'extraNice').'" href="?a='.browse('prev', 'nice').'">
        <img class="thumb" src="'.$prevAlbumSleeve.'" alt="'.TXT_PREVIOUS_ALBUM.' = '.browse('prev', 'nice').'" /></a>
    </div>
    <div class="right" style="position: relative; z-index: 2;">
        <div class="fadeAlbums"><strong>'.TXT_NEXT_ALBUM.'</strong>
        <p>'.browse('next', 'extraNice').'</p></div>
        <a title="'.TXT_NEXT_ALBUM.' = '.browse('next', 'extraNice').'" href="?a='.browse('next', 'nice').'">
        <img class="thumb" src="'.$nextAlbumSleeve.'" alt="'.TXT_NEXT_ALBUM.' = '.browse('next', 'nice').'" /></a>
    </div>
    <div class="middle" style="position: relative; z-index: 2;">
        <a title="'.$labelName.', '.TXT_BASELINE.'" href="./"><img style="width:60px" class="rollover" src="img/beldigital_logo_off.png" alt="beldigital_logo_on.png" /></a>
    </div>
</div>
<p id="footBr">&nbsp;</p>
    ';
}


// fixedFooter($dirList) ////////////////////////////////////////
// Fixed Footer pane


// Version Control

function vc($element) {

    $opts = array('http'=>array('method'=>"GET", 'header'=>"User-Agent: microlabel"));

    $context = stream_context_create($opts);
    $current_commits = file_get_contents("https://api.github.com/repos/xaccrocheur/microlabel/commits", false, $context);


    if ($current_commits !== false) {
        $commits = json_decode($current_commits);
        $ref_commit = "c505abb5107da6f9052ec1f7ad1735186aeeda4e";

        $current_commit_minus1 = $commits['1']->sha;
        $commit_message = "last message : ".$commits['0']->commit->message;

        if (!strcmp($current_commit_minus1, $ref_commit)) {
            $version_class = "unmoved";
            $version_message = "μLabel version is up-to-date : (".$commit_message.")";
        } else {
            $version_class = "moved";
            $version_message = "New version available : (".$commit_message.")";
        }
    } else {
        $version_class = "unknown";
        $version_message = "Can't read μLabel version status";
    }

    switch ($element) {
    case 'class':
        return $version_class;
        break;
    case 'message':
        return $version_message;
        break;
    }
}

function fixedFooter($dirList) {
    $script = isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : '';
    echo '
   </div> <!--end content div-->

   <div id="osx-modal-content">
     <div id="osx-modal-title">Help</div>
     <div class="close"><a href="#" class="simplemodal-close">x</a></div>
     <div id="osx-modal-data">
       <h2>MicroLabel 1.5</h2>
       '.TXT_DEBUG_HELP_TXT.'

<div id="version" onClick="document.location.href=\'https://github.com/xaccrocheur/microlabel\'" title="'.vc("message").'">
    <a href="https://github.com/xaccrocheur/microlabel">Microlabel</a> <span class="'.vc("class").'">♼</span>
</div>


     </div>
   </div>


   <div id="controlFooter" class="zindex-one">
     <div id="controlFooter-left">
       <a title="'.TXT_FRENCH.'" href="?a='.browse('current', 'nice').'&amp;lang=fr">
         <img class="buttons" alt="'.TXT_FRENCH.'" src="img/flags/fr.png" /></a>
       <a title="'.TXT_ENGLISH.'" href="?a='.browse('current', 'nice').'&amp;lang=en">
         <img class="buttons" alt="'.TXT_ENGLISH.'" src="img/flags/uk.png" /></a>
       <a title="'.TXT_SPANISH.'" href="?a='.browse('current', 'nice').'&amp;lang=es">
         <img class="buttons" alt="'.TXT_SPANISH.'" src="img/flags/es.png" /></a>
       <a title="'.TXT_GERMAN.'" href="?a='.browse('current', 'nice').'&amp;lang=de">
         <img class="buttons" alt="'.TXT_SPANISH.'" src="img/flags/de.png" /></a>
       <a title="'.TXT_HELP.'" class="osx" href="#">
         <img id="helpButton" class="buttons" src="img/button_help_on.png" alt="'.TXT_HELP.'" /></a>
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



$fileList = getInfo($directoryToScan, 'musicFiles');


// Main block
if (!isset($_GET['a'])) {
    index($dirList, $labelName);
} else {

    /* twit('AzerOo0'); */
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
    /* echo '<pre style="text-align:left;">$GLOBALS : '; */
    /* var_dump($GLOBALS); */
    /* echo '</pre>'; */
    /* var_dump($_SERVER["SCRIPT_URI"]); */
    /* echo '<br />'; */
}


echo '

</body>
</html>
';

// if (!isset($_GET['debug'])) {

//     $fp = fopen($cachefile, 'w'); // open the cache file for writing
//     fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file
//     fclose($fp); // close the file
// }
// ob_end_flush(); // Send the output to the browser

?>
