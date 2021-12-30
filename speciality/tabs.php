<?php  // $Id: tabs.php,v 1.2 2012/06/19 07:44:03 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

/*
	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {
		$strurl = "&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid";
	} else {
		$strurl = '';
	}
*/
    $toprow2 = array();

    $toprow2[] = new tabobject('speciality', "speciality.php?id=$fid", $strspeciality);
    $toprow2[] = new tabobject('speciality2group', "speciality2group.php?id=$fid", $strspeciality . ' (сводная)');
    
    if ($admin_is) {    
        $toprow2[] = new tabobject('statenrol', "statenrol.php?id=$fid", 'Статистика подписки на дисциплины');
    }            
    
    $tabs2 = array($toprow2);


/// Print out the tabs and continue!
    print_tabs($tabs2, $currenttab, NULL, NULL);

?>
