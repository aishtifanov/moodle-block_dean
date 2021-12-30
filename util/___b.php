<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');
	require_once($CFG->libdir.'/filelib.php');	
    
   	$num = optional_param('n', 1, PARAM_INT);
   	
	$COUNTATTEMPT = 1000;
	// $STARTTIME = 1262305364; // 01.01.2010
	$STARTTIME = 1251764564; // 01.09.2009
	
	$strtitle = '__Clear all assignment early then 01.01.2010 (' .$STARTTIME. ')';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	// DELETE FROM mdl_question_states WHERE timestamp < 1262305364
	// DELETE FROM mdl_log WHERE time < 1262305364


	for ($courseid=$num; $courseid<=$num+100; $courseid++)	{
		delete_all_assignment($courseid);
		// echo '<hr>';
	}
	$num += 100;
	redirect ("___b.php?n=$num", $num, 4);
  	/*
	  if (delete_all_assignment($courseid))	{
  		redirect ("___a.php?n=$courseid", $courseid, 2);
  	}  
 	*/
	print_footer();
	


	
function delete_all_assignment($courseid) 
{
	global $COUNTATTEMPT, $STARTTIME, $CFG;
	
    $strsql = "SELECT assub.userid, ass.id as assignment, ass.course 
               FROM {$CFG->prefix}assignment ass, {$CFG->prefix}assignment_submissions assub 
               WHERE assub.timemodified < $STARTTIME AND ass.course=$courseid AND assub.assignment=ass.id";
	
	// echo $strsql . '<br>';
	if ($assignments = get_records_sql ($strsql))	{	
		foreach ($assignments as $assignment)	{
            $path = usercleaner_get_assignments_filepath($assignment);
            echo $path . '<br>';
            fulldelete($path);
            // unlink($path);
		}
	} 
	return true;
}	 


/**
 * Retrieves the path of the directory containing the files of the user in an assignment
 *
 * @return      array               $infos      a 0 based indexed 2-dimensional array of the recordset containing infos to build the path
 */
function usercleaner_get_assignments_filepath($info) {
    global $CFG;

    $path   = $CFG->dataroot.'/';
    $path  .= $info->course.'/';
    $path  .= $CFG->moddata.'/';
    $path  .= 'assignment/';
    $path  .= $info->assignment.'/';
    $path  .= $info->userid;
    return $path;
}


?>


