<?php

require_once('libs/microlabel.php');

$path =  MICROLABEL_MUSIC_DIR;

$URI = (isset($_GET['d'])) ? explode(",", $_GET['d']) : die("arg");


foreach ($URI as $step) {
    $path = $path.'/'.$step;
}

$mm_type="application/octet-stream";

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Type: " . $mm_type);
header("Content-Length: " .(string)(filesize($path)) );
header('Content-Disposition: attachment; filename="'.basename($path).'"');
header("Content-Transfer-Encoding: binary\n");

readfile($path);

exit();
?>
