<?php // $Id: student.php,v 1.2 2011/02/10 11:56:29 shtifanov Exp $

    require_once("../../../config.php");

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);          // Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Group id
    $uid = optional_param('uid', 0, PARAM_INT);       // User id
   	$action   = optional_param('action', '');
    	
    include('incl_stud.php');

	if ($mode == 5) 	{
	    if (!$user = get_record("user", "id", $uid) ) {
	        error("No such user in this course");
	    }

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
    	print_user_picture($user->id, '', $user->picture, true, false, false);
	    echo "</td><td width=\"100%\" class=\"content\">";

    	// Print the description

    	if ($user->description) {
        	echo format_text($user->description, FORMAT_MOODLE)."<hr />";
	    }

    	// Print all the little details in a list

	    echo '<table border="0" cellpadding="0" cellspacing="0" class="list">';

	    // Non-cached - get accessinfo
    	if ($user->id === $USER->id && isset($USER->access)) {
	        $accessinfo = $USER->access;
	    } else {
	        $accessinfo = get_user_access_sitewide($user->id);
	    }

		$fields = array('fullname', 'visible', 'category');
		// id, category, sortorder, password, fullname, shortname, idnumber, summary, format, showgrades, modinfo, newsitems, teacher, teachers, student, students, guest, startdate, enrolperiod, numsections, marker, maxbytes, showreports, visible, hiddensections, groupmode, groupmodeforce, defaultgroupingid, lang, theme, cost, currency, timecreated, timemodified, metacourse, requested, restrictmodules, expirynotify, expirythreshold, notifystudents, enrollable, enrolstartdate, enrolenddate, enrol, defaultrole
    	$mycourses = get_user_courses_bycap($user->id, 'moodle/course:view', $accessinfo,
                                      NULL, 'c.shortname ASC', $fields, 0);
    

	    if ($action == 'ne387af') {
   			if	($mycourses)	{
       			foreach ($mycourses as $mycourse) {
       				unenrol_student_dean ($user->id, $mycourse->id);
       			}
			}
			redirect("student.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid=$uid", 0);	   	
		}

	    // if ($mycourses = get_my_courses($user->id)) {
    	if	($mycourses)	{
    	   $courselisting = '';
	       print_row(get_string('courses').':', '('.count($mycourses).')');
	       foreach ($mycourses as $mycourse) {
		       if ($mycourse->visible and $mycourse->category) {
    		       $courselisting = "<a href=\"$CFG->wwwroot/user/view.php?id=$user->id&amp;course=$mycourse->id\">$mycourse->fullname</a>";
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
    
	if ($admin_is)	{ 
		$options = array('mode' => $mode, 'fid' => $fid, 'sid' => $sid, 'cid' => $cid, 'gid' => $gid,  'uid' => $uid, 'action' => 'ne387af');
		echo '<table align="center" border=0><tr><td>';
		print_single_button("student.php", $options, get_string("unenrollall", 'block_dean'));
		echo '</td></tr></table>';
	}	

    print_footer();
   
?>


