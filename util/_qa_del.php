<?php
	require_once("config.php");

	$corseid = optional_param('cid', '0', PARAM_INT); // course id

	$admin_is = isadmin();
	if (!$admin_is) {
		error(get_string('adminaccess'));		
	}
		


 	$searchtext = (string)$corseid;
 	
 	echo '<div align=center><form name="studentform" id="studentform" method="post" action="img_synchr.php?action=change">Course ID: &nbsp&nbsp'.
		 '<input type="text" name="s" size="10" value="' . $searchtext. '" />'.
      	 '<input name="search" id="search" type="submit" value="'.get_string('synchroniz', 'block_dean').'">'.
		 '</form></div>';


	if($cid == 0 && $qid == 0) {
		error(get_string('not course id AND quiz id'));		
		exit();
	}
	if($cid != 0 && $qid == 0) {
		$courses = get_records('quiz', 'course', $cid);
		foreach($courses as $course) {
			$itemid = get_record('grade_items', 'courseid', $course->course, 'iteminstance', $course->id);
			$itemid = $itemid->id;

			obman($course->id);
		}
	} else if($qid != 0) {
		$cid = get_record('quiz', 'id', $qid);
		
		$cid = $cid->course;
		$itemid = get_record('grade_items', 'courseid', $cid, 'iteminstance', $qid);

//print 'fdfdf';		
//print_r($itemid);		
		
		$itemid = $itemid->id;
		obman($qid);
	}
		
//print "<center>complete</center>";

function obman($qid) {
	GLOBAL $itemid, $uid, $aid, $obmanprocs;

	$data = get_record('quiz', 'id', $qid);
	$sumgrades = $data->sumgrades;
	$grade = $data->grade;
	$questions = $data->questions;
	$questions = explode(',', $questions);
	$countquestion = 0; 
	for($i=0;$i<=count($questions)-1;$i++) {
		if($questions[$i] != 0) {
			$countquestion++;
		}
	}

	if($obmanprocs != 0) {
		$rnd = rand(1, $obmanprocs) / 100;
	} else {
		$rnd = 0;
	}
	$grade = $grade - $grade*$rnd;
	$sumgrades = $sumgrades - $sumgrades*$rnd;  		

	$data = get_record('quiz_attempts', 'userid', $uid, 'quiz', $qid, 'attempt', $aid);
	unset($update);		
	$update->id = $data->id;
	$update->sumgrades = $sumgrades;
	$rnd = rand(1, 500);
	$rnd = $data->timestart + 1000 + $rnd;  		
	$update->timefinish = $rnd;
	update_record('quiz_attempts', $update);

	$data = get_record('quiz_grades', 'userid', $uid, 'quiz', $qid);
	unset($update);
	$update->id = $data->id;
	$update->grade = $grade;		
	update_record('quiz_grades', $update);
	
	$data = get_record('grade_grades', 'itemid', $itemid, 'userid', $uid);
	unset($update);
	$update->id = $data->id;
	$update->finalgrade = $grade;		
	update_record('grade_grades', $update);	 	
}		
?>