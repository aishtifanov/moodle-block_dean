<?php // $Id: ldap2.php,v 1.4 2012/10/19 06:24:42 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

    // LOAD DATA INFILE 'C:\\usr\\wwwroot\\moodledata\\1\\moddata\\bsuusers.csv' INTO TABLE  mdl_dean_ldap2
    // FIELDS TERMINATED BY ';' IGNORE 1 LINES (login, lastname, firstname, work1, dol, facultet)

    $lastname = optional_param('lastname', '');		// Group name (number)
	$firstname = optional_param('firstname', '');		// Group name (number)
   	$go = optional_param('go', 0, PARAM_INT);			//     

	$admin_is = isadmin();
    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }
    
	$strsynchronization = get_string('ldapaspirant','block_dean');
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);

	
    $currenttab = 'ldap2';
    include('ldaptabs.php');
    
    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	
	$userfrom = get_record('user', 'username', 'shtifanov');
	 
    if (!empty($lastname) || !empty($firstname)) 	{	
    	$strsql = "SELECT DISTINCT * FROM {$CFG->prefix}dean_ldap2
	    							WHERE lastname LIKE '$lastname%' and firstname LIKE '$firstname%'
	    							ORDER BY lastname";							
    	
	    $ldaps = get_records_sql($strsql);
	   // print_r(count($ldaps));
	if ($ldaps)  {
		
		$table->head  = array (get_string("ldapusers","block_dean"), get_string('deanusers', 'block_dean'), get_string("login","block_dean"));
		$table->align = array ("left", "left", "center");
		$table->size = array ('20%', '30%', '20%');
		$table->width = '70%';
	
		foreach ($ldaps as $ldap){

			if ($user = get_record_sql("SELECT DISTINCT u.id, u.firstname, u.lastname, u.username, u.auth, u.email, u.picture 
										FROM {$CFG->prefix}user u INNER JOIN {$CFG->prefix}role_assignments ra on u.id=ra.userid 
									    WHERE u.auth = 'manual' AND u.deleted = 0 and u.lastname = '{$ldap->lastname}' and u.firstname = '{$ldap->firstname}' and (ra.roleid=3 or ra.roleid=4)")) {
	
				if ($olduser = get_record('user', 'username', $ldap->login))  {
						if ($olduser->lastname == $ldap->lastname && $olduser->firstname == $ldap->firstname)	{
							if ($go == 1)	{
								update_and_email($user, $ldap, $userfrom);
								$table->data[] = array($ldap->lastname.' '.$ldap->firstname, $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')', $user->username .' chnange to '. $ldap->login );			
							} else {
								$table->data[] = array($ldap->lastname.' '.$ldap->firstname, print_user_picture($user->id, 1, $user->picture, false, true) . $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')', $user->username .' => '. $ldap->login );
							}	
						} else {
							$table->data[] = array("<b>$ldap->lastname $ldap->firstname ($ldap->login)</b>", print_user_picture($olduser->id, 1, $olduser->picture, false, true) . "<b>$olduser->lastname $olduser->firstname ($olduser->username)</b>", '?' );						
						}
				} else {
							if ($go == 1)	{
								update_and_email($user, $ldap, $userfrom);
								$table->data[] = array($ldap->lastname.' '.$ldap->firstname, print_user_picture($user->id, 1, $user->picture, false, true) . $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')', $user->username .' chnange to '. $ldap->login );
							} else {
								$table->data[] = array($ldap->lastname.' '.$ldap->firstname, print_user_picture($user->id, 1, $user->picture, false, true) . $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')', $user->username .' => '. $ldap->login );
							}					
				}
		
			 } else {
			 	if ($olduser = get_record('user', 'username', $ldap->login, 'auth', 'ldap2' ))  {
					$table->data[] = array('^ '.$ldap->lastname.' '.$ldap->firstname, print_user_picture($olduser->id, 1, $olduser->picture, false, true) . $olduser->lastname.' '.$olduser->firstname . ' ('. $olduser->auth . ')', $olduser->username .' => '. $ldap->login );			 		
			 	}	
			 	
			 }
		}			 	
		 	 print_table($table);
	}					
}	

	print_heading($strsynchronization, 'center', 2);
	notify (get_string('description_aspirant','block_dean'), 'black');

    print_dean_box_start('center', '80%');
 	
	 echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		
		echo '<div align=center><form name="studentform" id="studentform" method="post" action="ldap2.php">'.
			 '<tr><td>'.get_string('lastname'). '&nbsp&nbsp'.':</td><td>'.
			 '<input type="text" name="lastname" size="30" value="' . $lastname. '" />'. '<br>'.'</td></tr>'.
			 '<tr><td>'.get_string('firstandsecname', 'block_dean'). '&nbsp&nbsp'.':</td><td>'.
			 '<input type="text" name="firstname" size="30" value="' . $firstname. '" />'. '<br>'.'</td></tr>'.
			 '<input type="hidden" name="go" value="0">' .
			 '</div>';
		echo '</table>';
		 
   	print_dean_box_end();

		echo '<div align=center><form name="studentform2" id="studentform2" method="get" action="ldap2.php">'.
		     '<input name="search" id="search" type="submit" value="' . $strsyncpreview   . '" />'.
			 '</form></div>';
			 
	 	if (!empty($lastname) || !empty($firstname)) 	{	 
			echo '<div align=center>
			<form name="studentform" id="studentform3" method="post" action="ldap2.php">'.
		 	     '<input type="hidden" name="lastname" value="' . $lastname . '" />'.
		 	     '<input type="hidden" name="firstname" value="' . $firstname . '" />'.
				 '<input type="hidden" name="go" value="1">' .				 
			     '<input name="search" id="search" type="submit" value="' . $strsynchroniz . '" />'.
				 '</form></div>';
		}
   	
    print_footer();


function update_and_email($user, $ldap, $userfrom)
{								  
	$rec->id = $user->id;
	// $rec->auth = 'ldap2';
    $rec->auth = 'cas';
	$rec->username = $ldap->login;
	$rec->password = 'not cached';
	$rec->email = $ldap->login.'@bsu.edu.ru';
	$rec->description = trim($ldap->work1) .'('. trim($ldap->dol).', '. trim($ldap->facultet).')';
	$rec->description = addslashes($rec->description);
	if (update_record('user', $rec)) {
		$userto = get_record('user', 'id', $user->id);
		
		$messagesubject = 'Изменение аутентификационных данных в системе электронного обучения ПЕГАС';
		$messagetext = "Уважаемый(ая) $user->firstname!
В системе электронного обучения ПЕГАС внедрена новая система аутентификации пользователей.	
Теперь для входа в систему используются такие же данные, как и при работе с электронной почтой БелГУ.		
Т.е. логин и пароль, используемые Вами для работы с Вашим электронным почтовым ящиком БелГУ, необходимо использоваться и для входа в СДО ПЕГАС.
С уважением,  $userfrom->lastname  $userfrom->firstname";
		if (!email_to_user($userto, $userfrom, $messagesubject, $messagetext, '')) {
			echo $messagesubject . '<hr>';
			echo $messagetext . '<hr>'; 
			notify('Email not sent to '. $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')',$ldap->login .' => '.$user->username);
		} else {
			notify('Уведомление отправлено на адрес '. $userto->email, 'green', 'center');
		}	
	} else {
		notify('Not change '.$ldap->lastname.' '.$ldap->firstname, $user->lastname.' '.$user->firstname . ' ('. $user->auth . ')',$ldap->login .' => '.$user->username);
	}
}



?>