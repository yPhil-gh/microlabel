<?php

require_once('libs/microlabel.php');

if (isset($_GET['errorcode'])) {
    $errorString = $_GET['errorcode'];
}

$suggestion = '';

if ($errorString == '401' || $errorString == '500') {
    $path = "../";
    $suggestion = TXT_ERROR_401_SUGGESTION;
} else if ($errorString == '404') {
    $suggestion = TXT_ERROR_404_SUGGESTION;
} else {
    $suggestion = '';
}


echo '
<html>
<head>
<meta charset="UTF-8">
    <link type="text/css" href="'.$path.'libs/css/style.css" rel="stylesheet" media="screen" />
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
