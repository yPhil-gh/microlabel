<?php

require_once('libs/microlabel.php');

if (isset($_GET['errorcode'])) {
    $errorString = $_GET['errorcode'];
}

$suggestion = '';
$path = "./";

if ($errorString == '401' || $errorString == '500') {
    $path = "../";
    $suggestion = "Something must be wrong with your .ht* files. Better check 'em now.";
} else if ($errorString == '404') {
    $suggestion = "Dude, I looked everywhere for that file, and could <strong>not</strong> find it.";
} else {

}


echo '
<html>
<head>
<meta charset="UTF-8">
    <link type="text/css" href="'.$path.'css/style.css" rel="stylesheet" media="screen" />
    <style type="text/css" media="screen">@import "css/style.css";</style>
    <script src="libs/jquery-1.5.1.min.js"></script>
    <script>
        $(document).ready(function () {
            $(".microlabel-index").hide();
        });
    </script>
</head>
<body class="microlabel-body">
';

if (isset($_GET['errorcode'])) {
    $errorString = $_GET['errorcode'];
}


$errorString = TXT_SERVER_ERROR.' : <strong>'.$errorString.'</strong>';

microlabelError($errorString, $suggestion);

?>

</body>
</html>
