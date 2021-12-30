<?php // $Id: lstgroupmember_g.php,v 1.3 2012/10/19 06:24:43 shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/tablelib.php');
    require_once('../lib.php');

    $mode = required_param('mode', PARAM_INT);        // Mode: 0, 1, 2, 3, 4, 9, 99 Can(or can't) show groups
    $fid = required_param('fid', PARAM_INT);          // Faculty id
    $sid = required_param('sid', PARAM_INT);          // Speciality id
    $cid = required_param('cid', PARAM_INT);		// Curriculum id
    $gid = required_param('gid', PARAM_INT);          // Graduate Group id

	$action   = optional_param('action', 'grades');
    if ($action == 'excel') {
        lstgroupmember_download('xls',$gid);
        exit();
	}

    if (!$site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

    $strfaculty = get_string('faculty','block_dean');
    $strspeciality = get_string("speciality","block_dean");
	$strcurriculums = get_string('curriculums','block_dean');
	$strgroup = get_string('group');
	$strgroups = get_string('groups');
	$strstudents = get_string("students","block_dean");

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/faculty/faculty.php\">$strfaculty</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/speciality/speciality.php?id=$fid\">$strspeciality</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/curriculum/curriculum.php?mode=2&amp;fid=$fid&amp;sid=$sid\">$strcurriculums</a>";
	$breadcrumbs .= " -> <a href=\"{$CFG->wwwroot}/blocks/dean/groups/graduategroup.php?mode=3&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid\">$strgroups</a>";
	$breadcrumbs .= " -> $strgroup";

    print_header("$site->shortname: $strgroup", "$site->fullname", $breadcrumbs);

	$admin_is = isadmin();
	$creator_is = iscreator();
	$teacher_is = isteacherinanycourse();

    if (!$admin_is && !$creator_is && !$teacher_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if ($fid == 0)  {
	   $faculty = get_record_sql("SELECT * FROM {$CFG->prefix}dean_faculty ORDER BY number", true);
	}
	elseif (!$faculty = get_record('dean_faculty', 'id', $fid)) {
        error(get_string('errorfaculty', 'block_dean'), '..\faculty\faculty.php');

    }

	if ($sid == 0)  {
	   $speciality = get_record_sql("SELECT * FROM {$CFG->prefix}dean_speciality", true);
	}
	elseif (!$speciality = get_record('dean_speciality', 'id', $sid)) {
        error(get_string('errorspeciality', 'block_dean'), '..\speciality\speciality.php?id=0');
    }

	// add_to_log($course->id, 'attendance', 'student view', 'index.php?course='.$course->id, $user->lastname.' '.$user->firstname);
    //add_to_log(SITEID, 'dean', 'speciality view', 'speciality.php?id='.SITEID, SITEID);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("lstgroupmember_g.php?mode=1&amp;gid=$gid&amp;sid=$sid&amp;cid=$cid&amp;fid=", $fid);
    listbox_speciality("lstgroupmember_g.php?mode=2&amp;gid=$gid&amp;fid=$fid&amp;cid=$cid&amp;sid=", $fid, $sid);
    listbox_curriculum("lstgroupmember_g.php?mode=3&amp;sid=$sid&amp;fid=$fid&amp;gid=$gid&amp;cid=", $fid, $sid, $cid);
//    listbox_group("lstgroupmember_g.php?mode=4&amp;cid=$cid&amp;sid=$sid&amp;fid=$fid&amp;gid=", $fid, $sid, $cid, $gid)	;
	echo '</table>';


	if ($fid != 0 && $sid != 0 && $cid != 0 && $gid != 0 && $mode >= 4)  {

//	    $currenttab = 'liststudents';
//	    include('tabsonegroup.php');

        if ($academygroups_g = get_record ('dean_academygroups_g', 'id', $gid))  {
        	print_heading($strgroup.' '.$academygroups_g->name,'center');
        }
		// print_heading($strgroup.' '.$groupmenu[$gid],'center');

	    $strnever = get_string('never');

    	$datestring->day   = get_string('day');
	    $datestring->days  = get_string('days');
    	$datestring->hour  = get_string('hour');
	    $datestring->hours = get_string('hours');
    	$datestring->min   = get_string('min');
	    $datestring->mins  = get_string('mins');
	    $datestring->sec   = get_string('sec');
    	$datestring->secs  = get_string('secs');

	    if ($admin_is || $creator_is ) {
	        $tablecolumns = array('picture', 'fullname', 'username', 'password', 'email',
								   'city',   'lastaccess');
	        $tableheaders = array('', get_string('fullname'), get_string('username'), get_string('password'),
	        						get_string('email'), get_string('city'), get_string('lastaccess'));
		}
		else 	{
	        $tablecolumns = array('picture', 'fullname', 'email',
								   'city',  'country', 'lastaccess');
	        $tableheaders = array('', get_string('fullname'), get_string('email'),
									  get_string('city'), get_string('country'), get_string('lastaccess'));
		}
	    // Should use this variable so that we don't break stuff every time a variable is added or changed.
	    $baseurl = $CFG->wwwroot."/blocks/dean/gruppa/lstgroupmember_g.php?mode=4&amp;sid=$sid&amp;fid=$fid&amp;cid=$cid&amp;gid=$gid";


        $table = new flexible_table("user-index-$gid");

	    $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
		// $table->column_style_all('align', 'left');

        $table->define_baseurl($baseurl);

        $table->sortable(true, 'lastname');
		// $table->sortable(true, 'lastaccess', SORT_DESC);

        $table->set_attribute('cellspacing', '0');
		// $table->set_attribute('align', 'left');
        $table->set_attribute('id', 'students');
        $table->set_attribute('class', 'generaltable generalbox');

        $table->setup();

	    if($whereclause = $table->get_sql_where()) {
            $whereclause .= ' AND ';
        }
        $studentsql = "SELECT u.id, u.userid, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone, u.auth, 
                              u.lastaccess, m.academygroupid
                            FROM {$CFG->prefix}dean_user_graduates u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members_g m ON m.userid = u.userid ";
       // $academygroup = get_record('dean_academygroups_g', 'id', $gid);
        $whereclause .= 'academygroupid = '.$academygroups_g->id.' AND ';

	    $studentsql .= 'WHERE '.$whereclause.' u.deleted = 0 AND u.confirmed = 1';


        if($sortclause = $table->get_sql_sort()) {
            $studentsql .= ' ORDER BY '.$sortclause;
        }

		 // print_r($studentsql);
        $students = get_records_sql($studentsql);


        if(!empty($students)) {

            if ($mode == 6) {
                foreach ($students as $key => $student) {
                    print_user($student, $course);
                }
            }
			else {
                foreach ($students as $student) {

                    if ($student->lastaccess) {
                        $lastaccess = format_time(time() - $student->lastaccess);
                    } else {
                        $lastaccess = $strnever;
                    }

	       	     if ($admin_is || $creator_is) {
	       	    	if ($student->auth == 'manual')	{
       	    			$psw1 = gen_psw($student->username);
	       	    	} else if ($student->auth == 'cas')	{
	       	    		$psw1 = 'CAS';
	       	    	} else if ($student->auth == 'ldap')	{
	       	    		$psw1 = 'LDAP';
	       	    	} else if ($student->auth == 'ldap3')	{
	       	    		$psw1 = 'LDAP3';
	       	    	}
                    $table->add_data(array (print_user_picture($student->userid, 1, $student->picture, false, true),
								    "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student_g.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$student->userid}\">".fullname($student)."</a></strong></div>",
                                    "<strong>$student->username</strong>/",
                                    "<strong>$psw1</strong>",
                                    $student->email,
                                    "<i>$student->city</i>",
									"<center><small>$lastaccess</small></center>"));
				 }

				  else if ($methodist_is) {
                    $table->add_data(array (print_user_picture($student->userid, 1, $student->picture, false, true),
								    "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student_g.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$student->userid}\">".fullname($student)."</a></strong></div>",
                                    "<strong>$student->username</strong>",
                                    "-",
                                    $student->email,
                                    "<i>$student->city</i>",
									"<center><small>$lastaccess</small></center>"));

				 }

				 else {
                    $table->add_data(array (print_user_picture($student->userid, 1, $student->picture, false, true),
								    "<align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student_g.php?mode=5&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid&amp;gid=$academygroups_g->id&amp;uid={$student->userid}\">".fullname($student)."</a></strong></align>",
                                    $student->email,
                                    "<i>$student->city</i>",
                                    '<i>'.get_string($student->country, 'countries').'</i>',
									"<center><small>$lastaccess</small></center>"));

				 }
                }

	    	echo '<div align=center>';
			$table->print_html();
        	echo '</div>';
			}
          /*
		    if ($admin_is || $creator_is ) {
				$options = array();
				$options['mode'] = 5;
			    $options['fid'] = $fid;
			    $options['sid'] = $sid;
			    $options['cid'] = $cid;
			    $options['gid'] = $gid;
			   	$options['sesskey'] = $USER->sesskey;
			    $options['action'] = 'excel';
				echo '<table align="center"><tr>';
			    echo '<td align="center">';
			    print_single_button("lstgroupmember.php", $options, get_string("downloadexcel"));
			    echo '</td></tr>';
			    echo '</table>';
			}
           */
		}

	}

    print_footer();

/*
function lstgroupmember_download($download,$gid)
{
    global $CFG;

    if ($download == "xls") {
        require_once("$CFG->libdir/excel/Worksheet.php");
        require_once("$CFG->libdir/excel/Workbook.php");

		$agroup = get_record('dean_academygroups', 'id', $gid);
		// HTTP headers
        header("Content-type: application/vnd.ms-excel");
        $downloadfilename = clean_filename("group_".$agroup->name);
        header("Content-Disposition: attachment; filename=\"$downloadfilename.xls\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

/// Creating a workbook
        $workbook = new Workbook("-");
        $myxls =& $workbook->add_worksheet($agroup->name);

/// Print names of all the fields
		$formath1 =& $workbook->add_format();
		$formath2 =& $workbook->add_format();
		$formatp =& $workbook->add_format();

		$formath1->set_size(12);
	    $formath1->set_align('center');
	    $formath1->set_align('vcenter');
		$formath1->set_color('black');
		$formath1->set_bold(1);
		$formath1->set_italic();
		// $formath1->set_border(2);

		$formath2->set_size(11);
	    $formath2->set_align('center');
	    $formath2->set_align('vcenter');
		$formath2->set_color('black');
		$formath2->set_bold(1);
		//$formath2->set_italic();
		$formath2->set_border(2);
		$formath2->set_text_wrap();

		$formatp->set_size(11);
	    $formatp->set_align('left');
	    $formatp->set_align('vcenter');
		$formatp->set_color('black');
		$formatp->set_bold(0);
		$formatp->set_border(1);
		$formatp->set_text_wrap();

		$myxls->set_column(0,0,4);
		$myxls->set_column(1,1,18);
		$myxls->set_column(2,2,40);
		$myxls->set_column(3,3,15);
		$myxls->set_row(0, 30);
        $myxls->write_string(0,0,get_string('group').' '.$agroup->name,$formath1);
		$myxls->merge_cells(0, 0, 0, 3);

        $myxls->write_string(1,0, 'N' ,$formath2);
        $myxls->write_string(1,1,get_string('username').' ('.get_string('password').')',$formath2);
        $myxls->write_string(1,2,get_string('fullname'),$formath2);
        $myxls->write_string(1,3,get_string('email'),$formath2);

		  // get_string('city'), get_string('country'), get_string('lastaccess'));

        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.maildisplay,
							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                              t.timeaccess as lastaccess, m.academygroupid
                            FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}user_students t ON t.userid = u.id
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id ";

        $whereclause = 'academygroupid = '.$gid.' AND ';
        $studentsql .= 'WHERE '.$whereclause.' u.deleted = 0 AND u.confirmed = 1';
        $studentsql .= ' ORDER BY u.lastname';

 	 // print_r($studentsql);
        $students = get_records_sql($studentsql);

        if(!empty($students)) {
             $i = 1;
             foreach ($students as $student) {
       	        $psw1 = gen_psw($student->username);
			    $i++;
    	       	$myxls->write_string($i,0,($i-1).'.',$formatp);
    	       	$myxls->write_string($i,1,"{$student->username} ($psw1)",$formatp);
        	    $myxls->write_string($i,2,fullname($student),$formatp);
           	    $myxls->write_string($i,3,$student->email,$formatp);
	 		 }
	  	     $i++;
  	   		 $myxls->write_string($i,2,get_string('vsego','block_dean'),$formath1);
       		 $myxls->write_formula($i, 3, "=COUNTA(D3:D$i)", $formath1);
		}

       $workbook->close();
       exit;
	}
}
*/

?>


