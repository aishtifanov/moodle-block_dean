<?php  // $Id: tabsynchr.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    $inactive = NULL;
    $activetwo = NULL;
    $toprow1 = array();
    $toprow2 = array();

  
    if ($admin_is || $creator_is) {
	    $toprow1[] = new tabobject('synchr_fid', $CFG->wwwroot."/blocks/dean/synchronization/synchr_fid.php", 
    	            get_string('synchr_fid', 'block_dean'));
	    $toprow1[] = new tabobject('synchr_sid', $CFG->wwwroot."/blocks/dean/synchronization/synchr_sid.php", 
    	            get_string('synchr_sid', 'block_dean'));
	    $toprow1[] = new tabobject('synchr_cid', $CFG->wwwroot."/blocks/dean/synchronization/synchr_cid.php", 
    	            get_string('synchr_cid', 'block_dean'));
	    $toprow2[] = new tabobject('synchr_did', $CFG->wwwroot."/blocks/dean/synchronization/synchr_did.php", 
    	            get_string('synchr_did', 'block_dean'));
	    $toprow2[] = new tabobject('synchr_gid', $CFG->wwwroot."/blocks/dean/synchronization/synchr_gid.php", 
    	            get_string('synchr_gid', 'block_dean'));
	    $toprow2[] = new tabobject('synchr_uid', $CFG->wwwroot."/blocks/dean/synchronization/synchr_uid.php", 
    	            get_string('synchr_uid', 'block_dean'));

	}	
	
   $tabs1 = array($toprow1);
   $tabs2 = array($toprow2); 
/// Print out the tabs and continue!
    print_tabs($tabs1, $currenttab, $inactive, $activetwo);
    print_tabs($tabs2, $currenttab, $inactive, $activetwo);

?>
