<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>MSK File List</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css" media="screen">
* { margin : 0; padding : 0; }
body { font:9pt helvetiva, arial, sans-serif; background: #000; color: #fff; }
h4 , p { margin : 5px 0 3px 0; }
div { border-bottom : 1px solid black; padding : 5px 5px; color: #000; }
div.mainInterface * { color: #fff; }
div.weirdFile { background: #FF9999; }
div.xmlFile { background: #BFBFBF; }
div.musicFile { background: #E2FFF0; }
div.imgFile { background: #D2C0E0; }
strong { color: #3D332E; }
</style>
</head>
<body>
<?php
require_once('libs/getid3/getid3.php');

if (isset($_GET['code'])) { die(highlight_file(__FILE__, 1)); }

$albums = './ALBUMS2';
$dir_iterator = new RecursiveDirectoryIterator($albums);
$iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
// could use CHILD_FIRST if you so wish
// trigger_error('Oops',E_USER_WARNING);
$size = 0;
$getID3 = new getID3;
$albumName ='';
echo '<div class="mainInterface"><h1>MSK : All files</h1></div>';
foreach ($iterator as $file) {
   if ($file->isFile()) {
      $files[] = $file;
      $mime = explode(" ", file_mime_type($file));
      if ($mime[0] !== 'JPEG' && $mime[0] !== 'GIF' && $mime[0] !== 'PNG' && $mime[0] !== 'MP3' && $mime[0] !== 'Ogg' && $mime[0] !== 'XML' && $mime[0] !== 'GIF' && $mime[0] !== 'MPEG') {
         $weirdFiles[] = $file;
         echo '<div class="weirdFile">';
         echo 'File : <em>('.$file->getPathname().'</em><br />';
         $size += $file->getSize();
         echo 'MIME : <strong>'.$mime[0].' ('.file_mime_type($file).')</strong><br />';
         echo $file->getSize().' Bytes / Modified '.date('Y.m.d-H:i:s', $file->getMTime()).'</div>';
      }
      if ($mime[0] == 'MP3') {
         $musicFiles[] = $files;
         $size += $file->getSize();
         $ThisFileInfo = $getID3->analyze($file);
         getid3_lib::CopyTagsToComments($ThisFileInfo);
         $musicFile[album] = $ThisFileInfo['comments_html']['album'][0];
         $musicFile[title] = $ThisFileInfo['comments_html']['title'][0];
         $musicFile[artist] = $ThisFileInfo['comments_html']['artist'][0];
         $musicFile[genre] = $ThisFileInfo['comments_html']['genre'][0];
         $musicFile[year] = $ThisFileInfo['comments_html']['year'][0];
         $musicFile[size] = $fileSize = $file->getSize();
         $musicFile[path] = $file->getPathname();
         $musicFile[mtime] = $fileMtime = date('Y.m.d-H:i:s', $file->getMTime());
         $musicFile[mime] = $fileMimeType = $mime[0];
         echo '<div class="musicFile">';
//          echo 'Artist : <strong>('.$artistTag.')</strong><br />';
//          echo 'Album : <strong>('.$albumTag.')</strong><br />';
//          echo 'Genre : <strong>('.$genreTag.')</strong><br />';
//          echo 'Year : <strong>('.$yearTag.')</strong><br />';
//          echo 'File : <em>('.$file->getPathname().'</em><br />';
//          echo 'MIME : <strong>'.$mime[0].' ('.file_mime_type($file).')</strong><br />';
//          echo $file->getSize().' Bytes / Modified '.date('Y.m.d-H:i:s', $file->getMTime()).'</div>';
//          asort($musicFile);
         foreach($musicFile as $key => $value) {
            if ($key == 'album' && isset($value)) {
               $sameAlbums[] = $value;
            }
            if ($key == 'title' && isset($value)) {
               $sameTitles[] = $value;
            }
            echo $key.' : <strong>('.$value.')</strong><br />';
         }
         echo '</div>';
      }
      if ($mime[0] == 'XML') {
         $xmlFiles[] = $file;
         echo '<div class="xmlFile">';
         echo 'File : <em>('.$file->getPathname().'</em><br />';
         $size += $file->getSize();
         echo 'MIME : <strong>'.$mime[0].' ('.file_mime_type($file).')</strong><br />';
         echo $file->getSize().' Bytes / Modified '.date('Y.m.d-H:i:s', $file->getMTime()).'</div>';
      }
      if ($mime[0] == 'JPEG' || $mime[0] == 'GIF' || $mime[0] == 'PNG') {
         $imgFiles[] = $file;
         echo '<div class="imgFile">';
         echo 'File : <em>('.$file->getPathname().'</em><br />';
         $size += $file->getSize();
         echo 'MIME : <strong>'.$mime[0].' ('.file_mime_type($file).')</strong><br />';
         echo $file->getSize().' Bytes / Modified '.date('Y.m.d-H:i:s', $file->getMTime()).'</div>';
      }
   }
}

function file_mime_type($file){
//    exec("find \"$file\" -print0 | xargs -0 ls",$unixName);
   exec("/usr/bin/file -b \"$file\"",$out);
   $mime_type = array_shift(explode(';',$out[0]));
   return $mime_type;
}
// exec("whereis vorbiscomment",$outvorbis);
// exec("uname -a",$outHostSys);
// $hostSys = explode(';',$outHostSys[0]);
// $kaynVorbis = explode(';',$outvorbis[0]);
// if ($kaynVorbis[0] === 'vorbiscomment:') {
//   $vorbis = 'Command "vorbiscomment" not found on host system ('.$hostSys[0].') ! I can play ogg files but not edit their tags.';
//   } else {
//      $vorbis = 'Command "vorbiscomment" available on host system ('.$hostSys[0].'): I can play ogg files and edit their tags';
//   }
// $vorbis = array_shift(explode(';',$outvorbis[0]));
echo '<div class="mainInterface">';
// echo '<h4>'.$vorbis.'</h4>';
// echo '<hr />';
$allAlbums = array_unique($sameAlbums);
rsort($allAlbums);

$allTitles = array_unique($sameTitles);
rsort($allTitles);

echo '<pre>';
foreach($allAlbums as $key => $value)
{
   echo 'album '.$key . " = " . $value . "<br />";
}
echo '<hr />';
foreach($allTitles as $key => $value)
{
   echo 'title '.$key . " = " . $value . "<br />";
}

// array_multisort($allAlbums, SORT_DESC, $allTitles, SORT_DESC);

$result_array = array_intersect_assoc($allTitles, $allAlbum);

echo '</pre>';
echo '<hr />';
echo '<p>Total valid music files: <strong>'.count($musicFiles).'</strong> songs in <strong>'.count($allAlbums).'</strong> albums</p>';
echo '<p>Total XML files: <strong>'.count($xmlFiles).'</strong> files</p>';
echo '<p>Total Image files: <strong>'.count($imgFiles).'</strong> files</p>';
echo '<p>Total Weird files: <strong>'.count($weirdFiles).'</strong> files</p>';
echo '<p>Total file size: <strong>'.$size.'</strong> bytes</p>';
echo '<p>Total files: <strong>'.count($files).'</strong> files</p>';
// echo '<pre>';
// var_dump($allAlbums);
// echo '</pre>';
print_r($result_array);
echo '</div>';
?>

</body>
</html>