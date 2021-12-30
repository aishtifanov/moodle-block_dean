<?php  // $Id: tabstudent.php,v 1.3 2009/10/09 08:23:27 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab) or empty($user)) {
        error('You cannot call this script in that way');
    }

    // print_heading(fullname($user));

    $inactive = NULL;
    $activetwo = NULL;
    $toprow = array();

    $toprow[] = new tabobject('profile', $CFG->wwwroot."/blocks/dean/student/student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
                get_string('profile'));


    if(empty($CFG->loginhttps)) {
            $wwwroot = $CFG->wwwroot;
    } else {
            $wwwroot = str_replace('http','https',$CFG->wwwroot);
    }

    if ($admin_is || $creator_is) {
    	
	    $toprow[] = new tabobject('registrationcard', $wwwroot."/blocks/dean/student/registrationcard.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
    	            // get_string('registrationcard', 'block_dean'));
					get_string('editmyprofile'));
		
		$toprow[] = new tabobject('studycard', $CFG->wwwroot."/blocks/dean/student/studycard.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
    	            get_string('studycard', 'block_dean'));

		$toprow[] = new tabobject('zachbook', $CFG->wwwroot."/blocks/dean/student/zachbooks.php?mode=5&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;uid={$user->id}",
    	            get_string('zachbook', 'block_dean'));
	/*
	    $toprow[] = new tabobject('recordbook', $CFG->wwwroot."/blocks/dean/student/recordbook.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
    	            get_string('recordbook', 'block_dean'));

	    $toprow[] = new tabobject('infaboutcompletofcur', $CFG->wwwroot."/blocks/dean/student/infaboutcompletofcur.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$user->id}",
    	            get_string('infaboutcompletofcur', 'block_dean'));
	*/
	}

   $tabs = array($toprow);


/// Print out the tabs and continue!
    print_tabs($tabs, $currenttab, $inactive, $activetwo);

?>
