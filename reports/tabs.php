<?php  // $Id: tabs.php,v 1.11 2011/10/12 09:11:29 shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    // $toprow[] = new tabobject('prep2teacher', "prep2teacher.php", get_string('prep2teacher', 'block_dean'));
    // $toprow[] = new tabobject('disc2course', "disc2course.php", get_string('disc2course', 'block_dean'));
    // $toprow[] = new tabobject('examtoexec', "examtoexec.php", get_string('examtoexec', 'block_dean'));

//	if 	($admin_is || $creator_is )	 {
	    // $toprow[] = new tabobject('examcompare', "examcompare.php", get_string('examcompare', 'block_dean'));
	    // $toprow[] = new tabobject('examreportvv', "examreportvv.php", get_string('examreport', 'block_dean'));
	    // $toprow[] = new tabobject('examrptattempts', "examrptattempts.php", get_string('examrptattempts', 'block_dean'));
	    // $toprow[] = new tabobject('examdbq', "examdbq.php", get_string('examdbq', 'block_dean'));
	    // $toprow[] = new tabobject('examtotal', "examtotal.php", get_string('examtotal', 'block_dean'));
	   $toprow[] = new tabobject('egereport', "egereport.php", get_string('egereport', 'block_dean'));
        // $toprow[] = new tabobject('statkafedra', "statkafedra.php", get_string('statkafedra', 'block_dean'));        
        // $toprow[] = new tabobject('examschedule', "examschedule.php", get_string('examschedule', 'block_dean'));
           
                   
    $tabs = array($toprow);

/// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
