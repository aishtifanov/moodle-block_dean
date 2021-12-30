<?PHP // $Id: searchstudent.php,v 1.7 2011/12/19 12:35:44 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('../lib_search.php');    

    $namestudent = optional_param('namestudent', '');		// student lastname
    $loginstudent = optional_param('loginstudent', '');		// student login
   	$action = optional_param('action', '');
    $uid_g = optional_param('uid', 0, PARAM_INT);

	$strstudent  = get_string('student');
	$strstudents = get_string('students');
    $strsearchstudent = get_string('searchstudent', 'block_dean');
    $strsearch = get_string("search");
    $strsearchresults  = get_string("searchresults");
    $searchtext1 = '';
    $searchtext2 = '';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsearchstudent";
    print_header($SITE->shortname . ': ' .$strsearchstudent, $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


    if ($action == 'restore' && $uid_g > 0)   {
        if ($userid = restore_user_account($uid_g)) {
            restore_dean_student($userid);
        }    
    }

    if (isset($action) && !empty($action)) 	{

	    if (isset($namestudent) && !empty($namestudent)) 	{
		     $searchtext1 = $namestudent;
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.auth,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                              u.lastaccess, m.academygroupid
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE u.lastname LIKE '$namestudent%'  AND deleted = 0
	                       ORDER BY u.lastname";
            
	    } else if (isset($loginstudent) && !empty($loginstudent)) 	{
	       
			 $searchtext2 = $loginstudent;

	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.auth,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                              u.lastaccess, m.academygroupid 
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE u.username LIKE '$loginstudent%'  AND deleted = 0
	                       ORDER BY u.username";
	    }
        
        // echo $studentsql;

        if($students = get_records_sql($studentsql)) {
            $table = table_students($students); 
      		// print_table($table);           
		}  else {
			notify(get_string('studentnotfound','block_dean'));
			echo '<hr>';
		}

	    if (isset($namestudent) && !empty($namestudent)) 	{
            $studentsql = "SELECT u.id, u.userid, u.username, u.firstname, u.lastname, u.email, u.auth,
    							  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
                                  u.lastaccess, m.academygroupid
                           FROM {$CFG->prefix}dean_user_graduates u
                           LEFT JOIN {$CFG->prefix}dean_academygroups_members_g m ON m.userid = u.userid
                           WHERE u.lastname LIKE '$namestudent%'
                           ORDER BY u.lastname";
	    } else if (isset($loginstudent) && !empty($loginstudent)) 	{
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.auth,
								  u.city, u.country, u.lastlogin, u.picture, u.lang, u.timezone,
	                              u.lastaccess, m.academygroupid 
	                       FROM {$CFG->prefix}dean_user_graduates u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members_g m ON m.userid = u.id
	                       WHERE u.username LIKE '$loginstudent%' 
	                       ORDER BY u.username";
	    }                           
        
        if ($students = get_records_sql($studentsql))   {
            table_students_graduate($students, $table);
        }    

		print_table($table);          
	}

	print_heading($strsearchstudent, 'center', 2);

	print_heading(get_string('searchstudentlastname', 'block_dean'), 'center', 3);
    print_dean_box_start("center", '50%');
	echo '<div align=center><form name="studentform1" id="studentform1" method="post" action="searchstudent.php?action=lastname">'.
		 get_string('lastname'). '&nbsp&nbsp'.
		 '<input type="text" name="namestudent" size="10" value="' . $searchtext1. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form></div>';
    print_dean_box_end();

	print_heading(get_string('searchstudentlogin', 'block_dean'), 'center', 3);
    print_dean_box_start("center", '50%');
	echo '<div align=center><form name="studentform2" id="studentform2" method="post" action="searchstudent.php?action=login">'.
		 get_string('username'). '&nbsp&nbsp'.
		 '<input type="text" name="loginstudent" size="10" value="' . $searchtext2. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsearch . '" />'.
		 '</form></div>';
    print_dean_box_end();

    echo '<p></p>';
    
    print_footer();



function table_students($students)
{
    global $CFG, $USER, $admin_is;

	$table->head  = array ('', get_string('fullname'), get_string('username'), get_string('group'), get_string('email'), get_string('lastaccess'),get_string('edit','block_dean'));
	$table->align = array ("center", "left", "left", "left", "left", "center", "center");

    $strnever = get_string('never');


    foreach ($students as $student) {

		//$course = get_records('user_students', 'course', $student->id);

        unset($agroup);
		$strgroups = '';
		$title = get_string('students','block_dean');
		if (count_records('dean_academygroups_members', 'userid', $student->id) > 1)	{
			$allmembers = get_records('dean_academygroups_members', 'userid', $student->id);
			foreach ($allmembers as $allmem)	{
				if ($agroup_ = get_record('dean_academygroups', 'id', $allmem->academygroupid))	{
					$agroup = $agroup_; 
					$strgroups .= "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
					$strgroups .= '!<br>';
				}	
			} 
		} else {
   			if ($agroup = get_record('dean_academygroups', 'id', $student->academygroupid))	{
				$strgroups .= "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
			}						   					
		} 

        // print_r($agroup);
        // $discipline = get_record('dean_discipline','courseid',$course->course,'term',$term,'curriculumid',$cid);
        if ($student->lastaccess) {
            $lastaccess = format_time(time() - $student->lastaccess);
        } else {
            $lastaccess = $strnever;
        }
        $strlinkupdate = '';
        if (isset($agroup) && !empty($agroup))     {
               // "<a title=\"$title\" href=\"retakeone.php?action=excel&amp;fid=$fid&amp;did=$did&amp;tabroll=retake&amp;retake=$retake&amp;rid=$rid&amp;sid=$sid&amp;cid=$cid&amp;gid=$gid&amp;uid={$student->studentid}\">";
            $tittle = get_string('showtests','block_dean');
            $strlinkupdate = "<a title=\"$tittle\" href=\"$CFG->wwwroot/blocks/dean/student/student_tst.php?fid={$agroup->facultyid}&amp;term={$agroup->term}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}&amp;uid={$student->id}\">";
            $strlinkupdate .= "<img src=\"{$CFG->pixpath}/f/edit.gif\"alt=\"$tittle\" /></a>&nbsp;";
            /*               	
			$title = get_string('allinfoabout', 'block_dean');
            $strlinkupdate .= "<a title=\"$tittle\" href=\"$CFG->wwwroot/blocks/dean/student/allinfotst.php?fid={$agroup->facultyid}&amp;term={$agroup->term}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/stats.gif\" alt=\"$title\" /></a>&nbsp;";
			*/
            $strstudent = "<div align=left><strong><a href=\"{$CFG->wwwroot}/blocks/dean/student/student.php?mode=5&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}&amp;uid={$student->id}\">".fullname($student)."</a></strong></div>";
        } else {
            $strstudent = fullname($student);
        }
        
	    if ($admin_is)	{
           	// if ($student->auth == 'ldap' && $strgroups == '')	{
           	if ($strgroups == '')	{    
				$title = get_string('deleteprofilepupil', 'block_dean');
                if (isset($agroup) && !empty($agroup))     {
                   $strlinkupdate .= "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/dean/student/delstud.php?fid={$agroup->facultyid}&amp;term={$agroup->term}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}&amp;uid={$student->id}&amp;sesskey=$USER->sesskey\">";
                } else {
                   $strlinkupdate .= "<a title=\"$title\" href=\"$CFG->wwwroot/blocks/dean/student/delstud.php?uid={$student->id}&amp;sesskey=$USER->sesskey\">"; 
                }   
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			}
        }
            
		$title = get_string('students','block_dean');
        $table->data[] = array (print_user_picture($student->id, 1, $student->picture, false, true),
						    $strstudent,
                            "<strong>$student->username</strong> ($student->auth)",
                            $strgroups,
                            $student->email,
                            "<center><small>$lastaccess</small></center>",
                            $strlinkupdate);
    }
    
    return $table;
}    



function table_students_graduate($students, &$table)
{
    global $CFG, $admin_is, $namestudent, $loginstudent;

    $strnever = get_string('never');

    $table->data[] = array ('<hr>', '<hr>', '<hr>', '<hr>', '<hr>', '<hr>', '<hr>');
    
    foreach ($students as $student) {

		$strgroups = '';
		$title = get_string('students','block_dean');
		if (count_records('dean_academygroups_members_g', 'userid', $student->id) > 1)	{
			$allmembers = get_records('dean_academygroups_members_g', 'userid', $student->id);
			foreach ($allmembers as $allmem)	{
				if ($agroup_ = get_record('dean_academygroups_g', 'id', $allmem->academygroupid))	{
					$agroup = $agroup_; 
					$strgroups .= "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember_g.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
					$strgroups .= '!<br>';
				}	
			} 
		} else {
   			if ($agroup = get_record('dean_academygroups_g', 'id', $student->academygroupid))	{
				$strgroups .= "<strong><a title=\"$title\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember_g.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
			}						   					
		} 

        // print_r($agroup);
        // $discipline = get_record('dean_discipline','courseid',$course->course,'term',$term,'curriculumid',$cid);
        if ($student->lastaccess) {
            $lastaccess = format_time(time() - $student->lastaccess);
        } else {
            $lastaccess = $strnever;
        }
        
        $titl = 'Восстановить';
		$strlinkupdate = "<a title=\"$titl\" href=\"searchstudent.php?action=restore&uid=$student->id&namestudent=$namestudent&loginstudent=$loginstudent\">";
		$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/dean/i/btn_move.png\" alt=\"$titl\" /></a>&nbsp;";

        $strstudent = fullname($student);
        
		$title = get_string('students','block_dean');
        $table->data[] = array ('выбывший',
						    $strstudent,
                            "<strong>$student->username</strong> ($student->auth)",
                            $strgroups,
                            $student->email,
                            "<center><small>$lastaccess</small></center>",
                            $strlinkupdate);
    }
}    

?>