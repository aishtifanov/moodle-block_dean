<?php  // $Id: tabsonegroup.php,v 1.2 2011/09/07 07:05:32 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    if ($admin_is || $creator_is ) {

	    $inactive = NULL;
 	    $activetwo = NULL;
	    $toprow = array();

 	    $toprow[] = new tabobject('liststudents', $CFG->wwwroot."/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
                get_string('studentsofgroup', 'block_dean'));

	    $toprow[] = new tabobject('registergroupincourse', $CFG->wwwroot."/blocks/dean/gruppa/registrgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
 			   	     get_string('registergroupincourse', 'block_dean'));
/*
	    $toprow[] = new tabobject('journalgroup', $CFG->wwwroot."/blocks/dean/journal/journalgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
    	            get_string('journalgroup', 'block_dean'));
*/
		$toprow[] = new tabobject('roll', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0",
  	                get_string('rolls', 'block_dean'));

		$toprow[] = new tabobject('certificate', $CFG->wwwroot."/blocks/dean/gruppa/certificate.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
 		 	                get_string('certificates', 'block_dean'));

/*
	    $toprow[] = new tabobject('reportdiscipline', $CFG->wwwroot.'/blocks/dean/gruppa/report.php?id=',
                get_string('reportdiscipline', 'block_dean'));
*/
 		$tabs = array($toprow);

/// Print out the tabs and continue!
        if ($currenttab == 'changeliststudents' || $currenttab == 'transferstudents') {
        	$currenttab0 = 'liststudents';
        } else {
        	$currenttab0 = $currenttab;
        }
	    print_tabs($tabs, $currenttab0, $inactive, $activetwo);

        if ($currenttab == 'liststudents' || $currenttab == 'changeliststudents' || $currenttab == 'transferstudents'|| $currenttab == 'transferstudents' || $currenttab == 'transferintorup') {
 	        $toprow2 = array();
	 	    $toprow2[] = new tabobject('liststudents', $CFG->wwwroot."/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
                get_string('liststudents', 'block_dean'));

	    	$toprow2[] = new tabobject('changeliststudents', $CFG->wwwroot."/blocks/dean/gruppa/changelistgroup.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
        	        get_string('changeliststudents', 'block_dean'));

			$toprow2[] = new tabobject('transferstudents', $CFG->wwwroot."/blocks/dean/gruppa/transferstudents.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
 		 	                get_string('transferstudents', 'block_dean'));

 		    $toprow2[] = new tabobject('transferintorup', $CFG->wwwroot."/blocks/dean/gruppa/transferintorup.php?mode=2&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid",
 		                    get_string('transferintorup', 'block_dean'));
	 		$tabs2 = array($toprow2);
  		    print_tabs($tabs2, $currenttab, $inactive, $activetwo);

 		}

 }
	?>
