<?php  // $Id: tabsgroups.php,v 1.2 2009/09/07 07:10:38 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    $toprow[] = new tabobject('listgroups', $CFG->wwwroot."/blocks/dean/groups/academygroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid",
                get_string('listgroups', 'block_dean'));

	if 	($admin_is || $creator_is )	 {
/*
	    $toprow[] = new tabobject('importgroup', $CFG->wwwroot."/blocks/dean/groups/importgroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid",
    	            get_string('importgroup', 'block_dean'));
*/
	    $toprow[] = new tabobject('importmanygroup', $CFG->wwwroot."/blocks/dean/groups/importmanygroups.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid",
    	            get_string('importmanygroup', 'block_dean'));


	    $toprow[] = new tabobject('frommoodletoacademy', $CFG->wwwroot."/blocks/dean/groups/formacademygroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid",
	                get_string('frommoodletoacademy', 'block_dean'));

	    $toprow[] = new tabobject('graduategroup', $CFG->wwwroot."/blocks/dean/groups/graduategroup.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid",
	                get_string('graduategroup', 'block_dean'));

	}
/*
    $toprow[] = new tabobject('transfer', $CFG->wwwroot.'/blocks/dean/groups/transfer.php?id=',
                get_string('transfer', 'block_dean'));
*/
   $tabs = array($toprow);


/// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
