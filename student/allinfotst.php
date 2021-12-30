<?php  // $Id: allinfotst.php,v 1.3 2011/02/07 13:10:22 shtifanov Exp $
    require_once("../../../config.php");
	require_once('../lib.php');    

   // $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = optional_param('uid', 0, PARAM_INT);       // User id
    $term = required_param('term', PARAM_INT);
    $qid = optional_param('qid', 0, PARAM_INT);       // quiz

    $namestudent = optional_param('namestudent', '');		// student lastname
    $loginstudent = optional_param('loginstudent', '');		// student login
   	$action = optional_param('action', '');

   
	$strstudent  = get_string('student');
	$strstudents = get_string('students');
    $strsearchstudent = get_string('searchstudent', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $searchtext1 = '';
    $searchtext2 = '';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchstudent";
    print_header("$SITE->shortname: $strsearchstudent", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    $strteststart  = get_string('strteststart','block_dean');
    $strtime  = get_string('zatrvremya','block_dean');
    $strmark  = get_string('ballov','block_dean');
    $strcolgrade  = get_string('colgrade','block_dean');
    $strdiscipline = get_string('course','block_dean');
    $strtestname = get_string('testname','block_dean');
    $strproc = get_string('procents','block_dean');

	$tablee->head  = array ($strdiscipline,$strtestname,$strteststart, $strtime, $strcolgrade, $strmark,$strproc);
    $tablee->align = array ("сутеук","left","center", "center", "center", "center", "center");
	$student = get_record('user','id',$uid);

    print_heading($student->lastname.' '.$student->firstname,'center');

        if($student) {

		if ($coursesids = get_records_sql("SELECT c.id, c.fullname FROM {$CFG->prefix}course c
                JOIN {$CFG->prefix}context ctx
                  ON (c.id=ctx.instanceid AND ctx.contextlevel=".CONTEXT_COURSE.")
                JOIN {$CFG->prefix}role_assignments ra
                  ON (ra.contextid=ctx.id AND ra.userid={$student->id})"))                  
			{
				foreach ($coursesids as $coursesid) {
        	        	if ($quizes = get_records('quiz','course',$coursesid->id)){
		  		  			$crs = get_record('course','id',$coursesid->id);
		
				  			foreach ($quizes as $quizz) {
				    			if ($qattempts = get_records_sql("SELECT * FROM {$CFG->prefix}quiz_attempts
				    									WHERE quiz={$quizz->id} and userid={$student->id}"))
				    			{//print_r($qattempts);
					    			foreach ($qattempts as $attempts){
										      $tablee->data[] = array ("<div align=left><strong><a href=\"{$CFG->wwwroot}/user/view.php?id={$student->id}&amp;course={$coursesid->id}\">".$crs->fullname."</a></strong></div>",$quizz->name,get_rus_format_date($attempts->timestart), format_time(($attempts->timefinish)-($attempts->timestart)), $attempts->attempt,round($attempts->sumgrades).'/'.$quizz->sumgrades, round((($attempts->sumgrades)*100)/($quizz->sumgrades)).'%');
						   			}
				    			}else {
				    	 			continue;
				   	 			}
				    		}      	        		
        	        	}


          		}
			}  else(notify('Студент не проходил тесты по данному курсу'));
                print_table($tablee);
        }else {
			notify(get_string('studentnotfound','block_dean'));
			echo '<hr>';
		}
			print_heading($strsearchstudent, 'center', 2);

	print_heading(get_string('searchstudentlastname', 'block_dean'), 'center', 3);
    print_dean_box_start("center");
	echo '<form name="studentform1" id="studentform1" method="post" action="searchstudent.php?action=lastname">'.
		 get_string('lastname'). '&nbsp&nbsp'.
		 '<input type="text" name="namestudent" size="10" value="' . $searchtext1. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form>';
    print_dean_box_end();

	print_heading(get_string('searchstudentlogin', 'block_dean'), 'center', 3);
    print_dean_box_start("center");
	echo '<form name="studentform2" id="studentform2" method="post" action="searchstudent.php?action=login">'.
		 get_string('username'). '&nbsp&nbsp'.
		 '<input type="text" name="loginstudent" size="10" value="' . $searchtext2. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form>';
    print_dean_box_end();
    print_footer();

?>