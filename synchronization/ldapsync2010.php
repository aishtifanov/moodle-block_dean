<?php // $Id: ldapsync.php,v 1.5 2009/11/30 12:32:03 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $fid = optional_param('fid', 0, PARAM_INT);          // Faculty id
    $sid = optional_param('sid', 0, PARAM_INT);          // Speciality id
   	$go = optional_param('go', 0, PARAM_INT);			//     
  	
	
	$strsynchronization = get_string('ldapsynchspec','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
    $currenttab = 'ldapsync2010';
    include('ldaptabs.php');
    

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");

	print_heading($strsynchronization, 'center', 4);
	notify (get_string('description_ldapsync2010','block_dean'), 'black');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_faculty("ldapsync2010.php?sid=$sid&amp;fid=", $fid);
    listbox_speciality("ldapsync2010.php?fid=$fid&amp;sid=", $fid, $sid);
	echo '</table>';

	if ($fid != 0 && $sid != 0)  {
		
		$ugroups1 = get_records_sql ("SELECT id, name FROM {$CFG->prefix}dean_academygroups
									 WHERE facultyid=$fid AND specialityid=$sid
									 ORDER BY name");
		if ($ugroups1) {
			$totalcountout = $totalcountmove = 0;  
			foreach ($ugroups1 as $ugroup) {
				$len = strlen ($ugroup->name);
				$numgroup = substr($ugroup->name, -2);
				if ($len == 6 && $numgroup < 50)	{ 
					print_heading($ugroup->name, 'center', 2);
					$table = table_syncronization_with_ldap($ugroup->name, $go);
					print_table($table);
				}	
			}
			
			notify("Количество отсутствующих студентов: ". $totalcountout, 'green');	
			notify("Количество переводимых студентов: ". $totalcountmove, 'green');

		} else {
			print_heading('Group not found!', 'center', 2);
		}	

	    print_dean_box_start('center', '80%');

		echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapsync2010.php">'.
			 '<input type="hidden" name="fid" value="' . $fid. '" />'.
			 '<input type="hidden" name="sid" value="' . $sid. '" />'.		 
	 		 '<input type="hidden" name="go" value="1">' .				 
		     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
			 '</form></div>';
			 
			 
	   	print_dean_box_end();
	}   	
   	
    print_footer();


function table_syncronization_with_ldap($namegroup, $makemove = 0)
{
	global $CFG, $totalcountout, $totalcountmove;
	
	$table->head  = array (get_string("username"), get_string('deanstuds',"block_dean"), 
							get_string("ldapstuds","block_dean"), get_string("group"), get_string('action', "block_dean"));
	$table->align = array ("left", "left", "left", "center", "center");
	$table->size = array ('10%', '30%', '30%', '10%', '10%');
	$table->width = '95%';
	
    if (isset($namegroup) && !empty($namegroup)) 	{

	    $agroup = get_record_select('dean_academygroups', "name = '$namegroup'", 'id, name');
		// $mgroups = get_records("groups", "name", $namegroup);
			    							
		if ($agroup)	{
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, u.auth, m.academygroupid
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE m.academygroupid={$agroup->id}  AND deleted = 0
	                       ORDER BY u.lastname";
			
			if ($astudents = get_records_sql($studentsql))	{

				$deanstudslogin = array();
				$deanstudsauth =  array();
				$deanstudsid = array();
				$deanstuds = array();
				foreach($astudents as $astudent)	{
					$deanstudslogin[$astudent->username] = $astudent->username;
					$deanstudsauth[$astudent->username] = $astudent->auth;
					$deanstudsid[$astudent->username] = $astudent->id;	
					$deanstuds[$astudent->username] = trim($astudent->lastname) . ' ' . trim($astudent->firstname);
					$deanstudentsobjects[$astudent->username] = $astudent;
				}

				
				if ($lstudents = get_records_select('dean_ldap', "group1= '$namegroup' or group2= '$namegroup'", 'lastname', 'id, login, firstname, lastname, secondname, group1, group2'))	{
					$ldapstudslogin =array();
					$ldapstuds = array();
					$ldapstudscheck = array();  
					foreach($lstudents as $lstudent)	{
						$ldapstudslogin[$lstudent->login] = $lstudent->login; 
						$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
						$ldapstudscheck[$lstudent->login] = false; 
					}	

					
					foreach ($deanstudslogin as $username => $login) {
						$straction = '';
						// $strlinkgroup1 = $strlinkgroup2 = '-';
					    $find = array_search($login, $ldapstudslogin);
						$strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true);
						// $strlinkgroup1 = "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";

												 
						$deanstud = $deanstuds[$login];
						if 	($find === false)	 {
							if ($newgropustudent = get_record_select('dean_ldap', "login = '$login'", 'id, login, firstname, lastname, secondname, group1, group2'))	{
								$strnewstudent = trim($newgropustudent->lastname) . ' ' . trim($newgropustudent->firstname). ' ' . trim($newgropustudent->secondname);
								$strnewgroup = " ($newgropustudent->group1, $newgropustudent->group2) ";
								if ($makemove)	{

									$flag1 = $flag2 = true;
									if (!empty($newgropustudent->group1))	{
										if (!$agroup = get_record_select('dean_academygroups', "name = '$newgropustudent->group1'", 'id, name'))	{
											$flag1 = false;
											// notify (get_string ('groupnotexists', 'block_dean', $newgropustudent->group1));
											$straction .= '<b>' . get_string ('groupnotexists', 'block_dean', $newgropustudent->group1) . '</b>';
										}	
									}	
									if (!empty($newgropustudent->group2))	{
										if (!$agroup = get_record_select('dean_academygroups', "name = '$newgropustudent->group2'", 'id, name'))	{
											$flag2 = false;
											// notify (get_string ('groupnotexists', 'block_dean', $newgropustudent->group2));
											$straction .= '<b>' . get_string ('groupnotexists', 'block_dean', $newgropustudent->group2) . '</b>';
										}	
									}
									
									if ($flag1 && $flag2)	{ 
										unenroll_student_from_all($deanstudsid[$login]);
										if (!empty($newgropustudent->group1))	{
											enroll_student_to_group($deanstudsid[$login], $newgropustudent->group1);
											$straction .= '<i>' . 'Переведен(а) в группу ' . $newgropustudent->group1 . '</i>';
										}	
										if (!empty($newgropustudent->group2))	{
											enroll_student_to_group($deanstudsid[$login], $newgropustudent->group2);
											$straction .= '<i>' . 'Переведен(а) в группу ' . $newgropustudent->group2 . '</i>' ;
										}	
										
									}			
								}
								
								$table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "<u>$strnewstudent</u>", $strnewgroup, $straction);
								$totalcountmove++;									
							} else {
								$table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "-", '-', $straction);
								$totalcountout++;
							}	
						} else {
							// $ldapstud = $ldapstuds[$login];
							// $table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "<b>$ldapstud</b>", '', '');
						}	
					}
				}	
			}
		}
	}
	
	return $table;
}	



function unenroll_student_from_all($userid)
{
	global $CFG;


	if ($dms = get_records_select('dean_academygroups_members', "userid = $userid", '', 'id, userid, academygroupid'))	{
		
		foreach ($dms as $dm)	{
			$academygroup = get_record_sql("SELECT id, name FROM {$CFG->prefix}dean_academygroups
							  			    WHERE id={$dm->academygroupid}");
	
			if ($mgroups = get_records_select("groups", "name = $academygroup->name", '', 'id, courseid, name'))  {
				 foreach($mgroups as $mgroup)  {
					unenrol_student_dean ($userid, $mgroup->courseid);
	            	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $userid);
				 }
			}
			delete_records('dean_academygroups_members', 'userid', $userid, 'academygroupid', $academygroup->id);
		}
	}
}


function enroll_student_to_group($userid, $namegroup)
{
	global $CFG;
	
	if (!$agroup = get_record_select('dean_academygroups', "name = '$namegroup'", 'id, name'))	{
		notify (get_string ('groupnotexists', 'block_dean', $newgropustudent->group2));
			return false;
	}	
	
    if (!$amember1 = get_record_select('dean_academygroups_members', "userid = $userid AND academygroupid = $agroup->id", 'id')) {
		$student->academygroupid = $agroup->id;
		$student->userid = $userid;
		$student->timeadded = time();
		if (!insert_record('dean_academygroups_members', $student))	{
			error(get_string('errorinaddinggroupmember','block_dean'), '');			 
		} 
		
	    // Get groups (moodle) in course with equal name
		if ($mgroups = get_records_select("groups", "name = $agroup->name", '' , 'id, courseid, name'))  	{
			 foreach($mgroups as $mgroup)  {
			 	
			    if ($context = get_context_instance(CONTEXT_COURSE, $mgroup->courseid)) {
	    			if (!$res = role_assign_dean(5, $userid, 0, $context->id, 0, 0, 0, 'manual'))	{
	    				notify('role> '. get_string('errornotaddstudenttocourse', 'block_dean', $userid) . $mgroup->courseid);	
	    			} else {
	    				// echo 'Course: ' . $mgroup->courseid . '<br>';
	    			}  
					  
	    		} else {
	    				notify('context> '. get_string('errornotaddstudenttocourse', 'block_dean', $userid) . $mgroup->courseid);
	    		}

        		$record->groupid = $mgroup->id;
  	            $record->userid = $userid;
    	        $record->timeadded = time();
	    	    if (!insert_record('groups_members', $record)) 	{
					notify('!!> '. get_string('erroraddingusertogroup', 'block_dean', $userid) . $mgroup->courseid);
        	    }
            } //foreach
		} //if
	}
	return  true;	
}

?>