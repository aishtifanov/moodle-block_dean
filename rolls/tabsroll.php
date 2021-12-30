<?php  // $Id: tabsroll.php,v 1.1.1.1 2009/08/21 08:38:46 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($tabroll)) {
        error('You cannot call this script in that way');
    }


	    $toprow = array();
  	    $toprow[] = new tabobject('zaschetexam', $CFG->wwwroot."/blocks/dean/rolls/rollofdiscipline.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=zaschetexam",
                get_string('zaschetexam', 'block_dean'));
   		$toprow[] = new tabobject('retake', $CFG->wwwroot."/blocks/dean/rolls/roll2.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid;&amp;did=$did;&amp;rid=$rid;&amp;tabroll=retake",
       	        get_string('retake', 'block_dean'));
   		$toprow[] = new tabobject('listofrolls', $CFG->wwwroot."/blocks/dean/rolls/rolls.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=$cid&amp;did=0",
       	        get_string('listofrolls', 'block_dean'));
        $tabs = array($toprow);
        print_tabs($tabs, $tabroll, NULL, NULL);


?>
