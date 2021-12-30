<?php // $Id: student_tst_inf.php,v 1.2 2011/02/10 11:56:29 shtifanov Exp $

    require_once("../../../config.php");
    //require_once("locallib.php");

    optional_variable($id);   // course
    optional_variable($q);

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "quiz", "view all", "index_g.php?id=$course->id", "");

// Print the header

    $strquizzes = get_string("modulenameplural", "quiz");
    $streditquestions = isteacheredit($course->id)
                        ? "<form target=\"_parent\" method=\"get\" "
                           ." action=\"$CFG->wwwroot/mod/quiz/edit.php\">"
                           ."<input type=\"hidden\" name=\"courseid\" "
                           ." value=\"$course->id\" />"
                           ."<input type=\"submit\" "
                           ." value=\"".get_string("editquestions", "quiz")."\" /></form>"

                        : "";
    $strquiz  = get_string("modulename", "quiz");

    print_header_simple("$strquizzes", "", "$strquizzes",
                 "", "", true, $streditquestions, navmenu($course));


// Get all the appropriate data

    if (! $quizzes = get_all_instances_in_course("quiz", $course)) {
        notice("There are no quizzes", "../../course/view.php?id=$course->id");
        die;
    }

// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
    $strbestgrade  = get_string("bestgrade", "quiz");
    $strquizcloses = get_string("quizcloses", "quiz");
    $strattempts = get_string("attempts", "quiz");
    $strusers  = $course->students;

    if (isteacher($course->id)) {
        $gradecol = $strattempts;
    } else {
        $gradecol = $strbestgrade;
    }

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname, $strquizcloses, $gradecol);
        $table->align = array ("center", "left", "left", "left");
        $table->size = array (10, "", "", "");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname, $strquizcloses, $gradecol);
        $table->align = array ("center", "left", "left", "left");
        $table->size = array (10, "", "", "");
    } else {
        $table->head  = array ($strname, $strquizcloses, $gradecol);
        $table->align = array ("left", "left", "left");
        $table->size = array ("", "", "");
    }
//"<a href=\"$CFG->wwwroot/blocks/dean/student/student_tst_inf.php?mode=5&amp;id=$mycourse->id&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$user->userid}\">$mycourse->fullname</a>";
    $currentsection = "";

    foreach ($quizzes as $quiz) {
        if (!$quiz->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a href=\"$CFG->wwwroot\mod\quiz\view.php?id=$quiz->coursemodule\">".format_string($quiz->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"$CFG->wwwroot\mod\quiz\view.php?id=$quiz->coursemodule\">".format_string($quiz->name,true)."</a>";
        }

       // $bestgrade = quiz_get_best_grade($quiz, $USER->id);

        $printsection = "";
        if ($quiz->section !== $currentsection) {
            if ($quiz->section) {
                $printsection = $quiz->section;
            }
            if ($currentsection !== "") {
                $table->data[] = 'hr';
            }
            $currentsection = $quiz->section;
        }

        $closequiz = userdate($quiz->timeclose);

        if (isteacher($course->id)) {
            if ($usercount = count_records_select('quiz_attempts', "quiz = '$quiz->id' AND preview = '0'", 'COUNT(DISTINCT userid)')) {
                $attemptcount = count_records('quiz_attempts', 'quiz', $quiz->id, 'preview', 0);
                $strviewallreports  = get_string('viewallreports', 'quiz', $attemptcount);
                $gradecol = "<a href=\"$CFG->wwwroot/mod/quiz/report.php?mode=overview&amp;q=$quiz->id\">$strviewallreports ($usercount $strusers)</a>";
            } else {
                $answercount = 0;
                $gradecol = "";
            }
        } else {
            if ($bestgrade === NULL || $quiz->grade == 0) {
                $gradecol = "";
            } else {
                $gradecol = "$bestgrade / $quiz->grade";
            }
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($printsection, $link, $closequiz, $gradecol);
        } else {
            $table->data[] = array ($link, $closequiz, $gradecol);
        }
    }

    echo "<br />";




// Finish the page

   // print_footer($course);
    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = optional_param('uid', 0, PARAM_INT);       // User id

    include('incl_stud_g.php');

   $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string("students","block_dean");


	//tabele attempts
	$strteststart  = get_string('strteststart','block_dean');
    $strtime  = get_string('zatrvremya','block_dean');
    $strmark  = get_string('mark','block_dean');
    $strcolgrade  = get_string('colgrade','block_dean');

	// Start creating reports
	//$quizzes = get_records_sql("SELECT * FROM {$CFG->prefix}quiz");

	$tablee->head  = array ($strteststart, $strtime, $strcolgrade, $strmark);
    $tablee->align = array ("center", "center", "center", "center");
    $tablee->size = array ("", "", "", "");
    $userr = get_record("dean_user_graduates", "userid", $uid);

	$quizes = get_records('quiz','course',$course->id);

    foreach ($quizes as $quizz){
    	 if ($qattempts = get_records_sql("SELECT * FROM {$CFG->prefix}quiz_attempts
    									WHERE quiz={$quizz->id} and userid={$userr->userid}"))
    	 {
    		foreach ($qattempts as $attempts){
		      $tablee->data[] = array (get_rus_format_date($attempts->timestart), format_time(($attempts->timefinish)-($attempts->timestart)), $attempts->attempt,round($attempts->sumgrades));
	        }
    	 }else {
    	 	continue;
    	 	}

    }


	//$categories = get_records


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 5) 	{

	    if ($academygroups_g = get_record ('dean_academygroups_g', 'id', $gid))  {
        	print_heading($strgroup.' '.$academygroups_g->name,'center');
        }

     	    if (!$user = get_record("dean_user_graduates", "userid", $uid) ) {
	        error("No such user in this course");
	    }  else{
	    	print_heading($user->lastname.' '.$user->firstname,'center');
	    }

        $fullname = fullname($user);
	    $personalprofile = get_string("personalprofile");
	    $participants = get_string("participants");

     print_table($table);
     print_table($tablee);
	    if ($user->deleted) {
	        print_heading(get_string("userdeleted"));
	    }

	    $currenttab = 'profile';
	    include('tabstudent.php');


    	echo "<table width=\"80%\" align=\"center\" border=\"0\" cellspacing=\"0\" class=\"userinfobox\">";
	    echo "<tr>";
	    echo "<td width=\"100\" valign=\"top\" class=\"side\">";
    	print_user_picture($user->userid, $course->id, $user->picture, true, false, false);
	    echo "</td><td width=\"100%\" class=\"content\">";

    	// Print the description

    	if ($user->description) {
        	echo format_text($user->description, FORMAT_MOODLE)."<hr />";
	    }

    	// Print all the little details in a list

	    echo '<table border="0" cellpadding="0" cellspacing="0" class="list">';
       //  print_r($gid);

     	if (!$academygroup = get_record('dean_academygroups_g', 'id', $gid)) {
        error("Academy group not found!");
	    }
         //print_r($uid);
            /*
     	if ($academystudents = get_records('dean_academygroups_members_g', 'academygroupid', $academygroup)){
     		$studcount=count($academystudents);



		 if ( $mycourses = get_records('dean_user_students_g', 'userid', $academystudents->userid)){
			//$courselisting = '';
            $s = count($mycourses);
            print($s);
			print_row(get_string('courses').':', '('.count($mycourses).')');

			foreach ($mycourses as $mycourse) {
		       //if ($mycourse->visible and $mycourse->category) {
    		       $courselisting = "<a href=\"$CFG->wwwroot/user/view_g.php?id=$academystudents->userid&amp;course=$mycourse->id\">$mycourse->course</a>";
       			 print_row(get_string('courses').':', '('.count($mycourses).')');
       			//}
		       print_row('-', $courselisting);
		    }
		 }  else {
			notify('Студент не подписан ни на один из курсов');

		 }

		}   */

	    if ($mycourses = get_my_coursess($user->userid)) {
    	   $courselisting = '';
	       print_row(get_string('courses').':', '('.count($mycourses).')');
	       foreach ($mycourses as $mycourse) {
		       if ($mycourse->visible and $mycourse->category) {
    		     //$courselisting = "<a href=\"$CFG->wwwroot/user/view_g.php?id=$user->userid&amp;course=$mycourse->id\">$mycourse->fullname</a>";
       			   $courselisting = "<a href=\"$CFG->wwwroot/blocks/dean/student/student_tst_inf.php?mode=5&amp;id=$mycourse->id&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$user->userid}\">$mycourse->fullname</a>";
       			}
		       print_row('-', $courselisting);
		    }
		}

		$stradress = "";
	    if ($user->city or $user->country) {
	        $countries = get_list_of_countries();
			$stradress .= $countries["$user->country"].", $user->city";
	    }
    	if ($user->address) {
			$stradress .= ", $user->address";
	    }
        print_row(get_string("address").":", $stradress );

	    if ($user->phone1) {
            print_row(get_string("phone").":", "$user->phone1");
    	}
        if ($user->phone2) {
            print_row(get_string("phone").":", "$user->phone2");
	    }

    	print_row(get_string("email").":", obfuscate_mailto($user->email, '', $user->emailstop));

	    if ($user->url) {
    	    print_row(get_string("webpage").":", "<a href=\"$user->url\">$user->url</a>");
	    }

    	if ($user->icq) {
        	print_row(get_string('icqnumber').':',"<a href=\"http://web.icq.com/wwp?uin=$user->icq\">$user->icq <img src=\"http://web.icq.com/whitepages/online?icq=$user->icq&amp;img=5\" width=\"18\" height=\"18\" border=\"0\" alt=\"\" /></a>");
	    }

    	if ($user->skype) {
        	print_row(get_string('skypeid').':','<a href="callto:'.urlencode($user->skype).'">'.s($user->skype).
            	' <img src="http://mystatus.skype.com/smallicon/'.urlencode($user->skype).'" alt="status" '.
	           ' height="16" width="16" /></a>');
	    }
    	if ($user->yahoo) {
        	print_row(get_string('yahooid').':', '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.s($user->yahoo).'&amp;.src=pg">'.s($user->yahoo).'</a>');
	    }
    	if ($user->aim) {
        	print_row(get_string('aimid').':', '<a href="aim:goim?screenname='.s($user->aim).'">'.s($user->aim).'</a>');
	    }
	    if ($user->msn) {
    	    print_row(get_string('msnid').':', s($user->msn));
	    }
	}
    echo "</table>";
    echo "</td></tr></table>";

    print_footer();


?>

