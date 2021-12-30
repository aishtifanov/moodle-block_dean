<?php // $Id: tabs.php,v 1.0 2011/12/13 11:28:00 zagorodnyuk Exp $
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

	$toprow[] = new tabobject(1, "index.php?ct=1", get_string('quiztab1', 'block_dean'));
	$toprow[] = new tabobject(2, "index.php?ct=2", get_string('quiztab2', 'block_dean'));

    if($headdepartment_is || $admin_is || $dean_is) {
		$toprow[] = new tabobject(3, "index.php?ct=3", get_string('quiztab3', 'block_dean'));
	}
	if(($admin_is || $deanuser == 1835) && $stats == 0) {
		$toprow[] = new tabobject(4, "index.php?ct=4", get_string('quiztab4', 'block_dean'));
	}

	$tabs = array($toprow);
	print_tabs($tabs, $currenttab, $inactive, $activetwo);
?>