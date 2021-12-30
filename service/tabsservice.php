<?php // $Id: tabsservice.php,v 1.0 2011/07/07 12:00:56 zagorodnyuk Exp $
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    $toprow[] = new tabobject(1, "index.php?cs=2&amp;id=$fid&amp;cst=1&amp;ct=$currenttab&amp;uid=$uid&amp;gid=$gid", get_string('text1', 'block_cdoservice'));
    $toprow[] = new tabobject(2, "index.php?cs=2&amp;id=$fid&amp;cst=2&amp;ct=$currenttab&amp;uid=$uid&amp;gid=$gid", get_string('text2', 'block_cdoservice'));

    $zaochnaya=mysql_query("SELECT name,idedform  FROM mdl_bsu_ref_groups WHERE id='".$currenttab."'");
    $zaochnaya=mysql_fetch_assoc($zaochnaya);  
    $grup = $zaochnaya['name'];
    
    $sql_data=mysql_query("SELECT username FROM mdl_user WHERE id='".$deanuser."'");
    $sql_data=mysql_fetch_assoc($sql_data);          
	$sss = 3;
    
   /* $sql_data=mysql_query("SELECT paralel FROM bsu_students_2013 WHERE codephysperson='".$sql_data['username']."' AND numprikazotsh=''");
    if(mysql_num_rows($sql_data)>0) {
    $sql_data=mysql_fetch_assoc($sql_data);     
    switch($sql_data['paralel']) {
			case 1: case 2:
				$sss = '03';
			break;
		}
	}
*/
	if($zaochnaya['idedform'] == 3 || $zaochnaya['idedform'] == 4 || $zaochnaya['idedform'] == 2) {
		$toprow[] = new tabobject(3, "index.php?cs=2&amp;id=$fid&amp;cst=3&amp;uid=$uid&amp;gid=$gid", get_string("text$sss", 'block_cdoservice'));
	}
	if($admin_is || $student_is) {
		if(!($methodist_is>0))
		    $toprow[] = new tabobject(4, "index.php?cs=2&amp;id=$fid&amp;cst=4&amp;gid=$gid&amp;uid=$uid", get_string('service_complaint', 'block_cdoservice'));
	}

	$tabs = array($toprow);
	print_tabs($tabs, $currentservicetab, $inactive, $activetwo);
?>
