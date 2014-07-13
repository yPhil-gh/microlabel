<?php
require_once('libs/microlabel.php');
require_once('libs/getid3/getid3.php');
require_once('mysql_connect.php');

////////////////////////////////////////////////////////////////////
// Microlabel copyright 2010-2014 Phil CM <xaccrocheur@gmail.com> //
// licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html       //
// Because all music should be free                               //
// Please don't harm nobody w/ this code even if they ask to      //
////////////////////////////////////////////////////////////////////
// Please see libs/microlabel.php for config options,             //
// and leave this file alone. That is, unless you find a bug ;)   //
////////////////////////////////////////////////////////////////////

$labelName = MICROLABEL_LABEL_NAME;

if (isset($_GET['tag'])) {
    header("Location: ./BO/?listdirectory=../".$rootMusicDir);
exit;
}

$rootMusicDir = MICROLABEL_MUSIC_DIR;

$album_list=array();
foreach ($dbh->query('select albums.id as album_id, albums.name, files.id as file_id
from
albums
left join files on files.id=albums.album_cover_file_id
where albums.status=1
order by albums.id
') as $album) $album_list[$album["album_id"]]=$album;


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

function getInfo_old($startPath, $element) {
    $thisAlbumSleeves = array();
    $getID3 = new getID3;
    $sp = '&nbsp;';
    $ch = 'comments_html';
    $thisMusicFileinfos = '';
    if (is_file($startPath)) {
        $fs = pathinfo($startPath);
        // $fsExt = $fs['extension'];

        $fsExt = isset($fs['extension']) ? $fs['extension'] : '';

        if ($fsExt == 'ogg' || $fsExt == 'mp3') {
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

            if ($infosExt == 'ogg' || $infosExt == 'mp3') {

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
            $thisAlbumSleeves['0'] = MICROLABEL_LABEL_LOGO;
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
            return MICROLABEL_LABEL_LOGO;
        }
        break;
    }
}

// $myDumbVar=getInfo($rootMusicDir, 'musicDirs');

////////////////////////////////


?>

<!DOCTYPE html>
<html>

<head>
<meta charset="UTF-8">
  <style type="text/css" media="screen">@import "libs/css/player.css";</style>
    <link type='text/css' href="libs/css/jquery.simplemodal-osx.css" rel='stylesheet' media='screen' />
    <style type="text/css" media="screen">@import "libs/css/style.css";</style>
    <style type="text/css" media="screen">@import "libs/css/jquery.colorbox.css";</style>

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
$zong = strip_tags($_GET['play']);
if (!empty($zong)) {
    echo $zong-1;
} else {
    echo "undefined";
}

?>";

$(document).ready(function() {

    if ( $("#horizon").is(":visible") ) {
        $("body > *").not("body > #horizon").remove();
    }

    var instr_w, conta_w;

    $('div.musicien').hover(function () {
        instr_w = parseFloat($(this).find('td.instruments').attr("title"));
        conta_w = parseFloat($(this).find('td.contacts').attr("title"));
        var div_width = (instr_w + conta_w * 5) + 'em';
        // alert(instr_w + 'contacts : ' + conta_w);
        $(this).stop(true,true).animate({
            width: '+='+div_width,
            height: '+=45'
        }, 500);
    }, function () {
        var div_width = (instr_w + conta_w * 5) + 'em';
        $(this).stop(true,true).animate({
            width: '-='+div_width,
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

    $('.fadeAlbums').fadeOut(4000);

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


  <?php

echo '<link rel="shortcut icon" href="'.MICROLABEL_LABEL_LOGO.'" />';

// $dirList = getInfo($rootMusicDir, 'musicDirs');

//spitTitle($dirList, $fileList)////////////////////////////////////////
// Print album Sleeve if any
// Affiche la pochette de l'album si on trouve une image, et le titre de l'album

function spitTitle($dirList, $fileList) {

    global $dbh,$album_list;

    $thisAlbumPath = browse('current', 'mean');
    // $thisAlbumTags = getInfo($thisAlbumPath, 'thisAlbumTags');
    // $thisAlbumSleeves = getInfo($thisAlbumPath, 'thisAlbumSleeves');

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

    $album_id=strip_tags($_GET['a']);
    foreach ($dbh->query("select distinct artists.name as artist_name, albums.name as album_name
from
albums
inner join album_detail on albums.id=album_detail.album_id
inner join songs on album_detail.song_id=songs.id
inner join song_credits on songs.id=song_credits.song_id
inner join artists on artists.id=song_credits.artist_id
where
albums.id=". $album_id
    ) as $row) {
        if($artistName!="")$artistName.=" &amp; ";
        $artistName.=$row["artist_name"];
    }
    $albumName=$row["album_name"];

    // $thisAlbumBackgroundImages = getInfo($thisAlbumPath, 'thisAlbumBackgroundImages');

    echo '
<title>'.$artistName. ' "'.$albumName.'" ('.$albumLabel.')</title>
<link rel="shortcut icon" type="image/x-icon" href="get_file.php?id='.$album_list[$album_id]["file_id"].'" />

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
        <a href="'.str_replace(" ", "%20", $thisAlbumSleeves['0']).'" title="'.TXT_DOWNLOAD.' '.$thisAlbumSleeveFileName.'" class="sleeve"><img style="height:100%" src="get_file.php?id='.$album_list[$album_id]["file_id"].'" alt="'.$thisAlbumSleeveFileName.'" /></a>
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
    $tinyurl = file_get_contents("http://tinyurl.com/api-create.php?url=".html_entity_decode($url));
    return $tinyurl;
}


// audioList($fileList) ////////////////////////////////////////
// Build audio content table
// Tableau de chansons

// $script = $_SERVER['SCRIPT_URI'];
// global $script;

function audioList($album_id) {

    global $dbh,$album_list;

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

  // $numberOfSongsInThisDirectory = getInfo($albumPath, 'numberOfSongs');

  // $thisAlbumTags = getInfo($albumPath, 'thisAlbumTags');

//cela va disparaitre
  foreach ($fileList as $fullFileName => $myFileName) {
    // $thisFileTags = getInfo($fullFileName, 'thisFileTags');
    $trackNumbers[] = $thisFileTags['track'];
    $trackTitles[$fullFileName] = $thisFileTags['title'];
    $track['url'] = $fullFileName;
    $numberOfSongs++;
  }

  echo '
        <ul id="MlPlayer">
        ';

  ksort($trackTitles);
  foreach ($dbh->query("select songs.id,songs.name,files.size
from album_detail
inner join songs on songs.id=album_detail.song_id
inner join files on files.id=songs.song_file_id
where album_detail.album_id=$album_id
") as $fileinfo){
      // var_dump($fileinfo);

//cela deviendra un fetch sql
//  foreach ($trackTitles as $fullFileName => $trackTitle) {
      $fullFileName=$fileinfo["name"];
//      $thisFileTags = getInfo($fullFileName, 'thisFileTags');
      $file_id=$fileinfo["id"]; //en prod sera issu du resultat sql

      $artistName = rawurldecode($thisAlbumTags['artist']);
      $albumName = rawurldecode($thisAlbumTags['album']);
      $albumGenre = $thisAlbumTags['genre'];
      $albumRecordLabel = rawurldecode($thisFileTags['organization']);
      $albumYear = $thisAlbumTags['year'];

      $artistName = rawurldecode($thisFileTags['artist']);
      $albumName = rawurldecode($thisFileTags['album']);
      $albumGenre = rawurldecode($thisFileTags['genre']);
      $albumYear = $thisFileTags['year'];
//      $trackTitle = rawurldecode($thisFileTags['title']);
      $trackTitle = $fileinfo["name"];
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
          $unSafeLink = 'http://'.$host.$script.'?a='.$thisFileNicePath.'&amp;play='.$z++;
      } else {
          $unSafeLink = 'http://'.$host.$myDir.'/?a='.$thisFileNicePath.'&amp;play='.$z++;
      }

      // $shortLink = getTinyUrl($unSafeLink);

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
                    <tr><td>'.TAGS_TRACK.'s</td><td class="value">'.$numberOfSongs.'</td></tr>
                    <tr><td>'.TAGS_YEAR.'</td><td class="value">'.$albumYear.'</td></tr>
                    <tr><td>'.TAGS_GENRE.'</td><td class="value">'.$albumGenre.'</td></tr>
                    <tr><td>'.TAGS_RECORD_LABEL.'</td><td class="value">'.$albumRecordLabel.'</td></tr>
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
          echo '<blockquote class="comment">'.$trackComment.'</blockquote>';
      }

      echo '
            <form class="urlFields">
              <input type="text" class="loongURL" size="20" name="'.TAGS_SHARE.'" onClick="this.select();" value="'.$unSafeLink.'"><br/>
              <!--input type="text" class="shortURL" size="20" onClick="this.select();" value="'.$shortLink.'" /-->
            </form>

            <table class="songMenu">
            <tr>
              <td><img src="img/icon_download.png" alt="'.TXT_DOWNLOAD.'" /><a title="' . $trackTitle.' ('.TXT_DOWNLOAD.')" href="get_file.php?id='.$file_id.'">'.TXT_DOWNLOAD.'</a></td>
              <td><img src="img/icon_love.png" alt="'.TXT_BUY.'" /><a href="#">'.TXT_BUY.'</a>

<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="CHTFK49LJPT8N">
<table>
<tr><td><input type="hidden" name="on0" value="Prix">Prix</td></tr><tr><td><select name="os0">
	<option value="Auditor">Auditor €1,00 EUR</option>
	<option value="Fan">Fan €3,00 EUR</option>
	<option value="Sponsor">Sponsor €5,00 EUR</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="EUR">
<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_cart_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>


</td>
            </tr>
            </table>
          </div>
          <a class="downloadTuneLink" href="get_file.php?id='.$file_id.'">'.TXT_DOWNLOAD.' '.$trackTitle .'</a>
        </li>
            ';

  } // End foreach
  echo '
        </ul>
    <div id="musiciens">
';

  // $musiciens = xmlInfos('all_musicians');
  // $xml = xmlInfos('all_musicians');

  // foreach ($xml as $musicien) {
  //     foreach($musicien as $key => $value) {
  //         $muzikos[(string) $musicien['name']][(string) $key][] = (string) $value;
  //     }
  // }


//   foreach ($muzikos as $name => $muziko) {
//       $myName = $name;
//       $thisZikoInstrs = array();
//       $thisZikoEmails = array();
//       $thisZikoTwitters = array();
//       $numberOfIntruments = 0;
//       $numberOfContacts = 0;
//       $myEmail = $muziko['email'][0];
//       $myTwitter = $muziko['twitter'][0];
//       $hash = md5(strtolower(trim($myEmail)));
//       $thisGravatar = 'http://www.gravatar.com/avatar/'.$hash.'?d=retro';
//       foreach($muziko as $key => $value) {
//           foreach ($value as $val) {
//               if ($key == 'instrument') {
//                   $thisZikoInstrs[] = $val;
//               }
//               if ($key == 'email') {
//                   $thisZikoEmails[] = $val;
//               }
//               if ($key == 'twitter') {
//                   $thisZikoTwitters[] = $val;
//               }
//           }
//       }

//       echo '
//     <div class="musicien">
// ';
//       echo '<h5 class="musicien"><a href="mailto:'.$myEmail.'"><img class="gravatar" alt="Email" title="Email '.$myName.'" src="'.$thisGravatar.'"></a>'.$myName.'</h5>
//             <table class="muzikosTable">
//                 <tr>
//                 <td class="instruments" title="'.count($thisZikoInstrs).'">';
//       foreach ($thisZikoInstrs as $instrument) {
//           $numberOfIntruments++;
//           if (file_exists('img/instruments/'.$instrument.'.png')) {
//               $instrument_icon = 'img/instruments/'.$instrument.'.png';
//           } else {
//               $instrument_icon = 'img/instruments/unknown_instrument.png';
//           }

//           echo '<img class="instrument" title="'.$myName.' plays '.$instrument.' on this album" alt="'.$myName.' plays '.$instrument.' on this album" src="'.$instrument_icon.'">';
//       }

//       echo '</td>

//             <td class="contacts" title="'.(count($thisZikoTwitters) + count($thisZikoEmails)).'">

//             <a href="mailto:'.$myEmail.'"><img class="contact" alt="Email" title="Email '.$myName.'" src="img/contacts/email.png"></a>

//             <a href="http://twitter.com/'.$myTwitter.'"><img class="instrument" alt="Twitter" title="Twitter account of '.$myName.'" src="img/contacts/twitter.png"></a>
//             </td>
//                 </tr>
//                 </table>

// ';

//       echo '
//         </div>';
//   }

// // ⌨ ☺ ☠

//   echo '
// </div>
// </div>
// ';



  echo '
        <script type="text/javascript">
          audio_volume=0.85; //default is 0.7

          MlPlayer("MlPlayer");
        </script>
        <div id="message"></div>
    '
      ;
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

function index() {

    global $dbh,$album_list;

    $stmt = $dbh->query('select count(*) as nb from albums where status=1');
    $row = $stmt->fetch();

    $numberOfAlbums = $row['nb'];

    $script = isset($_SERVER["PHP_SELF"]) ? $_SERVER["PHP_SELF"] : '';

    echo '<title>'.$labelName.' - Free Music</title>
    </head>
    ';

    echo '
    <body id="microlabel-index" class="microlabel-body">
        <div class="microlabel-index">
    ';

    if ($numberOfAlbums < 1) {
echo '
		<div id="horizon">
			<div id="error">
			<img src="img/instruments/horns.png"/>
				<h1 id="error">Uh-oh</h1>
                Something quite wrong happenned. I think you just deleted your Music directory :(
			</div>
		</div>
';

    } else {
        echo '
            <ul id="microlabel">
';
}

    // foreach ($dirList as $key => $albumPath) {
        // $thisAlbumTags = getInfo($albumPath, 'thisAlbumTags');

    foreach ($album_list as $album) {

        $artistName = "";
        foreach ($dbh->query("select distinct artists.name
from
album_detail
inner join songs on album_detail.song_id=songs.id
inner join song_credits on songs.id=song_credits.song_id
inner join artists on artists.id=song_credits.artist_id
where album_detail.album_id=". $album["album_id"]
        ) as $artist){
            if($artistName!="")$artistName.=" &amp; ";
            $artistName.=$artist["name"];
        }
        $albumName = $album['name'];
        $albumGenre = $thisAlbumTags['genre'];
        $albumYear = $thisAlbumTags['year'];
        $thisAlbumSleeve="get_file.php?id=".($album["file_id"]==""?"-4":$album["file_id"]);
        $labelName = isset($thisAlbumTags['organization']) ? $thisAlbumTags['organization'] : 'No Label';
        $labelName = ($labelName == " " || $labelName == "&nbsp;") ? 'No Label' : $thisAlbumTags['organization'];

        $newAlbumSexyUrlElements = explode("/", $dirList[$key]);
        $newAlbumSexyUrl = $newAlbumSexyUrlElements[1].",".$newAlbumSexyUrlElements['2'];
        // $thisAlbumSleeve = getInfo($albumPath, 'thisAlbumSleeve');

        echo '
                <li>
                    <a href="?a='.$album['album_id'].'">
                        <img class="content" src="'.$thisAlbumSleeve.'" alt="'.$artistName.' - '.$albumName.' ('.$labelName.')" />
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


<span id="PaypalSignContainer"></span>
<script src="https://www.paypalobjects.com/js/external/api.js"></script>
<script>
paypal.use( ["login"], function(login) {
  login.render ({
    "appid": "ATH7axAW1bxQT_D7qIxSEDxPZhbnNV5XDfGyTV30y6nNT7EgEKB7-o2zEN4e",
    "authend": "sandbox",
    "scopes": "openid profile email",
    "containerid": "PaypalSignContainer",
    "locale": "en-us",
    "theme": "neutral",
    "returnurl": "http://opensimo.org/play/auth_ok.php"
  });
});
</script>


    ';
}

// browse($dirList, $position) ////////////////////////////////////////
// Construit la logique précédent <= courant => suivant
// Navigate through $dirList items

function browse($album_id,$position) {
    global $album_list;
    reset($album_list);
    if(!is_numeric($album_id))return 0;
    while(current($album_list)["album_id"]!=$album_id)next($album_list);
    if($position=='prev'){
        if(!prev($album_list)) end($album_list);
    }
    else{
        if(!next($album_list)) reset($album_list);
    }
    return current($album_list)["album_id"];
}

function bytestostring($size, $precision = 0) {
    $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'kB', 'B');
    $total = count($sizes);
    while($total-- && $size > 1024) $size /= 1024;
    return round($size, $precision).$sizes[$total];
}


function albumBrowser($album_id) {
    global $album_list;
    $prev_album=browse($album_id,'prev');
    $next_album=browse($album_id,'next');
    $prevAlbumSleeve = "get_file.php?id=".$album_list[$prev_album]["file_id"];
    $nextAlbumSleeve = "get_file.php?id=".$album_list[$next_album]["file_id"];

    echo '
<div id="albumBrowser" class="main transparent" style="position: relative;">
    <div class="left" style="position: relative; z-index: 2;">
        <a title="'.TXT_PREVIOUS_ALBUM.' = '.$album_list[$prev_album]["name"].'" href="?a='.$album_list[$next_album]["file_id"].'">
        <img class="thumb" src="'.$prevAlbumSleeve.'" alt="'.TXT_PREVIOUS_ALBUM.' = '.$album_list[$prev_album]["name"].'" /></a>
    </div>
    <div class="right" style="position: relative; z-index: 2;">
        <div class="fadeAlbums"><strong>'.TXT_NEXT_ALBUM.'</strong>
        </div>
        <a title="'.TXT_NEXT_ALBUM.' = '.$album_list[$prev_album]["name"].'" href="?a='.$album_list[$next_album]["file_id"].'">
        <img class="thumb" src="'.$nextAlbumSleeve.'" alt="'.TXT_NEXT_ALBUM.' = '.$album_list[$next_album]["name"].'" /></a>
    </div>
    <div class="middle" style="position: relative; z-index: 2;">
        <a title="'.$labelName.', '.TXT_BASELINE.'" href="./"><img class="microlabel_logo" src="'.MICROLABEL_LABEL_LOGO.'" alt="label logo" /></a>
    </div>
</div>
<p id="footBr">&nbsp;</p>
    ';
}


// Version Control

function vc($element) {

    $opts = array('http'=>array('method'=>"GET", 'header'=>"User-Agent: microlabel"));

    $context = stream_context_create($opts);
    $current_commits = file_get_contents("https://api.github.com/repos/xaccrocheur/microlabel/commits", false, $context);


    if ($current_commits) {
        $commits = json_decode($current_commits);
        $ref_commit = "93d82d485443462806a295e35316b18b6dca1e87";

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

       '.TXT_DEBUG_HELP_TXT.'

<table id="microlabel-controls">
<tr>
<td>
<a class="microlabel-logo" href="https://github.com/xaccrocheur/microlabel">Microlabel</a>
</td>
<td style="text-align:right;">
<span onClick="document.location.href=\'https://github.com/xaccrocheur/microlabel\'" title="'.vc("message").'" class="version '.vc("class").'">♼</span>

<script id="fbhu2f8">(function(i){var f,s=document.getElementById(i);f=document.createElement("iframe");f.src="//api.flattr.com/button/view/?uid=xaccrocheur&button=compact&url="+encodeURIComponent(document.URL);f.title="Flattr";f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})("fbhu2f8");</script>

<form title="Donate to the μLabel dev team, so it can be better and stay free" style="display:inline;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3ZYW9UL7GACBE">
<input type="image" class="love" src="img/icon_love.png" border="0" name="submit" alt="PayPal">
</form>


</td>
</tr>


</table>


     </div>
   </div>

   <div id="controlFooter" class="zindex-one">
       <a title="'.TXT_FRENCH.'" href="?a='.browse('current', 'nice').'&amp;lang=fr">
         <img class="buttons" alt="'.TXT_FRENCH.'" src="img/flags/FR.png" /></a>
       <a title="'.TXT_ENGLISH.'" href="?a='.browse('current', 'nice').'&amp;lang=en">
         <img class="buttons" alt="'.TXT_ENGLISH.'" src="img/flags/GB.png" /></a>
       <a title="'.TXT_SPANISH.'" href="?a='.browse('current', 'nice').'&amp;lang=es">
         <img class="buttons" alt="'.TXT_SPANISH.'" src="img/flags/ES.png" /></a>
       <a title="'.TXT_GERMAN.'" href="?a='.browse('current', 'nice').'&amp;lang=de">
         <img class="buttons" alt="'.TXT_SPANISH.'" src="img/flags/DE.png" /></a>
       <a title="'.TXT_HELP.'" class="osx" href="#">
         <img class="buttons" src="img/button_help_on.png" alt="'.TXT_HELP.'" /></a>
       <a title="Tag!" href="./?tag">
         <img class="buttons" src="img/button_tag.png" alt="'.TXT_HELP.'" /></a>

<form target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" >
<input type="hidden" name="cmd" value="_cart">
<input type="hidden" name="business" value="XECHR8TM2Y9YN">
<input type="hidden" name="display" value="1">
<input type="image" src="https://www.paypalobjects.com/fr_FR/FR/i/btn/btn_viewcart_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">

<span id="PaypalSignContainer"></span>
<script src="https://www.paypalobjects.com/js/external/api.js"></script>
<script>
paypal.use( ["login"], function(login) {
  login.render ({
    "appid": "ATH7axAW1bxQT_D7qIxSEDxPZhbnNV5XDfGyTV30y6nNT7EgEKB7-o2zEN4e",
    "authend": "sandbox",
    "scopes": "profile email https://uri.paypal.com/services/paypalattributes",
    "containerid": "PaypalSignContainer",
    "locale": "en-us",
    "theme": "neutral",
    "returnurl": "http://opensimo.org/play/"
  });
});
</script>

   </div>
    ';
}

// Query string
$friendlyPath = strip_tags($_GET['a']);
$slash = "/";
$dash = ",";
$meanPath = $rootMusicDir.$slash.str_replace($dash, $slash, $friendlyPath);
$directoryToScan = $meanPath;
$directoryToScan = trim($directoryToScan, $slash);

// $fileList = getInfo($directoryToScan, 'musicFiles');

// Main block
if (!isset($_GET['a'])) {
    index();
} else {
    $album_id = strip_tags($_GET['a']);
    spitTitle($dirList, $fileList);
    audioList($album_id);
    // videoList($fileList, $directoryToScan);
    albumBrowser($album_id);
    fixedFooter($dirList);
}

echo '
</body>
</html>
';

if ($cache) {
    $fp = fopen($cachefile, 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);
    chmod($cachefile, 0764);
    ob_end_flush();
}

?>
