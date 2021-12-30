<?php // $Id: tabs.php,v 1.0 2011/12/13 11:28:00 zagorodnyuk Exp $
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    $toprow[] = new tabobject(1, "clearpegas.php?ct=1", get_string('page1', 'block_dean'));
    $toprow[] = new tabobject(2, "clearpegas.php?ct=2", get_string('page2', 'block_dean'));

	$tabs = array($toprow);
	print_tabs($tabs, $currenttab, $inactive, $activetwo);
?>