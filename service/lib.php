<?php
//	require_once("../../../config.php");
	if (strpos($CFG->dbhost,'localhost')===false&&strpos($CFG->dbhost,'127.0.0.1')===false)
	{
		$link = mysql_connect('bsu-dekanat.bsu.edu.ru','dean','big#psKT');
		$db_selected = mysql_select_db('dean', $link);
		mysql_query('SET NAMES utf8');
	}
?>