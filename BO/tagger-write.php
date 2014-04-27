<?php
/////////////////////////////////////////////////////////////////
/// getID3() by James Heinrich <info@getid3.org>               //
//  available at http://getid3.sourceforge.net                 //
//            or http://www.getid3.org                         //
/////////////////////////////////////////////////////////////////
//                                                             //
// /demo/demo.write.php - part of getID3()                     //
// sample script for demonstrating writing ID3v1 and ID3v2     //
// tags for MP3, or Ogg comment tags for Ogg Vorbis            //
// See readme.txt for more details                             //
//                                                            ///
/////////////////////////////////////////////////////////////////


//die('Due to a security issue, this demo has been disabled. It can be enabled by removing line '.__LINE__.' in '.$_SERVER['PHP_SELF']);

$TaggingFormat = 'UTF-8';

header('Content-Type: text/html; charset='.$TaggingFormat);
echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>Microlabel - Tag writer</title>
    <style type="text/css" media="screen">@import "../css/style.css";</style>
    <body id="microlabel-tagger" class="microlabel-body">
<div id="tagger-main">
';

require_once('../libs/getid3/getid3.php');
// require_once('../libs/microlabel.php');

// Initialize getID3 engine
$getID3 = new getID3;
$getID3->setOption(array('encoding'=>$TaggingFormat));

getid3_lib::IncludeDependency(GETID3_INCLUDEPATH.'write.php', __FILE__, true);

$browsescriptfilename = 'tagger.php';

$Filename = (isset($_REQUEST['Filename']) ? $_REQUEST['Filename'] : '');

if (isset($_POST['WriteTags'])) {

	$TagFormatsToWrite = (isset($_POST['TagFormatsToWrite']) ? $_POST['TagFormatsToWrite'] : array());
	if (!empty($TagFormatsToWrite)) {
		echo 'starting to write tag(s)<BR>';

		$tagwriter = new getid3_writetags;
		$tagwriter->filename       = $Filename;
		$tagwriter->tagformats     = $TagFormatsToWrite;
		$tagwriter->overwrite_tags = true;
		$tagwriter->tag_encoding   = $TaggingFormat;
		if (!empty($_POST['remove_other_tags'])) {
			$tagwriter->remove_other_tags = true;
		}

		$commonkeysarray = array('Title', 'Artist', 'Album', 'Year', 'Comment');
		foreach ($commonkeysarray as $key) {
			if (!empty($_POST[$key])) {
				$TagData[strtolower($key)][] = $_POST[$key];
			}
		}
		if (!empty($_POST['Genre'])) {
			$TagData['genre'][] = $_POST['Genre'];
		}
		if (!empty($_POST['GenreOther'])) {
			$TagData['genre'][] = $_POST['GenreOther'];
		}
		if (!empty($_POST['Track'])) {
			$TagData['track'][] = $_POST['Track'].(!empty($_POST['TracksTotal']) ? '/'.$_POST['TracksTotal'] : '');
		}

		if (!empty($_FILES['userfile']['tmp_name'])) {
			if (in_array('id3v2.4', $tagwriter->tagformats) || in_array('id3v2.3', $tagwriter->tagformats) || in_array('id3v2.2', $tagwriter->tagformats)) {
				if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
					ob_start();
					if ($fd = fopen($_FILES['userfile']['tmp_name'], 'rb')) {
						ob_end_clean();
						$APICdata = fread($fd, filesize($_FILES['userfile']['tmp_name']));
						fclose ($fd);

						list($APIC_width, $APIC_height, $APIC_imageTypeID) = GetImageSize($_FILES['userfile']['tmp_name']);
						$imagetypes = array(1=>'gif', 2=>'jpeg', 3=>'png');
						if (isset($imagetypes[$APIC_imageTypeID])) {

							$TagData['attached_picture'][0]['data']          = $APICdata;
							$TagData['attached_picture'][0]['picturetypeid'] = $_POST['APICpictureType'];
							$TagData['attached_picture'][0]['description']   = $_FILES['userfile']['name'];
							$TagData['attached_picture'][0]['mime']          = 'image/'.$imagetypes[$APIC_imageTypeID];

						} else {
							echo 'invalid image format (only GIF, JPEG, PNG)<br>';
						}
					} else {
						$errormessage = ob_get_contents();
						ob_end_clean();
						echo 'cannot open '.$_FILES['userfile']['tmp_name'].'<br>';
					}
				} else {
					echo '!is_uploaded_file('.$_FILES['userfile']['tmp_name'].')<br>';
				}
			} else {
				echo 'WARNING: Can only embed images for ID3v2<br>';
			}
		}

		$tagwriter->tag_data = $TagData;
		if ($tagwriter->WriteTags()) {
			echo 'Successfully wrote tags<BR>';
			if (!empty($tagwriter->warnings)) {
				echo 'There were some warnings:<blockquote style="background-color:#FFCC33; padding: 10px;">'.implode('<BR><BR>', $tagwriter->warnings).'</BLOCKQUOTE>';
			}
		} else {
            $errorString = 'Failed to write tags! '.implode('<br />', $tagwriter->errors);
            // microlabelError($errorString);
		}

	} else {

		echo 'WARNING: no tag formats selected for writing - nothing written';

	}
	echo '<hr>';
}


echo '<div class="microlabel-message"><a href="'.htmlentities($browsescriptfilename.'?listdirectory='.rawurlencode(realpath(dirname($Filename))), ENT_QUOTES).'">Browse current directory</a> | ';
if (!empty($Filename)) {
	echo '<a href="'.htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES).'">Start Over</a><form action="'.htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES).'" method="post" enctype="multipart/form-data"></div>
<table class="table">
<tr class="microlabel-table"><th>Filename:</th><td><input type="hidden" name="Filename" value="'.htmlentities($Filename, ENT_QUOTES).'"><a href="'.htmlentities($browsescriptfilename.'?filename='.rawurlencode($Filename), ENT_QUOTES).'" target="_blank">'.$Filename.'</a></td></tr>';
	if (file_exists($Filename)) {

		// Initialize getID3 engine
		$getID3 = new getID3;
		$OldThisFileInfo = $getID3->analyze($Filename);
		getid3_lib::CopyTagsToComments($OldThisFileInfo);

		switch ($OldThisFileInfo['fileformat']) {
			case 'mp3':
			case 'mp2':
			case 'mp1':
				$ValidTagTypes = array('id3v1', 'id3v2.3', 'ape');
				break;

			case 'mpc':
				$ValidTagTypes = array('ape');
				break;

			case 'ogg':
				if (!empty($OldThisFileInfo['audio']['dataformat']) && ($OldThisFileInfo['audio']['dataformat'] == 'flac')) {
					//$ValidTagTypes = array('metaflac');
					// metaflac doesn't (yet) work with OggFLAC files
					$ValidTagTypes = array();
				} else {
					$ValidTagTypes = array('vorbiscomment');
				}
				break;

			case 'flac':
				$ValidTagTypes = array('metaflac');
				break;

			case 'real':
				$ValidTagTypes = array('real');
				break;

			default:
				$ValidTagTypes = array();
				break;
		}
		echo '<tr><td>Title</td> <td><input type="text" size="40" name="Title"  value="'.htmlentities((!empty($OldThisFileInfo['comments']['title'])  ? implode(', ', $OldThisFileInfo['comments']['title'] ) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td>Artist</td><td><input type="text" size="40" name="Artist" value="'.htmlentities((!empty($OldThisFileInfo['comments']['artist']) ? implode(', ', $OldThisFileInfo['comments']['artist']) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td>Album</td> <td><input type="text" size="40" name="Album"  value="'.htmlentities((!empty($OldThisFileInfo['comments']['album'])  ? implode(', ', $OldThisFileInfo['comments']['album'] ) : ''), ENT_QUOTES).'"></td></tr>';
		echo '<tr><td>Year</td>  <td><input type="text" size="4"  name="Year"   value="'.htmlentities((!empty($OldThisFileInfo['comments']['year'])   ? implode(', ', $OldThisFileInfo['comments']['year']  ) : ''), ENT_QUOTES).'"></td></tr>';

		$TracksTotal = '';
		$TrackNumber = '';
		if (!empty($OldThisFileInfo['comments']['track_number']) && is_array($OldThisFileInfo['comments']['track_number'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['track_number'];
		} elseif (!empty($OldThisFileInfo['comments']['track']) && is_array($OldThisFileInfo['comments']['track'])) {
			$RawTrackNumberArray = $OldThisFileInfo['comments']['track'];
		} else {
			$RawTrackNumberArray = array();
		}
		foreach ($RawTrackNumberArray as $key => $value) {
			if (strlen($value) > strlen($TrackNumber)) {
				// ID3v1 may store track as "3" but ID3v2/APE would store as "03/16"
				$TrackNumber = $value;
			}
		}
		if (strstr($TrackNumber, '/')) {
			list($TrackNumber, $TracksTotal) = explode('/', $TrackNumber);
		}
		echo '<tr><td>Track</td><td><input type="text" size="2" name="Track" value="'.htmlentities($TrackNumber, ENT_QUOTES).'"> of <input type="text" size="2" name="TracksTotal" value="'.htmlentities($TracksTotal, ENT_QUOTES).'"></TD></TR>';

		$ArrayOfGenresTemp = getid3_id3v1::ArrayOfGenres();   // get the array of genres
		foreach ($ArrayOfGenresTemp as $key => $value) {      // change keys to match displayed value
			$ArrayOfGenres[$value] = $value;
		}
		unset($ArrayOfGenresTemp);                            // remove temporary array
		unset($ArrayOfGenres['Cover']);                       // take off these special cases
		unset($ArrayOfGenres['Remix']);
		unset($ArrayOfGenres['Unknown']);
		$ArrayOfGenres['']      = '- Unknown -';              // Add special cases back in with renamed key/value
		$ArrayOfGenres['Cover'] = '-Cover-';
		$ArrayOfGenres['Remix'] = '-Remix-';
		asort($ArrayOfGenres);                                // sort into alphabetical order
		echo '<tr><th>Genre</th><td><select name="Genre">';
		$AllGenresArray = (!empty($OldThisFileInfo['comments']['genre']) ? $OldThisFileInfo['comments']['genre'] : array());
		foreach ($ArrayOfGenres as $key => $value) {
			echo '<option value="'.htmlentities($key, ENT_QUOTES).'"';
			if (in_array($key, $AllGenresArray)) {
				echo ' selected="selected"';
				unset($AllGenresArray[array_search($key, $AllGenresArray)]);
				sort($AllGenresArray);
			}
			echo '>'.htmlentities($value).'</option>';
		}
		echo '</select><input type="text" name="GenreOther" size="10" value="'.htmlentities((!empty($AllGenresArray[0]) ? $AllGenresArray[0] : ''), ENT_QUOTES).'"></td></tr>';

		echo '<tr><td>Write Tags</td><td>';
		foreach ($ValidTagTypes as $ValidTagType) {
			echo '<input type="checkbox" name="TagFormatsToWrite[]" value="'.$ValidTagType.'"';
			if (count($ValidTagTypes) == 1) {
				echo ' checked="checked"';
			} else {
				switch ($ValidTagType) {
					case 'id3v2.2':
					case 'id3v2.3':
					case 'id3v2.4':
						if (isset($OldThisFileInfo['tags']['id3v2'])) {
							echo ' checked="checked"';
						}
						break;

					default:
						if (isset($OldThisFileInfo['tags'][$ValidTagType])) {
							echo ' checked="checked"';
						}
						break;
				}
			}
			echo '>'.$ValidTagType.'<br>';
		}
		if (count($ValidTagTypes) > 1) {
			echo '<hr><input type="checkbox" name="remove_other_tags" value="1"> Remove non-selected tag formats when writing new tag<br>';
		}
		echo '</td></tr>';

		echo '<tr><td>Comment</td><td><textarea cols="55" rows="3" name="Comment" wrap="virtual">'.((isset($OldThisFileInfo['comments']['comment']) && is_array($OldThisFileInfo['comments']['comment'])) ? implode("\n", $OldThisFileInfo['comments']['comment']) : '').'</textarea></td></tr>';

		echo '<tr><td>Picture<br>(ID3v2 only)</td><td><input type="file" name="userfile" accept="image/jpeg, image/gif, image/png"><br>';
		echo '<select name="APICpictureType">';
		$APICtypes = getid3_id3v2::APICPictureTypeLookup('', true);
		foreach ($APICtypes as $key => $value) {
			echo '<option value="'.htmlentities($key, ENT_QUOTES).'">'.htmlentities($value).'</option>';
		}
		echo '</select></td></tr>';
		echo '<tr><td align="center" colspan="2"><input type="submit" name="WriteTags" value="Save"> ';
		echo '<input type="reset" value="Reset"></td></tr>';

	} else {

		echo '<tr><td>Error</td><td>'.htmlentities($Filename).' does not exist</td></tr>';

	}
	echo '
          </div>
          </table>
          </form>';

}

?>
</body>
</html>