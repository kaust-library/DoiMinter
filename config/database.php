<?php
	//$doiMinter = new mysqli("localhost", MYSQL_USER, MYSQL_PW, "doiMinter-test");
	$doiMinter = new mysqli("localhost", MYSQL_USER, MYSQL_PW, "doiMinter");
	
	ini_set('mbstring.internal_encoding','UTF-8');
	ini_set('mbstring.func_overload',7);
	ini_set('default_charset', 'UTF-8' );
	
	$doiMinter->set_charset("utf8");