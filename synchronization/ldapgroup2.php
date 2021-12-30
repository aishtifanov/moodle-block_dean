<?php // $Id: ldapgroup2.php,v 1.3 2012/10/19 06:24:42 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    $namegroup = optional_param('namegroup', '');		// Group name (number)
   	$go = optional_param('go', 0, PARAM_INT);			//     

	$strsynchronization = get_string('ldapsynchronization','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }


    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	// $admin_is = true; $go=1; $namegroup = '100459';
	 
    if (isset($namegroup) && !empty($namegroup)) 	{

	    $agroup = get_record('dean_academygroups', 'name', $namegroup);
		$mgroups = get_records("groups", "name", $namegroup);	    							
		if ($agroup)	{
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, m.academygroupid
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE m.academygroupid={$agroup->id}  AND deleted = 0
	                       ORDER BY u.lastname";
			
			if ($astudents = get_records_sql($studentsql))	{

				$deanstuds = array();
				$deanstudsid = array();
				foreach($astudents as $astudent)	{
					$deanstudsid[$astudent->username] = $astudent->id;	
					$deanstuds[$astudent->username] = trim($astudent->lastname) . ' ' . trim($astudent->firstname);
					$deanstudentsobjects[$astudent->username] = $astudent;
				}

				
				if ($lstudents = get_records('dean_ldap', 'group1', $namegroup, 'lastname'))	{
					$ldapstuds = array();
					$ldapstudscheck = array();  
					foreach($lstudents as $lstudent)	{
						$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
						$ldapstudscheck[$lstudent->login] = false; 
						$newusers[$lstudent->login]->lastname =  trim($lstudent->lastname);
						$newusers[$lstudent->login]->firstname =  trim($lstudent->firstname);
						$newusers[$lstudent->login]->secondname =  trim($lstudent->secondname);						
					}	

					$table->head  = array (get_string('deanstuds',"block_dean"), get_string("ldapstuds","block_dean"), 
					get_string("logins", "block_dean"), get_string("group"));
					$table->align = array ("left", "left", "center", "center");
					$table->size = array ('30%', '20%', '15%', '10%');
					$table->width = '90%';
						
					$strlinkupdate = '-';
					foreach ($deanstuds as $username => $deanstud) {
						$login = array_search($deanstud, $ldapstuds);
						if 	($login === false)	 {
							$strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true) . "<b>$deanstud</b>";
							$strlinkupdate = "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";							 
							$table->data[] = array ($strlink, '<b>? ? ?</B>', 
											$username . ' => ?', $strlinkupdate);
							/*				
							$removestudent = $deanstudsid[$username];

							if ($go == 1)	{
								if ($mgroups)  {
									 foreach($mgroups as $mgroup)  {
									 	if ($context = get_context_instance(CONTEXT_COURSE, $mgroup->courseid)) {
									 		role_unassign_dean(5, $removestudent, $context->id, $mgroup->courseid);
				    						echo 'Unenrol course: ' . $mgroup->courseid . ' ' . $deanstud .'<br>';
									 	}	
									 }
								}
								delete_records('dean_academygroups_members', 'userid', $removestudent);			
								echo '----------------------------------------<br>';					
							}	
							*/
							
						}  else {
							$ldapstudscheck[$login] = true;
							$strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true).$deanstud;
							$strlinkupdate = "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
							$table->data[] = array ($strlink, $ldapstuds[$login], 
											$username . ' => ' . $login, $strlinkupdate);
											
							if ($login != $username && $go == 1)	{
								if (!$olduser = get_record('user', 'username', $login))	{
									$newuser->id = $deanstudsid[$username];
									$newuser->username = $login;
                                    $newuser->auth = 'cas';
									if (!update_record('user', $newuser))	{
										notify ("Нельзя изменить логин $login, так как он уже существует в БД!!!");
									} 
								} else {
									$firstname_o = trim($olduser->firstname);
									list ($fname, $sname) = explode (' ', $firstname_o);
									list ($dlname, $dfname, $dsname) = explode (' ', $deanstud);
									if ($olduser->lastname == $dlname && $fname == $dfname && empty($sname))	{
										delete_records('user', 'username', $login);
										$newuser->id = $deanstudsid[$username];
										$newuser->username = $login;
                                        $newuser->auth = 'cas';
										if (!update_record('user', $newuser))	{
											print_r($newuser);
											notify ("Нельзя изменить логин $login, так как он уже существует в БД!!!");
										} 
									}    
								} 
							}																			
						}
					}
					
					$strlinkupdate = '-';
					foreach($ldapstudscheck as $login => $check1)  {
						if (!$check1)	{
							
							$strdeanstud = get_already_exist_users($newusers[$login], $login, $agroup);

							$table->data[] = array ($strdeanstud, $ldapstuds[$login], 
											'? => ' . $login, $strlinkupdate);
							if ($go == 1)	{											
								enrol_dean($newusers[$login], $login, $agroup);
							}	
							// break;				
						}
					}
					print_table($table);					
				}
			}
		}
		else {
			notify(get_string('groupnotfound','block_dean'));
			echo '<hr>';
		}
	}

	print_heading($strsynchronization, 'center', 2);

    print_dean_box_start('center', '80%');


	echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
		 get_string('numgroup', 'block_dean'). '&nbsp&nbsp'.
		 '<input type="text" name="namegroup" size="10" value="' . $namegroup. '" />'.
	     '<input name="search" id="search" type="submit" value="' . $strsyncpreview   . '" />'.
		 '</form></div>';
	if (isset($namegroup) && !empty($namegroup)) 	{	 
			echo '<div align=center>
			<form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
				 '<input type="hidden" name="go" value="1">' .				 
				 '<input type="hidden" name="namegroup" value="' . $namegroup . '" />'.
			     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
				 '</form></div>';
	}			 
		 
   	print_dean_box_end();
   	
    print_footer();


function enrol_dean($newuser, $login, $agroup)
{
	$user->mnethostid = 1;
	$user->lastname = $newuser->lastname;
	$user->firstname = $newuser->firstname. ' ' . $newuser->secondname;
	$user->auth = 'ldap';
	$user->username = $login;  
	$user->password = 'not cached';
	$user->email = $user->username . '@bsu.edu.ru';
	$user->city = get_string('town', 'block_dean');
	$user->country = 'RU';
	$user->lang = 'ru_utf8';
    $user->confirmed = 1;
    $user->timemodified = time();

	// print_r($user);    echo '<hr />';
	$flagexistence = false;   // check already registred user
    if ($user1 = get_record('user', 'username', $user->username)) {
        notify(get_string('usernotaddedregistered', 'error', "$user->lastname ($user->username)"));
		$user = $user1;
		$flagexistence = true;
    }
	else if ($user1 = get_record('user', 'lastname', $user->lastname, 'firstname', $user->firstname))  {
	    if ($amember = get_record('dean_academygroups_members', 'userid', $user1->id)) {
	    	if ($amember->academygroupid == $agroup->id)	{
                notify(get_string('usernotaddedregistered', 'error', "$user->lastname $user->firstname"));
				$user = $user1;
				$flagexistence = true;
	    	}
	    }
	}

    if ($flagexistence)	{
    	return false;
    }


	if (!$flagexistence) {
		if(!($user->id = insert_record("user", $user)))  {
            notify(get_string('usernotaddederror', 'error', $user->username));
            continue;
		}
		else {
			 notify("user: $user->id &raquo; $user->username &raquo; $user->lastname $user->firstname", 'green', 'left');
		}
	}

    if (!$amember1 = get_record('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $agroup->id)) {
		$student->academygroupid = $agroup->id;
		$student->userid = $user->id;
		$student->timeadded = time();
		if (insert_record('dean_academygroups_members', $student))	{
			 $timeadded = time();
		} else  {
			error(get_string('errorinaddinggroupmember','block_dean'), '');
		}

	    // Get groups (moodle) in course with equal name
		if ($mgroups = get_records("groups", "name", $agroup->name))  	{
			 foreach($mgroups as $mgroup)  {
			 	
			    if ($context = get_context_instance(CONTEXT_COURSE, $mgroup->courseid)) {
	    			if (!$res = role_assign_dean(5, $user->id, 0, $context->id, 0, 0, 0, 'manual'))	{
	    				notify('role> '. get_string('errornotaddstudenttocourse', 'block_dean', $user->username) . $mgroup->courseid);	
	    			} else {
	    				echo 'Course: ' . $mgroup->courseid . '<br>';
	    			}  
					  
	    		} else {
	    				notify('context> '. get_string('errornotaddstudenttocourse', 'block_dean', $user->username) . $mgroup->courseid);
	    		}

        		$record->groupid = $mgroup->id;
  	            $record->userid = $student->userid;
    	        $record->timeadded = time();
	    	    if (!insert_record('groups_members', $record)) 	{
					notify('!!> '. get_string('erroraddingusertogroup', 'block_dean', $user->username) . $mgroup->courseid);
        	    }
            } //foreach
		} //if

		unset ($student);
		$student->userid = $user->id;
		$student->facultyid = $agroup->facultyid;
		$student->specialityid = $agroup->specialityid;
		$student->timemodified = time();
		if (insert_record('dean_student_studycard', $student))	{
			 $timeadded = time();
		} else  {
			error(get_string('errorinaddingstudcard','block_dean'), "");
		}

    }
	unset ($user);
	unset ($student);

}



function get_already_exist_users($newuser, $login, $agroup)
{
	global $CFG;
	
	$user->lastname = $newuser->lastname;
	$user->firstname = $newuser->firstname. ' ' . $newuser->secondname;
	
	$strlink = '<b>? ? ?</b>';
	if ($user1 = get_record('user', 'lastname', $user->lastname, 'firstname', $user->firstname))  {
	    if ($amember = get_record('dean_academygroups_members', 'userid', $user1->id)) {
	    	$strlink = print_user_picture($user1->id, 1, $user1->picture, false, true). '<b><i>'. $user->lastname . ' ' . $user->firstname . ' !!!</i></b>';
			if ($agroup = get_record('dean_academygroups', 'id', $amember->academygroupid))		{
				$strlink .= "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
			}	
	    }
	} 
	
	return $strlink;
}


?>