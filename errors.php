<?php
$err = $_REQUEST['err'];
if(!$err) {
  die("No error code specified.");
  } else if(strtolower($err) == "log") {
  $title = "Error Log";
//   include($_SERVER['DOCUMENT_ROOT'].'/header.php');
  echo elog();
//   die(include($_SERVER['DOCUMENT_ROOT'].'/footer.php'));
  }

// Set $title to error name before calling header.php
$names = array("400"=>"Bad Request", "401"=>"Unauthorized", "403"=>"Forbidden", "404"=>"Page Not Found", "500"=>"Server Error");
if(!$names[$err]) { // This means that the error code specified isn't in your array
  die("Error code not recognized.");
  }
$title = $err." error: ".$names[$err];
include($_SERVER['DOCUMENT_ROOT'].'/header.php');

// Format log for viewing
function elog() {
  if(!file_exists('./log.txt')) {
    $file = "There is no log file to view.<br /><br />\n\n";
    return $file;
    }
  $file = implode('', file('./log.txt'));
  $file = str_replace("\n", "<br />\n", $file);
  return $file;
  }

// Get client info
$ua = $_SERVER['HTTP_USER_AGENT'];
$ua_s = strpos($ua, "(")+1;
$ua = substr($ua, $ua_s);
$ua_e = strpos($ua, ".NET")-2;
$ua = substr($ua, 0, $ua_e);
list($com, $brows, $ver, $os) = explode("; ", $ua);
$ip = $_SERVER['REMOTE_ADDR'];
$referer = $_SERVER['HTTP_REFERER'];
$time = date('l, F j, Y \a\t g:i a');
if(strtolower($com) == "compatible") {
  $com = "True";
  } else {
  $com = "False";
  }
if(!$referer) {
  $referer = "None";
  }

// Print error description
$descs = array("400"=>"I'm sorry, but it appears your browser has sent an unrecognizable request to the server, please try again.", "401"=>"This page is either password protected, or you are unauthorized to view it.", "403"=>"You do not have permission to access this document.", "404"=>"Sorry, the page you were looking for can not be located.", "500"=>"An unexpected error has occured on the server, if this is the first time this has happened, <br />please go <a href=\"javascript:history.back(1)\">back</a>, then try to access this page again.");
echo "<b>".$names[$err]."</b><br /><br />\n\n";
echo $descs[$err]."<br /><br />\n\n";
echo "<i>error occurred on ".$time."</i><br />\n";
echo "<i>Web Server <a href=\"http://".$_SERVER['SERVER_NAME']."\">".$_SERVER['SERVER_NAME']."</a></i><br /><br />";

// Prepare client info for addition into log.txt
$info = "-=BEGIN ERROR=-\n";
$info .= "Error ".$err.": ".$names[$err]."\n";
$info .= "Occured on: ".$time."\n";
$info .= "Client IP: ".$ip."\n";
$info .= "Client Compatible: ".$com."\n";
$info .= "Client Browser: ".$brows."\n";
$info .= "Client Version: ".$ver."\n";
$info .= "Client Operating System: ".$os."\n";
$info .= "Refering URL: ".$referer."\n";
$info .= "-=END ERROR=-\n\n";

// Add information to log.txt
$handle = fopen("./log.txt", 'a');
fwrite($handle, $info);
fclose($handle);

include($_SERVER['DOCUMENT_ROOT'].'/footer.php');
?>