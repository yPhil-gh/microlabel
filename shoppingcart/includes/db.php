<?php
	@mysql_connect("localhost","microlabel","plop") or die("Demo is not available, please try again later");
	@mysql_select_db("microlabel") or die("Demo is not available, please try again later");
	session_start();
?>