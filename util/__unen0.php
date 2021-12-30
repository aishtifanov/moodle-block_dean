<?php // $Id: __unenroll.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

    require_once('../../config.php');
    require_once('lib.php');

    $i = optional_param('i', 0, PARAM_INT); // course id
    
    $strtitle = 'Unenroll deleted students from all.';

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

    
    //  unenroll_deleted_students_from_course();
    delete_unused_context();
  
    notify ('Complete all.');

	print_footer();
	
	
	
function unenroll_deleted_students_from_course($courseid=0) 
{
	global $CFG;
    
  	 $academystudents = get_records_sql('SELECT id FROM mdl_user where deleted=1');
     if ($academystudents) 	{
         $ii = 0;
	     foreach ($academystudents as $astud)	  {
            $ii++;
			unenrol_student_dean ($astud->id, $courseid);
         }
         notify('Unenroll students = '. $ii);
    } else {
        notify ('Not found deleted users.');
    }
}	 


function delete_unused_context()    
{
    $strsql = "SELECT DISTINCT a.contextid FROM mdl_role_assignments a LEFT JOIN  mdl_context c ON a.contextid=c.id WHERE c.id Is Null";
    
    if ($ctxs = get_records_sql($strsql))   {
        foreach ($ctxs as $ctx) {
            delete_records('role_assignments', 'contextid', $ctx->contextid); 
        }
    }
}
?>
