<?php  // $Id: tabsjournal.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttabjournal)) {
        error('You cannot call this script in that way');
    }

/*
	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0)  {
		$strurl = "&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid";
	} else {		$strurl = '';	}
*/
    $toprow2 = array();

    $toprow2[] = new tabobject('pagesjournal', $CFG->wwwroot."/blocks/dean/journal/journalgroup.php?gid=$gid".$strurl,
                get_string('pagesjournal', 'block_dean'));

   	$toprow2[] = new tabobject('journalschedule', $CFG->wwwroot."/blocks/dean/journal/journalschedule.php?gid=$gid".$strurl,
        	        get_string('journalschedule', 'block_dean'));

    $toprow2[] = new tabobject('journalsetting', $CFG->wwwroot."/blocks/dean/journal/journalsetting.php?gid=$gid".$strurl,
    	            get_string('journalsetting', 'block_dean'));

    $toprow2[] = new tabobject('journalstat', $CFG->wwwroot."/blocks/dean/journal/journalstats.php?gid=$gid".$strurl,
    	            get_string('journalstat', 'block_dean'));

    $toprow2[] = new tabobject('journalreports', $CFG->wwwroot."/blocks/dean/journal/journalreports.php?gid=$gid".$strurl,
    	            get_string('journalreports', 'block_dean'));

    $toprow2[] = new tabobject('journalinfo', $CFG->wwwroot."/blocks/dean/journal/journalinfo.php?gid=$gid".$strurl,
    	            get_string('info'));

   $tabs2 = array($toprow2);


/// Print out the tabs and continue!
    print_tabs($tabs2, $currenttabjournal, NULL, NULL);

?>
