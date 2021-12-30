<?php  // $Id: tabs.php,v 1.8 2011/03/22 14:02:54 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    $toprow[] = new tabobject('prep2teacher', $CFG->wwwroot."/blocks/dean/reports/prep2teacher.php",
                get_string('prep2teacher', 'block_dean'));

    $toprow[] = new tabobject('disc2course', $CFG->wwwroot."/blocks/dean/reports/disc2course.php",
                get_string('disc2course', 'block_dean'));

    $toprow[] = new tabobject('examtoexec', $CFG->wwwroot."/blocks/dean/reports/examtoexec.php",
                get_string('examtoexec', 'block_dean'));

//	if 	($admin_is || $creator_is )	 {
	    $toprow[] = new tabobject('examcompare', $CFG->wwwroot."/blocks/dean/reports/examcompare.php",
	                get_string('examcompare', 'block_dean'));

	    $toprow[] = new tabobject('examreport', $CFG->wwwroot."/blocks/dean/reports/examreport.php",
	                get_string('examreport', 'block_dean'));

	    $toprow[] = new tabobject('examrptattempts', $CFG->wwwroot."/blocks/dean/reports/examrptattempts.php",
	                get_string('examrptattempts', 'block_dean'));
                    
	    $toprow[] = new tabobject('examdbq', $CFG->wwwroot."/blocks/dean/reports/examdbq.php",
	                get_string('examdbq', 'block_dean'));

	    $toprow[] = new tabobject('examtotal', $CFG->wwwroot."/blocks/dean/reports/examtotal.php",
	                get_string('examtotal', 'block_dean'));
        /*            
        $toprow[] = new tabobject('examschedule', $CFG->wwwroot."/blocks/dean/reports/examschedule.php",
	                get_string('examschedule', 'block_dean'));
        */            
                    
    $tabs = array($toprow);


/// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
