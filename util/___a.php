<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');

/*

SELECT REFS.Name as SubDepartment, REFD.Name as DisciplineName
FROM   mdl_bsu_plans AS P INNER JOIN mdl_bsu_disciplines AS D ON P.Id = D.PlanId INNER JOIN
       mdl_bsu_ref_disciplinename AS REFD ON REFD.Id = D.DisciplineNameId INNER JOIN
       mdl_bsu_ref_subdepartment AS REFS ON REFS.Id = D.SubDepartmentId INNER JOIN
       mdl_bsu_ref_speciality AS REFSP ON REFSP.Id = P.SpecialityId
where  P.SpecialityId=117 AND REFS.NotUsing=0
Order by Disciplinename


		    TRUNCATE TABLE mdl_question_states;
		    TRUNCATE TABLE mdl_question_sessions;
		    TRUNCATE TABLE mdl_question_attempts;
		    TRUNCATE TABLE mdl_stats;

*/
    
   	$num = optional_param('n', 1, PARAM_INT);
   	
	$COUNTATTEMPT = 1000;
	// $STARTTIME = 1262305364; // 01.01.2010
	$STARTTIME = 1251764564; // 01.09.2009
	
	$strtitle = '__Clear all ATTEMPT early then 01.01.2010 (' .$STARTTIME. ')';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/admin/index.php">'.get_string('admin').'</a>';
	$breadcrumbs .= " -> " . $strtitle;
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	/*
	$attempts = count_records_select('quiz_attempts', "timestart < $STARTTIME");
	echo $attempts . '<br>';
	exit(0);	
	*/

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

	$num++;
  	if (delete_all_attempt())	{
  		redirect ("___a.php?n=$num", $num, 2);
  	} else {
	    $tables = array('grade_outcomes_history', 'grade_categories_history', 
						'grade_items_history', 'grade_grades_history', 'scale_history');
	    foreach ($tables as $table) {
	        if (delete_records_select($table, "timemodified < $STARTTIME")) {
	             echo "Deleted old grade history records from '$table' <br>";
	        }
	    }
  	}
 
 	
	print_footer();
	


	
function delete_all_attempt() 
{
	global $COUNTATTEMPT, $STARTTIME;
	
	$strsql = "SELECT * FROM mdl_quiz_attempts where timestart < $STARTTIME LIMIT $COUNTATTEMPT";
	// echo $strsql . '<br>';
	if ($attempts = get_records_sql ($strsql))	{	
	
		// echo '$COUNTATTEMPT = ' . $COUNTATTEMPT . '<hr>';
		// $i=1;
		foreach ($attempts as $attempt)	{
			// echo $i . '.' . $attempt->id .'<br>';  
		    delete_records('quiz_attempts', 'id', $attempt->id);
		    // delete_records("question_states", "attempt", $attempt->uniqueid);
		    // delete_records("question_sessions", "attemptid", $attempt->uniqueid);
		    // delete_records("question_attempts", "id", $attempt->uniqueid);
		    delete_records('quiz_grades', 'userid', $attempt->userid,'quiz', $attempt->quiz);
		    // $i++;
		}
		return true;
	} 
	return false;
}	 

?>


