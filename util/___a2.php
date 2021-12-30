<?php // $Id: __zero.php,v 1.1.1.1 2009/10/29 08:23:05 Shtifanov Exp $

	require_once('../../config.php');


	$action = optional_param('action', '');
	$corseid = optional_param('cid', 1700, PARAM_INT); // course id
   	
	$COUNTATTEMPT = 1000;
	$STARTTIME = 1262305364; // 01.01.2010
	// $STARTTIME = 1251764564; // 01.09.2009
	
	$strtitle = '__Clear all ATTEMPT early then 01.01.2010 (' .$STARTTIME. ')';

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
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

	if ($action == 'change' && $corseid != 0)	{
	    ignore_user_abort(false); 
	        
	    @set_time_limit(0);
	    @ob_implicit_flush(true);
	    @ob_end_flush();
	    
	  	if (delete_all_attempt_in_course($corseid))	{
	  		notify('Complete', 'green');
	  	} 
	}

 	$searchtext = (string)$corseid;
 	
 	$datefrom = date('d.m.Y', $STARTTIME);
 	
 	echo '<div align=center><form name="studentform" id="studentform" method="post" action="___a2.php?action=change">
	 	 Course ID: &nbsp&nbsp'.
		 '<input type="text" name="cid" size="10" value="' . $searchtext. '" /><br>'.
		 'Time : &nbsp&nbsp'.
		 '<input type="text" name="stime" size="10" value="' . $datefrom. '" /><br>'.
      	 '<input name="search" id="search" type="submit" value="'.get_string('deleteattempts', 'block_dean').'">'.
		 '</form></div>';

	print_footer();
	


	
function delete_all_attempt_in_course($courseid) 
{
	global $COUNTATTEMPT, $STARTTIME;

	$strsql = "SELECT id, course, name FROM mdl_quiz where course = $courseid";
	// echo $strsql . '<br>';
	if ($quizes = get_records_sql ($strsql))	{
		$i=1;		
		foreach ($quizes as $quiz)	{
			echo $quiz->name . '<br>';
			delete_records_select('quiz_attempts', "timestart < $STARTTIME and quiz = {$quiz->id}");
	
		/*	$strsql = "SELECT id, quiz FROM mdl_quiz_attempts where timestart < $STARTTIME and quiz = {$quiz->id}";
			// echo $strsql . '<br>';
			
			if ($attempts = get_records_sql ($strsql))	{	
	
				// echo '$COUNTATTEMPT = ' . $COUNTATTEMPT . '<hr>';

				foreach ($attempts as $attempt)	{
					echo $i . '. ' . $attempt->id .'<br>';  
				    // delete_records('quiz_attempts', 'id', $attempt->id);
				    // delete_records("question_states", "attempt", $attempt->uniqueid);
				    // delete_records("question_sessions", "attemptid", $attempt->uniqueid);
				    // delete_records("question_attempts", "id", $attempt->uniqueid);
				    // delete_records('quiz_grades', 'quiz', $attempt->quiz);
				    $i++;
				}
			}
		*/	
		}		
		return true;
	} 
	return false;
}	 

?>


