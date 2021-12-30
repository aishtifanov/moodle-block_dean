<?php // $Id: student_g.php,v 1.2 2011/02/10 11:56:29 shtifanov Exp $

    require_once("../../../config.php");

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = optional_param('uid', 0, PARAM_INT);       // User id
    optional_variable($q);
    include('incl_stud_g.php');

   $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string("students","block_dean");

	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode == 5) 	{

	    if ($academygroups_g = get_record ('dean_academygroups_g', 'id', $gid))  {
        	print_heading($strgroup.' '.$academygroups_g->name,'center');
        }

     	    if (!$user = get_record("dean_user_graduates", "userid", $uid) ) {
	        error("No such user in this course");
	    }  else{
	    	print_heading($user->lastname.' '.$user->firstname,'center');
	    }
                /*
         $studentsql = "SELECT u.id, u.userid, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              u.lastaccess, m.academygroupid
                            FROM {$CFG->prefix}dean_user_graduates u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members_g m ON m.userid = u.userid
                       WHERE academygroupid={$academygroups_g->id}";
       // $academygroup = get_record('dean_academygroups_g', 'id', $gid);

       //$whereclause .= 'academygroupid = '.$academygroups_g->id.' AND ';

	   // $studentsql .= 'WHERE '.$whereclause.' u.deleted = 0 AND u.confirmed = 1';

        $students = get_records_sql($studentsql);
              */
        $fullname = fullname($user);
	    $personalprofile = get_string("personalprofile");
	    $participants = get_string("participants");

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
       			 //  $courselisting = "<a href=\"$CFG->wwwroot/mod/quiz/report.php?mode=5&amp;id=$mycourse->id&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$user->userid}\">$mycourse->fullname</a>";
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


