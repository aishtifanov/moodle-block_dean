<?php // $Id: tabs.php,v 1.0 2011/7/7 13:00:56 zagorodnyuk Exp $
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

	if((!$dean_is || $methodist_is ) && !$grant_user_is) {
	    $toprow[] = new tabobject(1, "index.php?cs=1&amp;id=$fid", get_string('print_service', 'block_cdoservice'));
    	$toprow[] = new tabobject(2, "index.php?cs=2&amp;id=$fid", get_string('service_order', 'block_cdoservice'));
    }
	if($admin_is || $methodist_is || $dean_is) {
	    $toprow[] = new tabobject(3, "index.php?cs=3&amp;id=$fid", get_string('service_order_pack', 'block_cdoservice'));
	    $toprow[] = new tabobject(4, "index.php?cs=4&amp;id=$fid", get_string('service_journal', 'block_cdoservice'));
	}
 
	$tabs = array($toprow);
	print_tabs($tabs, $servicetab, $inactive, $activetwo);
?>
