<?php // $Id: ldap2.php,v 1.2 2010/12/10 11:05:51 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');

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

	print_heading($strsynchronization, 'center', 2);
	notify (get_string('description_aspirant','block_dean'), 'black');
	
	$table->head  = array (get_string("ldapusers","block_dean"), get_string('deanusers', 'block_dean'), get_string("login","block_dean"));
	$table->align = array ("left", "left", "left");
	$table->size = array ('20%', '30%', '20%');
	$table->width = '70%';
    
    $strsql = "SELECT id, username, lastname, firstname FROM {$CFG->prefix}user
              where (auth = 'ldap2' || email like '%@bsu.edu.ru') and  deleted = 0";
    if($teachers = get_records_sql($strsql)) {
        foreach ($teachers as $teacher) 	{
            $teacherusername = strtoupper($teacher->username);
    		$teachermenu[$teacherusername] = $teacher->id; // fullname($teacher);
    	}
    }

   	$strsql = "SELECT login, lastname, firstname, work1, dol, facultet FROM {$CFG->prefix}dean_ldap2";							
    if ($ldaps = get_records_sql($strsql))  {
        foreach ($ldaps as $ldap)   {
            $login = strtoupper($ldap->login);
            if (!isset($teachermenu[$login]))   {
		        $table->data[] = array($ldap->lastname.' '.$ldap->firstname, '', $ldap->login);
                insert_ldap2($ldap);                
            } else {
                /*
                if ($olduser = get_record_select('user', "username = '$ldap->login'", "id, username, lastname, firstname, picture")) {
                    $table->data[] = array("<b>$ldap->lastname $ldap->firstname ($ldap->login)</b>", print_user_picture($olduser->id, 1, $olduser->picture, false, true) . "<b>$olduser->lastname $olduser->firstname ($olduser->username)</b>", '');
                }  else {
                    $table->data[] = array("<b>$ldap->lastname $ldap->firstname ($ldap->login)</b>", '?', '?' );    
                } 
                */   
            }
        }    
    }
  	
    print_table($table);

    print_footer();
    
    
function insert_ldap2($ldap)
{
    $user->lastname = trim($ldap->lastname);
	$user->firstname = trim($ldap->firstname);
	$user->username = $ldap->login;
    $user->password = 'not cached';
	$user->auth = 'ldap2';
    $user->mnethostid = 1;
	$user->email = $user->username . '@bsu.edu.ru';
	$user->city = get_string('town', 'block_dean');
	$user->country = 'RU';
	$user->lang = 'ru_utf8';
    $user->confirmed = 1;
    $user->timemodified = time();
    $user->description = $ldap->work1 . ' ('. $ldap->dol . ', ' . trim($ldap->facultet) . ')';                

    // print_r($user);    echo '<hr />';
	// check already registred user
	if (!record_exists('user', 'username', $user->username)) {
    	if(!($user->id = insert_record("user", $user)))  {
	        notify(get_string('usernotaddederror', 'error', $user->username));
	    }
		else {
    	    notify("$user->id &raquo; $user->username &raquo; $user->lastname $user->firstname", 'green', 'left');
		}
	} else {
	   
	   notify('Username exists: ' . $user->username);
       $olduser = get_record_select('user', "username = '$user->username'", "id, auth, username, lastname, firstname, picture");
       notify("<b>$olduser->lastname $olduser->firstname ($olduser->username, $olduser->auth)</b>");     
	}
}    
?>