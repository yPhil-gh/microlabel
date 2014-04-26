<?php


if (isset($_GET['errorcode'])) {
    $errorString = $_GET['errorcode'];
}

$suggestion = '';

if ($errorString == '401' || $errorString == '500') {
    $path = "../";
    $suggestion = "Something must be wrong with your .ht* files. Better check 'em now.";
} else {
    $path = "./";
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

require_once('libs/microlabel.php');

$errorString = 'A much infortunate '.$errorString.' error has occured.';

microlabelError($errorString, $suggestion);

?>

</body>
</html>
