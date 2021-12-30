<?php  // $Id: tabsemestr.php,v 1.1.1.1 2009/08/21 08:38:45 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set

/*
    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }
*/
    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();


//	$strterm = get_string('term', 'block_dean');

	for ($i=1; $i<=12; $i++)   {
	    $toprow[] = new tabobject($strterm.$i , $CFG->wwwroot."/blocks/dean/curriculum/disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;term=$i",
								  // $strterm.'&nbsp;'.$i );
								  $i );
	}

	if 	($admin_is || $creator_is )	 {
	    $toprow[] = new tabobject( $strterm.'0', $CFG->wwwroot."/blocks/dean/curriculum/disciplines.php?fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;term=0",
 								    get_string('all'));


	    $toprow[] = new tabobject( 'importrup', $CFG->wwwroot."/blocks/dean/curriculum/importrup.php?fid=$fid&amp;sid=$sid&amp;cid=$cid",
 								    get_string('importrup', 'block_dean'));
	}


   $tabs = array($toprow);

/// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
