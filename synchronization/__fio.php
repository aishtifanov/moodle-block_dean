<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../lib.php');
    require_once('lib_ldap.php');    

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> __Синхронизация фамилий";
    print_header("$SITE->shortname: 1", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

  	sync_fio();
 
	print_footer();
	
	
	
function sync_fio() 
{
	global $CFG;
	
     $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.picture, u.auth, m.academygroupid
                   FROM {$CFG->prefix}user u
                   LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id";
	
	if ($astudents = get_records_sql($studentsql))	{
	    $deanstudsid = array();
		$deanstuds = array();
		foreach($astudents as $astudent)	{
		    $deanstudsid[$astudent->username] = $astudent->id;
			$deanstuds[$astudent->username] = trim($astudent->lastname) . ' ' . trim($astudent->firstname);
		}
    }    

	if ($lstudents = get_records_select('dean_ldap', "", '', 'id, login, firstname, lastname, secondname'))	{
		$ldapstuds = array();
		foreach($lstudents as $lstudent)	{
			$ldapstuds[$lstudent->login] = trim($lstudent->lastname) . ' ' . trim($lstudent->firstname). ' ' . trim($lstudent->secondname);
            $ldapstudentsobjects[$lstudent->login] = $lstudent;
		}	
    }    
    
    $i=0;
    foreach($astudents as $astudent)	{
        if (!isset($ldapstuds[$astudent->username])) {
            // notify ("Not found in LDAP {$deanstuds[$astudent->username]}");
            continue; 
        }
        $deanstudsusername = mb_strtoupper($deanstuds[$astudent->username], 'UTF-8');
        $ldapstudslogin = mb_strtoupper($ldapstuds[$astudent->username], 'UTF-8');        
        if ($deanstudsusername <> $ldapstudslogin) {
            notify ("{$deanstuds[$astudent->username]} !=! {$ldapstuds[$astudent->username]}");
            $lstudent = $ldapstudentsobjects[$astudent->username];
            $rec->id = $deanstudsid[$astudent->username];
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
    notify("Complete. Total updated = $i");
	return true;
}	 

?>


