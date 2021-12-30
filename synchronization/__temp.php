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
    
	$strsynchronization = 'Change temp.ru to bsu.edu.ru';
    $strsynchroniz = get_string("synchroniz", 'block_dean');
    $strsyncpreview = get_string("syncpreview", 'block_dean');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strsynchronization";
    print_header("$SITE->shortname: $strsynchronization", $SITE->fullname, $breadcrumbs);
   
    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	print_heading($strsynchronization, 'center', 2);
/*	
	$table->head  = array (get_string("ldapusers","block_dean"), get_string('deanusers', 'block_dean'), get_string("login","block_dean"));
	$table->align = array ("left", "left", "left");
	$table->size = array ('20%', '30%', '20%');
	$table->width = '70%';
*/    
    $strsql = "SELECT id, username, lastname, firstname, email FROM {$CFG->prefix}user
              where (auth = 'cas') and (email like '%@temp.ru') and  (deleted = 0)";
    if($students = get_records_sql($strsql)) {
        foreach ($students as $student) 	{
            // $table->data[] = array($student->lastname.' '.$student->firstname, $student->email, $student->username);
            $student->email = $student->username . '@bsu.edu.ru';
            set_field('user', 'email', $student->email, 'id', $student->id); 
    	}
    }
	
    // print_table($table);
    echo 'Compete all';
    print_footer();
   
?>