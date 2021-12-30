<?php // $Id: lib_ldap.php,v 1.9 2013/09/03 14:01:07 shtifanov Exp $



function dean_registry_ldap($newuser, $agroup)
{
	$user->mnethostid = 1;
	$user->lastname = $newuser->lastname;
	$user->firstname = $newuser->firstname. ' ' . $newuser->secondname;
	$user->auth = 'cas';
	$user->username = $newuser->login;  
	$user->password = 'not cached';
	$user->email = $newuser->login . '@bsu.edu.ru';
	$user->city = get_string('town', 'block_dean');
	$user->country = 'RU';
	$user->lang = 'ru_utf8';
    $user->confirmed = 1;
    $user->address = ''; 
    $user->timemodified = time();

    if ($user1 = get_record('user', 'username', $user->username, 'auth', 'cas')) {
		set_field('user', 'lastname', $user->lastname, 'id', $user1->id);
		set_field('user', 'firstname', $user->firstname, 'id', $user1->id);
        notify(get_string('alreadyregister', 'block_dean', "$user->lastname ($user->username)"));
		$user = $user1;				
    } else {
		if(!($user->id = insert_record("user", $user)))  {
            notify(get_string('usernotaddederror', 'error', $user->username));
		}
		else {
			 notify("new user: $user->id &raquo; $user->username &raquo; $user->lastname $user->firstname", 'green', 'left');
		}
	}

	enrol_in_academygroup($user, $agroup);
}



function enrol_in_academygroup($user, $agroup)	
{
    if (!$amember1 = get_record('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $agroup->id)) {
		$student->academygroupid = $agroup->id;
		$student->userid 		 = $user->id;
		$student->timeadded 	 = time();
		if (!insert_record('dean_academygroups_members', $student))	{
			error(get_string('errorinaddinggroupmember','block_dean'), '');
		}
		unset ($student);
		
		$student->userid 	= $user->id;
		$student->facultyid = $agroup->facultyid;
		$student->specialityid = $agroup->specialityid;
        $student->academygroupid = $agroup->id;
		$student->timemodified = time();
		if (!insert_record('dean_student_studycard', $student))	{
			error(get_string('errorinaddingstudcard','block_dean'), "");
		}
		
		notify(get_string('enrolinacademgroup', 'block_dean', $agroup->name), 'green', 'left');
    }
}    



function enrol_dean($newuser, $login, $agroup)
{
	$user->mnethostid = 1;
	$user->lastname = $newuser->lastname;
	$user->firstname = $newuser->firstname. ' ' . $newuser->secondname;
	$user->auth = 'cas';
	$user->username = $login;  
	$user->password = 'not cached';
	$user->email = $user->username . '@bsu.edu.ru';
	$user->city = get_string('town', 'block_dean');
	$user->country = 'RU';
	$user->lang = 'ru_utf8';
    $user->confirmed = 1;
    $user->timemodified = time();
    $user->address = '-'; 

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

    /*if ($flagexistence)	{
    	return false;
    }*/


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
        
        unenroll_from_agroup_this_faculty($user, $agroup);
        
        $student = new stdClass();
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
        $student = new stdClass();
		$student->userid = $user->id;
		$student->facultyid = $agroup->facultyid;
		$student->specialityid = $agroup->specialityid;
        $student->academygroupid = $agroup->id;
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
	
    $user = new stdClass();
	$user->lastname = $newuser->lastname;
	$user->firstname = $newuser->firstname. ' ' . $newuser->secondname;
	
	$strlink = '<b>? ? ?</b>';
	if ($user1 = get_record('user', 'lastname', $user->lastname, 'firstname', $user->firstname, '', '',  'id, picture, deleted, lastname, firstname'))  { // , 'deleted', 0,
	    if ($amember = get_record('dean_academygroups_members', 'userid', $user1->id)) {
	    	$strlink = print_user_picture($user1->id, 1, $user1->picture, false, true). '<b><i>'. $user->lastname . ' ' . $user->firstname . ' !!!</i></b>';
			if ($agroup = get_record('dean_academygroups', 'id', $amember->academygroupid))		{
				$strlink .= "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
                if ($user1->deleted)    {
                    $user1->deleted = 0;
                    $user1->username = $login;
                    $user1->email = $login.'@bsu.edu.ru';
                    update_record('user', $user1);
                }
            }     
	    }
	} 
	
	return $strlink;
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


function table_syncronization_with_ldap($namegroup, $makemove = 0)
{
	global $CFG, $totalcountout, $totalcountmove, $totalcountldap;
	
	$strunenroll = get_string('studentunenroll', 'block_dean');
	
	$table->head  = array (get_string("username"), get_string('deanstuds',"block_dean"), 
							get_string("ldapstuds","block_dean"), get_string("group"), get_string('action', "block_dean"));
	$table->align = array ("left", "left", "left", "center", "center");
	$table->size = array ('10%', '30%', '30%', '10%', '10%');
	$table->width = '95%';
	
    $i=0;
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
						$ldapstudentsobjects[$lstudent->login] = $lstudent; 
					}	

					
					foreach ($deanstudslogin as $username => $login) {
						$straction = '';
                        $strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true);
						// $strlinkgroup1 = $strlinkgroup2 = '-';
						// $strlinkgroup1 = "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";
												 
						$deanstud = $deanstuds[$login];
					    $find = array_search($login, $ldapstudslogin);                        
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
									
									if ($flag1 || $flag2)	{ 
										unenroll_student_from_all($deanstudsid[$login]);
									}	
									
									if ($flag1)	{	
										if (!empty($newgropustudent->group1))	{
											enroll_student_to_group($deanstudsid[$login], $newgropustudent->group1);
											$straction .= '<i>' . 'Переведен(а) в группу ' . $newgropustudent->group1 . '</i>';
										}	
									}
									
									if ($flag2)	{	
										if (!empty($newgropustudent->group2))	{
											enroll_student_to_group($deanstudsid[$login], $newgropustudent->group2);
											$straction .= '<i>' . 'Переведен(а) в группу ' . $newgropustudent->group2 . '</i>' ;
										}
									}			
								}
								
								$table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "<u>$strnewstudent</u>", $strnewgroup, $straction);
								$totalcountmove++;									
							} else {
								if ($makemove)	{
									unenroll_student_from_all($deanstudsid[$login]);
									$table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "-", '-', $strunenroll);
								}	else {
									$table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "-", '-', $straction);
								}
								$totalcountout++;
							}	
						} else {
						  	// $strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true);
							// $ldapstud = $ldapstuds[$login];
							// $table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "<b>$ldapstud</b>", '', '');
                            $deanstudfio = mb_strtoupper($deanstuds[$login], 'UTF-8');
                            $ldapstudfio = mb_strtoupper($ldapstuds[$login], 'UTF-8');        
                            if ($deanstudfio != $ldapstudfio) {
                                notify ("{$deanstuds[$login]} !=! {$ldapstuds[$login]}");
                                $lstudent = $ldapstudentsobjects[$login];
                                $rec->id = $deanstudsid[$login];
                                $rec->lastname = trim($lstudent->lastname);
                                $rec->firstname = trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
                                
                                if (!update_record('user', $rec))   {
                                    print_r($rec);
                                    notify ("Error in update user.");
                                }  else {
                                    $i++;
                                    notify ("$i. Update user.", 'green');
                                } 
                            } 
                            
						}	
					}
					
					
					foreach ($ldapstudslogin as $username => $login) {
						$straction = '';
						// $strlinkgroup1 = $strlinkgroup2 = '-';
					    $find = array_search($login, $deanstudslogin);
												 
						$ldapstud = $ldapstuds[$login];
						if 	($find === false)	 {
							
							if ($makemove)	{
								$flag1 =  true;
								if (!$agroup = get_record_select('dean_academygroups', "name = '$namegroup'", 'id, facultyid, specialityid, curriculumid, name'))	{
									$flag1 = false;
									// notify (get_string ('groupnotexists', 'block_dean', $newgropustudent->group1));
									$straction .= '<b>' . get_string ('groupnotexists', 'block_dean', $namegroup) . '</b>';
								}	
									
								if ($flag1)	{
									enrol_dean($ldapstudentsobjects[$login], $login, $agroup);
									$straction = get_string('enrolinacademgroup', 'block_dean', $agroup->name);
								}			
							}
							
							$table->data[] = array ($login, "<b>-</b>", "<u>$ldapstud</u>", '', $straction);
							$totalcountldap++;									
						} else {
							// $ldapstud = $ldapstuds[$login];
							// $table->data[] = array ($login, $strlink . "<b>$deanstud</b>", "<b>$ldapstud</b>", '', '');
						}	
					}
					
				}	else {
					print_heading("Группа $namegroup не найдена в системе LDAP", 'center', 2);
					return false;
				}
			}  else {
				print_heading("Студенты группы $namegroup не найдены в системе Пегас", 'center', 2);
				
				if ($lstudents = get_records_select('dean_ldap', "group1= '$namegroup' or group2= '$namegroup'", 'lastname', 'id, login, firstname, lastname, secondname, group1, group2'))	{
					$ldapstudslogin =array();
					$ldapstuds = array();
					$ldapstudscheck = array();  
					foreach($lstudents as $lstudent)	{
						$ldapstudslogin[$lstudent->login] = $lstudent->login; 
						$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
						$ldapstudscheck[$lstudent->login] = false;
						$ldapstudentsobjects[$lstudent->login] = $lstudent; 
					}	
					
					foreach ($ldapstudslogin as $username => $login) {
						$straction = '';
						// $strlinkgroup1 = $strlinkgroup2 = '-';
						$ldapstud = $ldapstuds[$login];
		
						if ($makemove)	{
							$flag1 =  true;
							if (!$agroup = get_record_select('dean_academygroups', "name = '$namegroup'", 'id, facultyid, specialityid, curriculumid, name'))	{
								$flag1 = false;
								// notify (get_string ('groupnotexists', 'block_dean', $newgropustudent->group1));
								$straction .= '<b>' . get_string ('groupnotexists', 'block_dean', $namegroup) . '</b>';
							}	
									
							if ($flag1)	{
								enrol_dean($ldapstudentsobjects[$login], $login, $agroup);
								$straction = get_string('enrolinacademgroup', 'block_dean', $agroup->name);
							}			
						}
							
						$table->data[] = array ($login, "<b>-</b>", "<u>$ldapstud</u>", '', $straction);
						$totalcountldap++;									
					}
				}	else {
					print_heading("Группа $namegroup не найдена в системе LDAP", 'center', 2);
					return false;
				}
					
		}
		} else {
			print_heading("Группа $namegroup не найдена в системе Пегас", 'center', 2);
			return false;
		}
	}
	
	return $table;
}


function unenroll_from_agroup_this_faculty($user, $agroup)
{
    if ($amembers = get_records_select('dean_academygroups_members', "userid = $user->id")) {
        $existgroupsids = array();
        foreach($amembers as $amember)  {
	    	if ($amember->academygroupid == $agroup->id) continue;
            $existgroupsids[]=$amember->academygroupid;             
        }
        $strids = implode (',', $existgroupsids);
        if ($eagroups = get_records_select('dean_academygroups', "id in ($strids)"))    {
            foreach ($eagroups as $eagroup) {
                if ($eagroup->facultyid == $agroup->facultyid) {
        			if ($mgroups = get_records_select("groups", "name = '$eagroup->name'", '', 'id, courseid, name'))  {
        				 foreach($mgroups as $mgroup)  {
        					unenrol_student_dean ($user->id, $mgroup->courseid);
        	            	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $user->id);
        				 }
        			}
        			delete_records('dean_academygroups_members', 'userid', $user->id, 'academygroupid', $eagroup->id);
                    notify('Студент исключен из группы '. $eagroup->name . ' этого же факультета. (' . $eagroup->id );                    
                }
            }    
        } 
    }
}	



function registration_agroup_from_bsu_students($namegroup)
{
    global $CFG;

    $agroup = get_record('dean_academygroups', 'name', $namegroup);
	// print_r($agroup); echo '<br>';
	if ($agroup)	{
	   /*
		$numst = get_records('dean_academygroups_members', 'academygroupid', $agroup->id);
		// print_r($numst); echo '<br>';
	    if ($numst)	{
	    	error(get_string('syncgrouphavestudents', 'block_dean'), 'ldapreg.php');
	    }
       */ 
        /*
		if ($lstudents = get_records('dean_ldap', 'group1', $namegroup, 'lastname'))	{

			$ldapstuds = array();
			foreach($lstudents as $lstudent)	{
				$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
			}	
				
			$table->head  = array (get_string("ldapstuds","block_dean"), get_string("logins", "block_dean"), get_string("action","block_dean"));
			$table->align = array ("left", "center", "center");
			$table->size = array ('20%', '15%', '10%');
			$table->width = '50%';
			$strlinkupdate = '-';
			
            // print_object($agroup);
            
			foreach($lstudents as $lstudent)	{
				
				$table->data[] = array ($ldapstuds[$lstudent->login], $lstudent->login, $strlinkupdate);
			
                // print_object($lstudent);
                 	
				// dean_registry_ldap($lstudent, $agroup);
			}	
			print_table($table);
		}	
        */
        
        $idgroup = get_field_select('bsu_ref_groups', 'id', "name = '$namegroup'");
        $usernames = get_records_select_menu('bsu_group_members', "groupid=$idgroup and deleted=0", '', 'id, username');
        // print_object($usernames);                   
        $strusers = implode (',', $usernames); 
        $sql = "CodePhysPerson in ($strusers)";
        // echo $sql;
        // $lstudents2 = get_records_select('bsu_students', "grup= '$namegroup' and Dateotsh = ''", 'name', 'CodePhysPerson as login, Name, grup');
        if ($lstudents2 = get_records_select('bsu_students', $sql, 'name', 'CodePhysPerson as login, Name')) {
        // if ($lstudents2 = get_records_select('bsu_students', "grup= '$namegroup' and Dateotsh = ''", 'name', 'CodePhysPerson as login, Name, grup'))    {
            
            $ldapstuds = array();
		    foreach($lstudents2 as $lstudent)	{
		        $ldapstuds[$lstudent->login]->login = $lstudent->login; 
                $ldapstuds[$lstudent->login]->name = trim($lstudent->Name);
                list ($lastname, $firstname, $secondname) = explode(' ', $lstudent->Name);
                $ldapstuds[$lstudent->login]->lastname =  $lastname;
                $ldapstuds[$lstudent->login]->firstname =  $firstname;
                $ldapstuds[$lstudent->login]->secondname =  $secondname;						
                $ldapstuds[$lstudent->login]->group1 =  $namegroup;// trim($lstudent->grup);
                $ldapstuds[$lstudent->login]->group2 =  '';
            }

			$table->head  = array (get_string("ldapstuds","block_dean"), get_string("logins", "block_dean"), get_string("action","block_dean"));
			$table->align = array ("left", "center", "center");
			$table->size = array ('20%', '15%', '10%');
			$table->width = '50%';
			$strlinkupdate = '-';
        
			foreach($ldapstuds as $lstudent)	{
				
				$table->data[] = array ($ldapstuds[$lstudent->login]->name, $lstudent->login, $strlinkupdate);
                // print_object($lstudent);
				dean_registry_ldap($lstudent, $agroup);
			}	
			print_table($table);
		}
        
        
        	
	}
	else {
		notify(get_string('groupnotfound','block_dean') . ': ' . $namegroup);
		echo '<hr>';
	}
}



/**
 * 
 */ 
function sync_group_with_infobelgu($namegroup, $go=0)   
{
    global $CFG;
    
	$strsynchronization = get_string('ldapsynchronization','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');
            
    if (isset($namegroup) && !empty($namegroup))  {

	    $agroup = get_record('dean_academygroups', 'name', $namegroup);
		$mgroups = get_records("groups", "name", $namegroup);	    							
		if ($agroup)	{
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, u.auth, m.academygroupid
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE m.academygroupid={$agroup->id}  AND deleted = 0
	                       ORDER BY u.lastname";
			// echo $studentsql . '<br />';
			if ($astudents = get_records_sql($studentsql))	{

				$deanstuds = array();
				$deanstudsid = array();
				foreach($astudents as $astudent)	{
					$deanstudsauth[$astudent->username] = $astudent->auth;
					$deanstudsid[$astudent->username] = $astudent->id;	
					$deanstuds[$astudent->username] = trim($astudent->lastname) . ' ' . trim($astudent->firstname);
					$deanstudentsobjects[$astudent->username] = $astudent;
				}

				/*
				if ($lstudents = get_records_select('dean_ldap', "group1= '$namegroup' or group2= '$namegroup'", 'lastname'))	{
					$ldapstuds = array();
					$ldapstudscheck = array();  
					foreach($lstudents as $lstudent)	{
						$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
						$ldapstudscheck[$lstudent->login] = false; 
						$newusers[$lstudent->login]->lastname =  trim($lstudent->lastname);
						$newusers[$lstudent->login]->firstname =  trim($lstudent->firstname);
						$newusers[$lstudent->login]->secondname =  trim($lstudent->secondname);						
						$newusers[$lstudent->login]->group1 =  trim($lstudent->group1);
						$newusers[$lstudent->login]->group2 =  trim($lstudent->group2);
					}
                } else {
                    $lstudents = array();
					$ldapstuds = array();
					$ldapstudscheck = array();  
                }    
                    // echo count($newusers) . '<br>';
                */    
                    $idgroup = get_field_select('bsu_ref_groups', 'id', "name = '$namegroup'");
                    $usernames = get_records_select_menu('bsu_group_members', "groupid=$idgroup and deleted=0", '', 'id, username');
                    // print_object($usernames);                   
                    $strusers = implode (',', $usernames); 
                    $sql = "CodePhysPerson in ($strusers) and idKodPrith=1";
                    // echo $sql;
                    // $lstudents2 = get_records_select('bsu_students', "grup= '$namegroup' and Dateotsh = ''", 'name', 'CodePhysPerson as login, Name, grup');
                    $lstudents2 = get_records_select('bsu_students', $sql, 'name', 'CodePhysPerson as login, Name');
					foreach($lstudents2 as $lstudent)	{
                        if (!isset($ldapstuds[$lstudent->login]))   {			   
                            $ldapstuds[$lstudent->login] = trim($lstudent->Name);
                            $ldapstudscheck[$lstudent->login] = false;
                            // $newusers2[$lstudent->login]->Name =  trim($lstudent->Name);						
                            // $newusers2[$lstudent->login]->grup =  trim($lstudent->grup);
                            
                            list ($lastname, $firstname, $secondname) = explode(' ', $lstudent->Name);
                            $newusers[$lstudent->login] = new stdClass(); 
                            $newusers[$lstudent->login]->lastname =  $lastname;
                            $newusers[$lstudent->login]->firstname =  $firstname;
                            $newusers[$lstudent->login]->secondname =  $secondname;						
                            $newusers[$lstudent->login]->group1 =  $namegroup; // trim($lstudent->grup);
                            $newusers[$lstudent->login]->group2 =  '';
                       }       
					}	
                    // echo count($newusers) . '<br>';
                    // print_object($newusers);
                    if ($go <> 1)	{
                        $table = new stdClass();
    					$table->head  = array ('№', get_string('deanstuds',"block_dean"), get_string("ldapstuds","block_dean"), 
    					get_string("logins", "block_dean"), get_string("group").' 1', get_string("group").' 2', get_string('action',"block_dean"));
    					$table->align = array ("left","left", "left", "center", "center", "center", "center");
    					$table->size = array ('3%', '30%', '20%', '15%', '10%', '10%', '5%');
    					$table->width = '95%';
                    }    
						
                    $i = 1;    
					foreach ($deanstuds as $username => $deanstud) {
						$strlinkgroup1 = $strlinkgroup2 = '-';
						$login = array_search($deanstud, $ldapstuds);
						$strlink = print_user_picture($deanstudentsobjects[$username]->id, 1, $deanstudentsobjects[$username]->picture, false, true);
						$strlinkgroup1 = "<br><strong><a title=\"$agroup->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup->facultyid}&amp;sid={$agroup->specialityid}&amp;cid={$agroup->curriculumid}&amp;gid={$agroup->id}\">$agroup->name</a></strong>";

						if ($deanstudsauth[$username] == 'cas')	{
							$studentLDAP = get_record('dean_ldap', 'login', $username);	
						} else {
							$studentDEAN = get_record('user', 'id', $deanstudsid[$username]);
							list ($afirstname, $asecondname) = explode(' ', $studentDEAN->firstname); 
							$studentLDAP = get_record('dean_ldap', 'firstname', $afirstname, 'lastname', $studentDEAN->lastname, 'secondname', $asecondname);
						}
						
						if (is_numeric($studentLDAP->group2))	{
						
							if ($studentLDAP->group2 == $agroup->name)	{
								$namegroup2 = $studentLDAP->group1;
							} else {
								$namegroup2 = $studentLDAP->group2;
							}
							
							if ($agroup2 = get_record('dean_academygroups', 'name', $namegroup2))	{
								$strlinkgroup2 = "<br><strong><a title=\"$agroup2->name\" href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$agroup2->facultyid}&amp;sid={$agroup2->specialityid}&amp;cid={$agroup2->curriculumid}&amp;gid={$agroup2->id}\">$agroup2->name</a></strong>";	
							} else {
								$strlinkgroup2 = $namegroup2;
							}
						}   

												 
						if 	($login === false)	 {
							$table->data[] = array ($i . '.', $strlink . "<b>$deanstud</b>", '<b>? ? ?</B>', 
											$username . ' => ?', $strlinkgroup1, $strlinkgroup2, '');
                            
                            // проверяем на смену фамилии (выход замуж)
                            if (isset($ldapstuds[$username])) {
                                
                                if ($go == 1)	{
                                    $strfirstname = $newusers[$username]->firstname . ' ' . $newusers[$username]->secondname;  
                                    set_field('user', 'lastname', $newusers[$username]->lastname, 'id', $deanstudsid[$username]);
                                    set_field('user', 'firstname', $strfirstname, 'id', $deanstudsid[$username]);
                                    $ldapstudscheck[$username] = true;
                                    notify ("<b>$deanstud</b> сменила фамилию на <b>" . $newusers[$username]->lastname . '</b>!', 'green');   
                                }
                                    
                            } else {  
                                /*
                                $idgroup = get_field_select('bsu_ref_groups', 'id', "name = '$namegroup'");
                                $usernames = get_records_select_menu('bsu_group_members', "groupid=$idgroup and deleted=0", 'id, username');
                                $strusers = implode (',', $usernames); 
                                */
										
                                $academs = get_records_select('bsu_students', "CodePhysPerson = '$username' and idKodPrith=1", 'id', 'CodePhysPerson as login, name, idKodPrith, TextPrith');
                                $academ = end($academs);
                                notify ("<b>$deanstud</b> " . $academ->name . '</b>!', 'green');        	
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
    								delete_records('dean_academygroups_members', 'userid', $removestudent, 'academygroupid', $agroup->id);			
    								echo '----------------------------------------<br>';					
    							}	
							}
							
						}  else {
							$ldapstudscheck[$login] = true;

							$rokirovkalink = '';
							if ($username != $login)	{
								$title = get_string('rokirovka', 'block_dean');
				                $rokirovkalink .= "<a title=\"$title\" href=\"ldapsync.php?username=$username&amp;login=$login&amp;action=rokirovka&amp;namegroup=$namegroup&amp;go=1\">";
								$rokirovkalink .= "<img src=\"{$CFG->pixpath}/i/tick_amber_big.gif\" alt=\"$title\" /></a>&nbsp;";
							}
							
							$table->data[] = array ($i  . '.', $strlink.$deanstud, $ldapstuds[$login], 
											$username . ' => ' . $login, $strlinkgroup1, $strlinkgroup2, $rokirovkalink);
											
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
                        $i++;
					}
					
					$strlinkgroup1 = $strlinkgroup2 = '-';
					foreach($ldapstudscheck as $login => $check1)  {
						if (!$check1)	{
							
							$strdeanstud = get_already_exist_users($newusers[$login], $login, $agroup);

							$table->data[] = array ($i++, $strdeanstud, $ldapstuds[$login], '? => ' . $login, $strlinkgroup1, $strlinkgroup2);
							if ($go == 1)	{											
								enrol_dean($newusers[$login], $login, $agroup);
							}	
							// break;				
						}
					}
                    if ($go != 1)	{
                        if (isset($namegroup) && !empty($namegroup)) 	{	 
                            echo '<div align=center>
                            <form name="studentform" id="studentform" method="post" action="ldapsync.php">'.
                        	 '<input type="hidden" name="go" value="1">' .				 
                        	 '<input type="hidden" name="namegroup" value="' . $namegroup . '" />'.
                             '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
                        	 '</form></div><p></p>';
                        }			 
                        
					   print_table($table);
                    }   					
				
			}
		} else {
			notify(get_string('groupnotfound','block_dean') . ': ' . $namegroup);
			echo '<hr>';
		}
	}
}
  
?>