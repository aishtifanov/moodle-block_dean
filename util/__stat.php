<?php // $Id: __unenroll.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $i = optional_param('i', 0, PARAM_INT); // course id
    $timetodo = optional_param('t', 1304294400, PARAM_INT); // 02.05.2011 
    
    $strtitle = 'Statistics role assignment.';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); // see bug report 5352. This should kill this thread as soon as user aborts.
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

    
    // statistics_role_assignment();
    
     $table = statistics_forum_posts($timetodo);
    
    // $table = students_in_not_ldap();
    print_table($table);
    
    notify ('Complete all.');

	print_footer();
	
	
	
function statistics_role_assignment() 
{
	global $CFG;
  
    if ($academystudents = get_records_sql('SELECT distinct userid FROM mdl_role_assignments')) 	{
         $ii = 0;
         echo '<pre>';
	     foreach ($academystudents as $astud)	  {
            $ii++;
            $cnt = count_records('role_assignments', 'userid', $astud->userid);
            echo $astud->userid . '; ' . $cnt . '<br>'; 
         }
         echo '<pre>';
         notify('Complete = '. $ii);
    } else {
        notify ('Not found deleted users.');
    }
}	 


function statistics_forum_posts($timetodo) 
{
	global $CFG;
  
	$table->head  = array ('userid', 'fullname', 'role', 'lastaccess', 'count', 'num');
    $table->align = array ("left", "left", "center", "center", "center", "center");
    $table->size = array('10%', '15%', '5%', '5%', '5%', '5%');
   	$table->width = '70%';
  
    if ($academystudents = get_records_sql('SELECT distinct userid FROM mdl_forum_posts')) 	{
         $ii = $allcount = 0;
	     foreach ($academystudents as $astud)	  {
            $ii++;
            $cnt = count_records_select('forum_posts', "userid = $astud->userid AND created <= $timetodo");
            $user = get_record_select('user', "id = $astud->userid", 'id, lastname, firstname, lastaccess');
            if ($roles = get_records_select('role_assignments', "userid = $astud->userid", 'roleid', 'id, roleid')) {
                $strroles = array();
                foreach ($roles as $role)   {
                    // $strroles .= $role->roleid . ', ';
                    $strroles[] = $role->roleid;
                }
            } else {
                $strroles[] = 0;
            }
            
            if ($user->lastaccess) {
                $lastaccess = format_time(time() - $user->lastaccess);
            } else {
                $lastaccess = get_string('never');
            }
            
            $m = min($strroles);
            
            if (($m >=5 || $m == 0) && $cnt != 0) {
                $table->data[] = array($astud->userid,  fullname($user), $m, $lastaccess, $cnt, $ii);
                echo $astud->userid . ', ';
            }  
            $allcount +=  $cnt;  
         }
    } else {
        notify ('Not found deleted users.');
    }
    echo '<hr>' . $allcount . '<hr>';
    return $table;
}	 


function clear_course_forum() 
{
	global $CFG;
    
/*
SELECT course, count(course) as cnt FROM mdl_forum
Group by course
Having count(course)>2
Order by cnt DESC



SELECT course, count(course) as cnt FROM mdl_forum_discussions
Group by course
Having count(course)>2
Order by cnt DESC

   
  
	$table->head  = array ('userid', 'fullname', 'role', 'count');
    $table->align = array ("left", "left", "center", "center");
    $table->size = array('10%', '15%', '5%', '5%');
   	$table->width = '70%';
  
    if ($academystudents = get_records_sql('SELECT distinct userid FROM mdl_forum_posts')) 	{
         $ii = 0;
	     foreach ($academystudents as $astud)	  {
            $ii++;
            $cnt = count_records('forum_posts', 'userid', $astud->userid);
            $user = get_record_select('user', "id = $astud->userid", 'id, lastname, firstname');
            if ($roles = get_records_select('role_assignments', "userid = $astud->userid", 'roleid', 'id, roleid')) {
                $strroles = array();
                foreach ($roles as $role)   {
                    // $strroles .= $role->roleid . ', ';
                    $strroles[] = $role->roleid;
                }
            } else {
                $strroles[] = 0;
            }     
            $table->data[] = array($astud->userid,  fullname($user), min($strroles), $cnt); 
         }
    } else {
        notify ('Not found deleted users.');
    }
    return $table;
*/    
}	 


function students_in_not_ldap() 
{
	global $CFG;

	$table->head  = array ('username', 'fullname', 'group', 'lastaccess', 'count');
    $table->align = array ("left", "left", "center", "center", "center");
    $table->size = array('10%', '15%', '5%', '5%', '5%');
   	$table->width = '70%';

    
    $strsql = "SELECT id, username, lastname, firstname, lastaccess FROM mdl_user where auth='cas' and deleted=0";
    if ($users = get_records_sql($strsql))  {
        $ii = 0;
        foreach ($users as $user)   {
            if (!$ldap = get_record_select('dean_ldap', "login = '$user->username'", 'id'))   {
                $ii++;
                // if (!$ldap2 = get_record_select('dean_ldap2', "login = '$user->username'", 'login'))   {
                $strgroup = '-'; 
                if ($amember = get_record_select('dean_academygroups_members', "userid = $user->id", 'academygroupid'))   {
                    if ($agroup = get_record_select ('dean_academygroups', "id = $amember->academygroupid", 'name'))    { 
                        $strgroup = $agroup->name;
                    }     
                    // notify (fullname($user) . ' not found in LDAP and LDAP2');
                }
                if (is_numeric($strgroup) || $strgroup == '-')  {
                    if ($user->lastaccess) {
                        $lastaccess = format_time(time() - $user->lastaccess);
                    } else {
                        $lastaccess = get_string('never');
                    }

                    $table->data[] = array($user->username,  fullname($user), $strgroup, $lastaccess, $ii);
                }         
            }
        }      
        notify ('Complete ' . $ii);
    }    
    return $table;
}
?>
