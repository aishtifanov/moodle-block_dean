
<?php // $Id: dean.php

    require_once("../../config.php");
 //   require_once('../../course/lib.php');
 
    $fr = fopen ("$CFG->dirroot/1.utf", "r");
	
	while (!feof($fr))	{
		    $onestr = fgets($fr);
			$winstr = utf8_decode($onestr);
			echo $winstr.'<br>';
	}
	fclose($fr);		

?>


