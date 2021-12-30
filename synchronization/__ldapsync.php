<?php // $Id: ldapsync.php,v 1.8 2011/09/23 07:41:50 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');    

    $namegroup = optional_param('namegroup', '');		// Group name (number)
   	$go = optional_param('go', 0, PARAM_INT);			//     
   	$action = optional_param('action', '');
   	$_username = optional_param('username', '');
   	$_login = optional_param('login', '');   	
   	

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

    $currenttab = 'ldapsync';
    include('ldaptabs.php');

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	// $admin_is = true; $go=1; $namegroup = '100459';
	 
	if ($action == 'rokirovka')	{
		if (!empty($_username) && !empty($_login))	{
			 if ($usermanual = get_record('user', 'username', $_username))	{
			 	// print_r($usermanual); echo '<hr>';
		 		if ($userlogin = get_record('user', 'username', $_login))	{
		 			// print_r($userlogin); echo '<hr>';
		 			if ($usermanual->lastname == $userlogin->lastname)	{
		 				// print_r($usermanual); echo '<hr>'; print_r($userlogin); echo '<hr>';
		 				if ($dms = get_records('dean_academygroups_members', 'userid', $userlogin->id))	{
		 					
		 					foreach ($dms as $dm)	{
								$academygroup = get_record_sql("SELECT * FROM {$CFG->prefix}dean_academygroups
	  		       								  			    WHERE id={$dm->academygroupid}");

								if ($mgroups = get_records("groups", "name", $academygroup->name))  {
									 foreach($mgroups as $mgroup)  {
										unenrol_student_dean ($userlogin->id, $mgroup->courseid);
					                	delete_records('groups_members', 'groupid', $mgroup->id, 'userid', $userlogin->id);
									 }
								}
								delete_records('dean_academygroups_members', 'userid', $userlogin->id, 'academygroupid', $academygroup->id);
		 					}
		 					
		 				}
		 				delete_records('user', 'id', $userlogin->id);
		 				notify ("Пользователь с логином  $_login удален.");
		 			} 
		 		}	
			 }
		}
	} 
	
	
    if (isset($namegroup) && !empty($namegroup)) 	{

	    $agroup = get_record('dean_academygroups', 'name', $namegroup);
		$mgroups = get_records("groups", "name", $namegroup);	    							
		if ($agroup)	{
	         $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, u.auth, m.academygroupid
	                       FROM {$CFG->prefix}user u
	                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
	                       WHERE m.academygroupid={$agroup->id}  AND deleted = 0
	                       ORDER BY u.lastname";
			
			if ($astudents = get_records_sql($studentsql))	{

				$deanstuds = array();
				$deanstudsid = array();
				foreach($astudents as $astudent)	{
					$deanstudsauth[$astudent->username] = $astudent->auth;
					$deanstudsid[$astudent->username] = $astudent->id;	
					$deanstuds[$astudent->username] = trim($astudent->lastname) . ' ' . trim($astudent->firstname);
					$deanstudentsobjects[$astudent->username] = $astudent;
				}

				
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

					$table->head  = array (get_string('deanstuds',"block_dean"), get_string("ldapstuds","block_dean"), 
					get_string("logins", "block_dean"), get_string("group").' 1', get_string("group").' 2', get_string('action',"block_dean"));
					$table->align = array ("left", "left", "center", "center", "center", "center");
					$table->size = array ('30%', '20%', '15%', '10%', '10%', '5%');
					$table->width = '95%';
						
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
							$table->data[] = array ($strlink . "<b>$deanstud</b>", '<b>? ? ?</B>', 
											$username . ' => ?', $strlinkgroup1, $strlinkgroup2, '');
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

							$rokirovkalink = '';
							if ($username != $login)	{
								$title = get_string('rokirovka', 'block_dean');
				                $rokirovkalink .= "<a title=\"$title\" href=\"ldapsync.php?username=$username&amp;login=$login&amp;action=rokirovka&amp;namegroup=$namegroup&amp;go=1\">";
								$rokirovkalink .= "<img src=\"{$CFG->pixpath}/i/tick_amber_big.gif\" alt=\"$title\" /></a>&nbsp;";
							}
							
							$table->data[] = array ($strlink.$deanstud, $ldapstuds[$login], 
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
					}
					
					$strlinkgroup1 = $strlinkgroup2 = '-';
					foreach($ldapstudscheck as $login => $check1)  {
						if (!$check1)	{
							
							$strdeanstud = get_already_exist_users($newusers[$login], $login, $agroup);

							$table->data[] = array ($strdeanstud, $ldapstuds[$login], 
											'? => ' . $login, $strlinkgroup1, $strlinkgroup2);
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
	// notify (get_string('description_synchronization','block_dean'), 'black');
	
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

?>