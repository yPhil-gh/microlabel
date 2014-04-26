<html>

<head>
<meta charset="UTF-8">
    <style type="text/css" media="screen">@import "css/style.css";</style>
    <script src="libs/jquery-1.5.1.min.js"></script>
    <script>
    $(document).ready(function () {
        $('.microlabel-index').hide();
    });
    </script>

    </head>

<body class="microlabel-body">
<?php


if (isset($_GET['errorcode'])) {
    $errorString = $_GET['errorcode'];
}

require_once('libs/microlabel.php');

$errorString = 'A much infortunate '.$errorString.' error has occured.';

microlabelError($errorString);

?>

</body>
</html>
