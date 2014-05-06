<?php

////////////////////////////////////////////////////////////////////
// Microlabel copyright 2010-2014 Phil CM <xaccrocheur@gmail.com> //
// licensed GPL3 - http://www.gnu.org/licenses/gpl-3.0.html       //
// Because all music should be free                               //
// Please don't harm nobody w/ this code even if they ask to      //
////////////////////////////////////////////////////////////////////
// tagger.php - Based on demo.browse.php - part of getID3()       //
// Sample script for browsing/scanning files and displaying       //
// information returned by getID3()                               //
// See readme.txt for more details                                //
////////////////////////////////////////////////////////////////////

//die('Due to a security issue, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in demos/'.basename(__FILE__));


/////////////////////////////////////////////////////////////////
// die if magic_quotes_runtime or magic_quotes_gpc are set
if (function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime()) {
	die('magic_quotes_runtime is enabled, getID3 will not run.');
}
if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
	die('magic_quotes_gpc is enabled, getID3 will not run.');
}
/////////////////////////////////////////////////////////////////

$PageEncoding = 'UTF-8';

$writescriptfilename = 'tagger-write.php';

require_once('../libs/getid3/getid3.php');

include('../libs/microlabel.php');

// Needed for windows only
define('GETID3_HELPERAPPSDIR', 'C:/helperapps/');

// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding' => $PageEncoding));

///////////////////////////////////////////////////////////////////////////////

// chmod("/somedir/somefile", 0755)

function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        error_log("Pl0oop!", 0);
        if (chmod($file, 0764)) {
            return true;
        } else {
            microlabelError(TXT_TAGGER_ERROR_PERMISSION, TXT_TAGGER_ERROR_PERMISSION_SUGGESTION);
        }
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function rrmdir($dir, $keep) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (chmod($object, 0755)) {
                    return true;
                } else {
                    microlabelError(TXT_TAGGER_ERROR_PERMISSION, TXT_TAGGER_ERROR_PERMISSION_SUGGESTION);
                }
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        if (!$keep) {
            rmdir($dir);
        }
    }
}

header('Content-Type: text/html; charset=UTF-8');
echo '<!doctype html>
<html>
<head>
    <title>Microlabel - Tagger</title>
    <link rel="shortcut icon" href="'.MICROLABEL_LABEL_LOGO.'" />
    <style type="text/css" media="screen">@import "../libs/css/style.css";</style>
    <script src="../libs/jquery-1.10.2.min.js"></script>
    <script src="../libs/jquery.expandable.js"></script>
    <script type="text/javascript">

$(document).ready(function() {

var fullHtml = $("#main").html();
fullHtml.replace("#error");

    if ( $("#error").is(":visible") ) {
        $("#overlay").show();
        $("#overlay").css("opacity", "0.5");
    }

$("input.input").expandable({
	    		width: 500,
	    		duration: 300,
	    		action: function(val){
	    			alert(val);
	    		}
	    	});

});

    </script>

</head>
<body id="microlabel-tagger" class="microlabel-body">
<div id="overlay" class="overlay"></div>
<div id="main">
';

// echo '('.$lang.')'.$method;

if (isset($_REQUEST['deletedir'])) {
    if (isset($_REQUEST['keep'])) {
        delTree(MICROLABEL_CACHE_DIR);
    } else {
        rrmdir($_REQUEST['deletedir'], false);
    }
}

if (isset($_REQUEST['deletefile'])) {
	if (file_exists($_REQUEST['deletefile'])) {
		if (unlink($_REQUEST['deletefile'])) {
			$deletefilemessage = 'Successfully deleted '.addslashes($_REQUEST['deletefile']);
		} else {
			$deletefilemessage = 'FAILED to delete '.addslashes($_REQUEST['deletefile']).' - error deleting file';
		}
	} else {
		$deletefilemessage = 'FAILED to delete '.addslashes($_REQUEST['deletefile']).' - file does not exist';
	}
	if (isset($_REQUEST['noalert'])) {
		echo $deletefilemessage;
	} else {
		echo '<script type="text/javascript">alert("'.$deletefilemessage.'");</script>';
	}
}


if (isset($_REQUEST['filename'])) {

	if (!file_exists($_REQUEST['filename']) || !is_file($_REQUEST['filename'])) {
		die(getid3_lib::iconv_fallback('ISO-8859-1', 'UTF-8', $_REQUEST['filename'].' does not exist'));
	}
	$starttime = microtime(true);
	$ThisFileInfo = $getID3->analyze($_REQUEST['filename']);
	$AutoGetHashes = (bool) (isset($ThisFileInfo['filesize']) && ($ThisFileInfo['filesize'] > 0) && ($ThisFileInfo['filesize'] < (50 * 1048576))); // auto-get md5_data, md5_file, sha1_data, sha1_file if filesize < 50MB, and NOT zero (which may indicate a file>2GB)
	if ($AutoGetHashes) {
		$ThisFileInfo['md5_file']  = md5_file($_REQUEST['filename']);
		$ThisFileInfo['sha1_file'] = sha1_file($_REQUEST['filename']);
	}

	getid3_lib::CopyTagsToComments($ThisFileInfo);

	$listdirectory = dirname($_REQUEST['filename']);
	$listdirectory = realpath($listdirectory); // get rid of /../../ references

	if (GETID3_OS_ISWINDOWS) {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$listdirectory = str_replace('\\', '/', $listdirectory.'/');
	}

	if (strstr($_REQUEST['filename'], 'http://') || strstr($_REQUEST['filename'], 'ftp://')) {
		echo '<em>Cannot browse remote filesystems</em><br/>';
	} else {
		echo 'Browse: <a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory), ENT_QUOTES).'">'.getid3_lib::iconv_fallback('ISO-8859-1', 'UTF-8', $listdirectory).'</a><br/>';
	}

	getid3_lib::ksort_recursive($ThisFileInfo);
	echo table_var_dump($ThisFileInfo, false, $PageEncoding);
	$endtime = microtime(true);
	echo 'File parsed in '.number_format($endtime - $starttime, 3).' seconds.<br/>';

} else {

	$listdirectory = (isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.');
	$listdirectory = realpath($listdirectory); // get rid of /../../ references
	$currentfulldir = $listdirectory.'/';

	if (GETID3_OS_ISWINDOWS) {
		// this mostly just gives a consistant look to Windows and *nix filesystems
		// (windows uses \ as directory seperator, *nix uses /)
		$currentfulldir = str_replace('\\', '/', $listdirectory.'/');
	}

	ob_start();
	if ($handle = opendir($listdirectory)) {

		ob_end_clean();
		echo str_repeat(' ', 300); // IE buffers the first 300 or so chars, making this progressive display useless - fill the buffer with spaces
		echo '
        <div id="tagger-main">
        <div class="microlabel-message">Processing';

		$starttime = microtime(true);

		$TotalScannedUnknownFiles  = 0;
		$TotalScannedKnownFiles    = 0;
		$TotalScannedPlaytimeFiles = 0;
		$TotalScannedBitrateFiles  = 0;
		$TotalScannedFilesize      = 0;
		$TotalScannedPlaytime      = 0;
		$TotalScannedBitrate       = 0;
		$FilesWithWarnings         = 0;
		$FilesWithErrors           = 0;

		while ($file = readdir($handle)) {
			$currentfilename = $listdirectory.'/'.$file;
			set_time_limit(30); // allocate another 30 seconds to process this file - should go much quicker than this unless intense processing (like bitrate histogram analysis) is enabled
			echo ' .'; // progress indicator dot
			flush();  // make sure the dot is shown, otherwise it's useless

			switch ($file) {
				case '..':
					$ParentDir = realpath($file.'/..').'/';
					if (GETID3_OS_ISWINDOWS) {
						$ParentDir = str_replace('\\', '/', $ParentDir);
					}
					$DirectoryContents[$currentfulldir]['dir'][$file]['filename'] = $ParentDir;
					continue 2;
					break;

				case '.':
					// ignore
					continue 2;
					break;
			}
			// symbolic-link-resolution enhancements by davidbullock״ech-center*com
			$TargetObject     = realpath($currentfilename);  // Find actual file path, resolve if it's a symbolic link
			$TargetObjectType = filetype($TargetObject);     // Check file type without examining extension

			if ($TargetObjectType == 'dir') {

                if (strpos($TargetObject, 'MUSIC')) {
                    $DirectoryContents[$currentfulldir]['dir'][$file]['filename'] = $file;
                }

			} elseif ($TargetObjectType == 'file') {

				$getID3->setOption(array('option_md5_data' => isset($_REQUEST['ShowMD5'])));
				$fileinformation = $getID3->analyze($currentfilename);

				getid3_lib::CopyTagsToComments($fileinformation);

				$TotalScannedFilesize += (isset($fileinformation['filesize']) ? $fileinformation['filesize'] : 0);

                // Security through obscurity

                if (isset($fileinformation["audio"])) {

                    if (isset($_REQUEST['ShowMD5'])) {
                        $fileinformation['md5_file'] = md5_file($currentfilename);
                    }

                    if (!empty($fileinformation['fileformat'])) {
                        $DirectoryContents[$currentfulldir]['known'][$file] = $fileinformation;
                        $TotalScannedPlaytime += (isset($fileinformation['playtime_seconds']) ? $fileinformation['playtime_seconds'] : 0);
                        $TotalScannedBitrate  += (isset($fileinformation['bitrate'])          ? $fileinformation['bitrate']          : 0);
                        $TotalScannedKnownFiles++;
                    } else {
                        $DirectoryContents[$currentfulldir]['other'][$file] = $fileinformation;
                        $DirectoryContents[$currentfulldir]['other'][$file]['playtime_string'] = '-';
                        $TotalScannedUnknownFiles++;
                    }
                    if (isset($fileinformation['playtime_seconds']) && ($fileinformation['playtime_seconds'] > 0)) {
                        $TotalScannedPlaytimeFiles++;
                    }
                    if (isset($fileinformation['bitrate']) && ($fileinformation['bitrate'] > 0)) {
                        $TotalScannedBitrateFiles++;
                    }
                }

                // echo '<pre>';
                // var_dump($fileinformation);
                // echo '</pre>';

			}
		}
		$endtime = microtime(true);
		closedir($handle);
		echo ' Done - Directory scanned in '.number_format($endtime - $starttime, 2).' seconds.<br/>
        </div>
        ';
		flush();

		$columnsintable = 14;

		echo '<table class="microlabel-tagger table">

        <tr>
            <th>'.TAGS_FILE_NAME.'</th>
            <th>'.TAGS_SIZE.'</th>
            <th>'.TAGS_FORMAT.'</th>
            <th>'.TAGS_PLAYTIME.'</th>
            <th>'.TAGS_BITRATE.'</th>
            <th>'.TAGS_ARTIST.'</th>
            <th>'.TAGS_TITLE.'</th>';
			if (isset($_REQUEST['ShowMD5'])) {
				echo '<th>MD5 (File) (<a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.'), ENT_QUOTES).'">on</a>)</th>';
				echo '<th>MD5 (File) (<a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.'), ENT_QUOTES).'">on</a>)</th>';
				echo '<th>MD5 (Source) (<a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.'), ENT_QUOTES).'">on</a>)</th>';
			} else {
				echo '<th colspan="3">MD5&nbsp<a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.rawurlencode(isset($_REQUEST['listdirectory']) ? $_REQUEST['listdirectory'] : '.').'&ShowMD5=1', ENT_QUOTES).'">off</a></th>';
			}
			echo '
            <th>'.TXT_TAGS.'</th>
            <th>'.TXT_ERRORS.'</th>
            <th>'.TXT_EDIT.'</th>
            <th>'.TXT_DELETE.'</th>
        </tr>
';
            $columnsintableMinusOne = $columnsintable - 1;
            $rowcounter = 0;
		foreach ($DirectoryContents as $dirname => $val) {
			if (isset($DirectoryContents[$dirname]['dir']) && is_array($DirectoryContents[$dirname]['dir'])) {
				uksort($DirectoryContents[$dirname]['dir'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['dir'] as $filename => $fileinfo) {
					echo '<tr>';
					if ($filename == '..') {
						echo '<td class="navigation" colspan="'.$columnsintable.'">
<form action="'.htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES).'" method="get">
<img src="../img/icon_folder.png"/>
<input type="submit" value="▲ Up"/><input class="input" style="width:50px;" type="text" name="listdirectory" value="';
						if (GETID3_OS_ISWINDOWS) {
							echo htmlentities(str_replace('\\', '/', realpath($dirname.$filename)), ENT_QUOTES);
						} else {
							echo htmlentities(realpath($dirname.$filename), ENT_QUOTES);
						}
						echo '">
<div class="tagger-path"><span>'.getid3_lib::iconv_fallback('ISO-8859-1', 'UTF-8', $currentfulldir);
						echo '</span></div></form></td>';
					} else {
						echo '<td class="directory" colspan="'.$columnsintableMinusOne.'"><img src="../img/icon_folder.png"/> <span class="right"><a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.urlencode($dirname.$filename), ENT_QUOTES).'">'.htmlentities($filename).'</a></span>

</td><td><a href="'.htmlentities($_SERVER['PHP_SELF'].'?deletedir='.urlencode($dirname.$filename), ENT_QUOTES).'" onClick="return confirm(\''.TXT_TAGGER_WARNING_DELETE.' ('.addslashes(htmlentities($dirname.$filename)).')\');">'.TXT_DELETE.'</a></td>';
					}
					echo '</tr>';
				}

			}

			if (isset($DirectoryContents[$dirname]['known']) && is_array($DirectoryContents[$dirname]['known'])) {
				uksort($DirectoryContents[$dirname]['known'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['known'] as $filename => $fileinfo) {

					echo '<tr>';
					echo '<td class="audiofile"><img src="../img/icon_audiofile.png"/> <a href="'.htmlentities($_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename), ENT_QUOTES).'" title="View detailed analysis">'.htmlentities($filename).'</a></td>';
					echo '<td class="right">'.number_format($fileinfo['filesize']).'</td>';
					echo '<td class="right">'.NiceDisplayFiletypeFormat($fileinfo).'</td>';
					echo '<td class="right">'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</td>';
					echo '<td class="right">'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000, 0, ((isset($fileinfo['audio']['bitrate_mode']) && ($fileinfo['audio']['bitrate_mode'] == 'vbr')) ? true : false)) : '-').'</td>';
					echo '<td>'.(isset($fileinfo['comments_html']['artist']) ? implode('<br/>', $fileinfo['comments_html']['artist']) : ((isset($fileinfo['video']['resolution_x']) && isset($fileinfo['video']['resolution_y'])) ? $fileinfo['video']['resolution_x'].'x'.$fileinfo['video']['resolution_y'] : '')).'</td>';
					echo '<td>'.(isset($fileinfo['comments_html']['title'])  ? implode('<br/>', $fileinfo['comments_html']['title'])  :  (isset($fileinfo['video']['frame_rate'])                                                 ? number_format($fileinfo['video']['frame_rate'], 3).'fps'                  : '')).'</td>';
					if (isset($_REQUEST['ShowMD5'])) {
						echo '<td><tt>'.(isset($fileinfo['md5_file'])        ? $fileinfo['md5_file']        : '&nbsp;').'</tt></td>';
						echo '<td><tt>'.(isset($fileinfo['md5_data'])        ? $fileinfo['md5_data']        : '&nbsp;').'</tt></td>';
						echo '<td><tt>'.(isset($fileinfo['md5_data_source']) ? $fileinfo['md5_data_source'] : '&nbsp;').'</tt></td>';
					} else {
						echo '<td align="center" colspan="3">-</td>';
					}
					echo '<td>'.(!empty($fileinfo['tags']) ? implode(', ', array_keys($fileinfo['tags'])) : '').'</td>';

					echo '<td>';
					if (!empty($fileinfo['warning'])) {
						$FilesWithWarnings++;
						echo '<a href="#" onClick="alert(\''.htmlentities(implode('\\n', $fileinfo['warning']), ENT_QUOTES).'\'); return false;" title="'.htmlentities(implode("; \n", $fileinfo['warning']), ENT_QUOTES).'">warning</a><br/>';
					}
					if (!empty($fileinfo['error'])) {
						$FilesWithErrors++;
						echo '<a href="#" onClick="alert(\''.htmlentities(implode('\\n', $fileinfo['error']), ENT_QUOTES).'\'); return false;" title="'.htmlentities(implode("; \n", $fileinfo['error']), ENT_QUOTES).'">error</a><br/>';
					}
					echo '</td>';

					echo '<td>';
					$fileinfo['fileformat'] = (isset($fileinfo['fileformat']) ? $fileinfo['fileformat'] : '');
					switch ($fileinfo['fileformat']) {
						case 'mp3':
						case 'mp2':
						case 'mp1':
						case 'flac':
						case 'mpc':
						case 'real':
							echo '<a href="'.htmlentities($writescriptfilename.'?Filename='.urlencode($dirname.$filename), ENT_QUOTES).'" title="Edit tags">edit&nbsp;tags</a>';
							break;
						case 'ogg':
							if (isset($fileinfo['audio']['dataformat']) && ($fileinfo['audio']['dataformat'] == 'vorbis')) {
								echo '<a href="'.htmlentities($writescriptfilename.'?Filename='.urlencode($dirname.$filename), ENT_QUOTES).'" title="Edit tags">edit&nbsp;tags</a>';
							}
							break;
						default:
							break;
					}
					echo '</td>';
					echo '<td><a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename), ENT_QUOTES).'" onClick="return confirm(\'Are you sure you want to delete '.addslashes(htmlentities($dirname.$filename)).'? \n(this action cannot be un-done)\');" title="'.htmlentities('Permanently delete '."\n".$filename."\n".' from'."\n".' '.$dirname, ENT_QUOTES).'">'.TXT_DELETE.'</a></td>';
					echo '</tr>';
				}
			}

			if (isset($DirectoryContents[$dirname]['other']) && is_array($DirectoryContents[$dirname]['other'])) {
				uksort($DirectoryContents[$dirname]['other'], 'MoreNaturalSort');
				foreach ($DirectoryContents[$dirname]['other'] as $filename => $fileinfo) {
					echo '<tr>';
					echo '<td><a href="'.htmlentities($_SERVER['PHP_SELF'].'?filename='.urlencode($dirname.$filename), ENT_QUOTES).'"><em>'.htmlentities($filename).'</em></a></td>';
					echo '<td class="right">'.(isset($fileinfo['filesize']) ? number_format($fileinfo['filesize']) : '-').'</td>';
					echo '<td class="right">'.NiceDisplayFiletypeFormat($fileinfo).'</td>';
					echo '<td class="right">'.(isset($fileinfo['playtime_string']) ? $fileinfo['playtime_string'] : '-').'</td>';
					echo '<td class="right">'.(isset($fileinfo['bitrate']) ? BitrateText($fileinfo['bitrate'] / 1000) : '-').'</td>';
					echo '<td></td>'; // Artist
					echo '<td></td>'; // Title
					echo '<td align="left" colspan="3">&nbsp;</td>'; // MD5_data
					echo '<td></td>'; // Tags

					//echo '<td></td>'; // Warning/Error
					echo '<td>';
					if (!empty($fileinfo['warning'])) {
						$FilesWithWarnings++;
						echo '<a href="#" onClick="alert(\''.htmlentities(implode('\\n', $fileinfo['warning']), ENT_QUOTES).'\'); return false;" title="'.htmlentities(implode("\n", $fileinfo['warning']), ENT_QUOTES).'">warning</a><br/>';
					}
					if (!empty($fileinfo['error'])) {
						if ($fileinfo['error'][0] != 'unable to determine file format') {
							$FilesWithErrors++;
							echo '<a href="#" onClick="alert(\''.htmlentities(implode('\\n', $fileinfo['error']), ENT_QUOTES).'\'); return false;" title="'.htmlentities(implode("\n", $fileinfo['error']), ENT_QUOTES).'">error</a><br/>';
						}
					}
					echo '</td>';

					echo '<td></td>'; // Edit
					echo '<td><a href="'.htmlentities($_SERVER['PHP_SELF'].'?listdirectory='.urlencode($listdirectory).'&deletefile='.urlencode($dirname.$filename), ENT_QUOTES).'" onClick="return confirm(\'Are you sure you want to delete '.addslashes($dirname.$filename).'? \n(this action cannot be un-done)\');" title="Permanently delete '.addslashes($dirname.$filename).'">'.TXT_DELETE.'</a></td>';
					echo '</tr>';
				}
			}

			echo '<tr>';
			echo '<td>Average:</td>';
			echo '<td class="right">'.number_format($TotalScannedFilesize / max($TotalScannedKnownFiles, 1)).'</td>';
			echo '<td>&nbsp;</td>';
			echo '<td class="right">'.getid3_lib::PlaytimeString($TotalScannedPlaytime / max($TotalScannedPlaytimeFiles, 1)).'</td>';
			echo '<td class="right">'.BitrateText(round(($TotalScannedBitrate / 1000) / max($TotalScannedBitrateFiles, 1))).'</td>';
			echo '<td rowspan="2" colspan="'.($columnsintable - 5).'"><table id="small-table"><tr><th class="right">Identified Files:</th><td class="right">'.number_format($TotalScannedKnownFiles).'</td><td>&nbsp;&nbsp;&nbsp;</td><th class="right">Errors:</th><td class="right">'.number_format($FilesWithErrors).'</td></tr><tr><th class="right">Unknown Files:</th><td class="right">'.number_format($TotalScannedUnknownFiles).'</td><td>&nbsp;&nbsp;&nbsp;</td><th class="right">Warnings:</th><td class="right">'.number_format($FilesWithWarnings).'</td></tr></table>';
			echo '</tr>';
			echo '<tr>';
			echo '<td>Total:</td>';
			echo '<td class="right">'.number_format($TotalScannedFilesize).'</td>';
			echo '<td>&nbsp;</td>';
			echo '<td class="right">'.getid3_lib::PlaytimeString($TotalScannedPlaytime).'</td>';
			echo '<td>&nbsp;</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		$errormessage = ob_get_contents();
		ob_end_clean();
        $errorString = 'Could not open directory <strong>'.$_GET['listdirectory'].'</strong>';
        microlabelError($errorString);
	}
}

echo '
</div>

<div id="albumBrowser" class="main transparent" style="position: relative;">
    <div class="left" style="position: relative; z-index: 2;">
    </div>
    <div class="right" style="position: relative; z-index: 2;">
    </div>
    <div class="middle" style="position: relative; z-index: 2;">
        <a title="'.$labelName.', '.TXT_BASELINE.'" href="'.MICROLABEL_ROOT_DIR.'"><img style="width:60px" src="'.MICROLABEL_LABEL_LOGO.'" alt="label logo" /></a>
    </div>
</div>

'.PoweredBygetID3().'
</div>
</body>
</html>';


/////////////////////////////////////////////////////////////////


function RemoveAccents($string) {
	// Revised version by markstewardרotmail*com
	// Again revised by James Heinrich (19-June-2006)
	return strtr(
		strtr(
			$string,
			"\x8A\x8E\x9A\x9E\x9F\xC0\xC1\xC2\xC3\xC4\xC5\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD1\xD2\xD3\xD4\xD5\xD6\xD8\xD9\xDA\xDB\xDC\xDD\xE0\xE1\xE2\xE3\xE4\xE5\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF1\xF2\xF3\xF4\xF5\xF6\xF8\xF9\xFA\xFB\xFC\xFD\xFF",
			'SZszYAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy'
		),
		array(
			"\xDE" => 'TH',
			"\xFE" => 'th',
			"\xD0" => 'DH',
			"\xF0" => 'dh',
			"\xDF" => 'ss',
			"\x8C" => 'OE',
			"\x9C" => 'oe',
			"\xC6" => 'AE',
			"\xE6" => 'ae',
			"\xB5" => 'u'
		)
	);
}


function BitrateColor($bitrate, $BitrateMaxScale=768) {
	// $BitrateMaxScale is bitrate of maximum-quality color (bright green)
	// below this is gradient, above is solid green

	$bitrate *= (256 / $BitrateMaxScale); // scale from 1-[768]kbps to 1-256
	$bitrate = round(min(max($bitrate, 1), 256));
	$bitrate--;    // scale from 1-256kbps to 0-255kbps

	$Rcomponent = max(255 - ($bitrate * 2), 0);
	$Gcomponent = max(($bitrate * 2) - 255, 0);
	if ($bitrate > 127) {
		$Bcomponent = max((255 - $bitrate) * 2, 0);
	} else {
		$Bcomponent = max($bitrate * 2, 0);
	}
	return str_pad(dechex($Rcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Gcomponent), 2, '0', STR_PAD_LEFT).str_pad(dechex($Bcomponent), 2, '0', STR_PAD_LEFT);
}

function BitrateText($bitrate, $decimals=0, $vbr=false) {
	return '<span style="color: #'.BitrateColor($bitrate).($vbr ? '; font-weight: bold;' : '').'">'.number_format($bitrate, $decimals).' k</span>';
}

function string_var_dump($variable) {
	if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
		return print_r($variable, true);
	}
	ob_start();
	var_dump($variable);
	$dumpedvariable = ob_get_contents();
	ob_end_clean();
	return $dumpedvariable;
}

function table_var_dump($variable, $wrap_in_td=false, $encoding='ISO-8859-1') {
	$returnstring = '';
	switch (gettype($variable)) {
		case 'array':
			$returnstring .= ($wrap_in_td ? '<td>' : '');
			$returnstring .= '<table class="dump">';
			foreach ($variable as $key => $value) {
				$returnstring .= '<tr><td valign="top">'.str_replace("\x00", ' ', $key).'</td>';
				$returnstring .= '<td valign="top">'.gettype($value);
				if (is_array($value)) {
					$returnstring .= '&nbsp;('.count($value).')';
				} elseif (is_string($value)) {
					$returnstring .= '&nbsp;('.strlen($value).')';
				}
				//if (($key == 'data') && isset($variable['image_mime']) && isset($variable['dataoffset'])) {
				if (($key == 'data') && isset($variable['image_mime'])) {
					$imageinfo = array();
					$imagechunkcheck = getid3_lib::GetDataImageSize($value, $imageinfo);
					$returnstring .= '</td><td><img src="data:'.$variable['image_mime'].';base64,'.base64_encode($value).'" width="'.$imagechunkcheck[0].'" height="'.$imagechunkcheck[1].'"></td></tr>';
				} else {
					$returnstring .= '</td>'.table_var_dump($value, true, $encoding).'</tr>';
				}
			}
			$returnstring .= '</table>';
			$returnstring .= ($wrap_in_td ? '</td>' : '');
			break;

		case 'boolean':
			$returnstring .= ($wrap_in_td ? '<td class="dump_boolean">' : '').($variable ? 'TRUE' : 'FALSE').($wrap_in_td ? '</td>' : '');
			break;

		case 'integer':
			$returnstring .= ($wrap_in_td ? '<td class="dump_integer">' : '').$variable.($wrap_in_td ? '</td>' : '');
			break;

		case 'double':
		case 'float':
			$returnstring .= ($wrap_in_td ? '<td class="dump_double">' : '').$variable.($wrap_in_td ? '</td>' : '');
			break;

		case 'object':
		case 'null':
			$returnstring .= ($wrap_in_td ? '<td>' : '').string_var_dump($variable).($wrap_in_td ? '</td>' : '');
			break;

		case 'string':
			$variable = str_replace("\x00", ' ', $variable);
			$varlen = strlen($variable);
			for ($i = 0; $i < $varlen; $i++) {
				$returnstring .= htmlentities($variable{$i}, ENT_QUOTES, $encoding);
			}
			$returnstring = ($wrap_in_td ? '<td class="dump_string">' : '').nl2br($returnstring).($wrap_in_td ? '</td>' : '');
			break;

		default:
			$imageinfo = array();
			$imagechunkcheck = getid3_lib::GetDataImageSize($variable, $imageinfo);
			if (($imagechunkcheck[2] >= 1) && ($imagechunkcheck[2] <= 3)) {
				$returnstring .= ($wrap_in_td ? '<td>' : '');
				$returnstring .= '<table class="dump">';
				$returnstring .= '<tr><td>type</td><td>'.getid3_lib::ImageTypesLookup($imagechunkcheck[2]).'</td></tr>';
				$returnstring .= '<tr><td>width</td><td>'.number_format($imagechunkcheck[0]).' px</td></tr>';
				$returnstring .= '<tr><td>height</td><td>'.number_format($imagechunkcheck[1]).' px</td></tr>';
				$returnstring .= '<tr><td>size</td><td>'.number_format(strlen($variable)).' bytes</td></tr>
                </table>
                </div>
';
				$returnstring .= ($wrap_in_td ? '</td>' : '');
			} else {
				$returnstring .= ($wrap_in_td ? '<td>' : '').nl2br(htmlspecialchars(str_replace("\x00", ' ', $variable))).($wrap_in_td ? '</td>' : '');
			}
			break;
	}
	return $returnstring;
}


function NiceDisplayFiletypeFormat(&$fileinfo) {

	if (empty($fileinfo['fileformat'])) {
		return '-';
	}

	$output  = $fileinfo['fileformat'];
	if (empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		return $output;  // 'gif'
	}
	if (empty($fileinfo['video']['dataformat']) && !empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['audio']['dataformat']) {
			return $output; // 'mp3'
		}
		$output .= '.'.$fileinfo['audio']['dataformat']; // 'ogg.flac'
		return $output;
	}
	if (!empty($fileinfo['video']['dataformat']) && empty($fileinfo['audio']['dataformat'])) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'mpeg'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // 'riff.avi'
		return $output;
	}
	if ($fileinfo['video']['dataformat'] == $fileinfo['audio']['dataformat']) {
		if ($fileinfo['fileformat'] == $fileinfo['video']['dataformat']) {
			return $output; // 'real'
		}
		$output .= '.'.$fileinfo['video']['dataformat']; // any examples?
		return $output;
	}
	$output .= '.'.$fileinfo['video']['dataformat'];
	$output .= '.'.$fileinfo['audio']['dataformat']; // asf.wmv.wma
	return $output;

}

function MoreNaturalSort($ar1, $ar2) {
	if ($ar1 === $ar2) {
		return 0;
	}
	$len1     = strlen($ar1);
	$len2     = strlen($ar2);
	$shortest = min($len1, $len2);
	if (substr($ar1, 0, $shortest) === substr($ar2, 0, $shortest)) {
		// the shorter argument is the beginning of the longer one, like "str" and "string"
		if ($len1 < $len2) {
			return -1;
		} elseif ($len1 > $len2) {
			return 1;
		}
		return 0;
	}
	$ar1 = RemoveAccents(strtolower(trim($ar1)));
	$ar2 = RemoveAccents(strtolower(trim($ar2)));
	$translatearray = array('\''=>'', '"'=>'', '_'=>' ', '('=>'', ')'=>'', '-'=>' ', '  '=>' ', '.'=>'', ','=>'');
	foreach ($translatearray as $key => $val) {
		$ar1 = str_replace($key, $val, $ar1);
		$ar2 = str_replace($key, $val, $ar2);
	}

	if ($ar1 < $ar2) {
		return -1;
	} elseif ($ar1 > $ar2) {
		return 1;
	}
	return 0;
}

function PoweredBygetID3($string='') {
	global $getID3;

    $i = 0;
    $dir = MICROLABEL_CACHE_DIR;
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false){
            if (!in_array($file, array('.', '..')) && !is_dir($dir.$file))
                $i++;
        }
    }
    // prints out how many were in the directory
	if (!$string) {
		$string = '
        <div class="powered-by">
                <p>Powered by <a href="http://getid3.sourceforge.net">getID3() v<!--GETID3VER--><br/>http://getid3.sourceforge.net</a><br />Running on PHP v'.phpversion().' ('.(ceil(log(PHP_INT_MAX, 2)) + 1).'-bit)</p>
                <p>('.$i.') pages cached in '.MICROLABEL_CACHE_DIR.'</p>
                <p style="text-align:center;">
                <a href="'.htmlentities($_SERVER['PHP_SELF']).'?deletedir='.MICROLABEL_CACHE_DIR.'/&keep=false" onClick="return confirm(\''.TXT_TAGGER_WARNING_DELETE.' ('.MICROLABEL_CACHE_DIR.')\');">
                <img src="../img/icon_empty_cache.png" alt="'.TXT_EMPTY_CACHE_DIR.'" title="'.TXT_EMPTY_CACHE_DIR.'"><br />
                '.TXT_EMPTY_CACHE_DIR.'
                </a>

</p>

        </div>';
	}
	return str_replace('<!--GETID3VER-->', $getID3->version(), $string);
}

// echo '<pre>';
// var_dump($_SERVER);
// echo '</pre>';

?>
