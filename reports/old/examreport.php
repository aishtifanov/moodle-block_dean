<?php // $Id: examreport.php,v 1.6 2011/02/08 11:26:30 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
    
    require_login();
    
	$action = optional_param('action', '');
    $timestartday = optional_param('uts', 0, PARAM_INT);    // Unix time stamp id

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
		$table = table_examreport($timestartday, false);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('examreport','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examreport';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_date("examreport.php?uts=", $timestartday);
	echo '</table>';

    if ($timestartday != 0) {
    	
		$table = table_examreport($timestartday);
  	   	print_table($table);

		echo  '<form name="allgroups" method="post" action="examreport.php">';
		echo  '<input type="hidden" name="uts" value="'.$timestartday.'" />';
		echo  '<input type="hidden" name="action" value="excel" />';
		echo  '<div align="center">';
		echo  '<input type="submit" name="mark" value="';
  		print_string('downloadexcel');
		echo '"></div></form>';
   }

   print_footer();



function table_examreport($timestartday, $notify = true)	
{
	global $CFG;
	
    $table->head  = array ('№', get_string('ffaculty','block_dean'), get_string('course','block_dean'), get_string('group'), 
							get_string('discipline','block_dean'),  get_string('modulename', 'quiz'),  get_string('time', 'quiz'), 
							get_string ('n_6', 'block_dean'), get_string ('n_2', 'block_dean'), get_string ('n_3', 'block_dean'),
							get_string ('n_4', 'block_dean'), get_string ('n_5', 'block_dean'), get_string ('vsego', 'block_dean'));
    $table->align = array ("center", "left", "center", "center", "left", "left", 'center', 'center', 'center', 'center', 'center', 'center', 'center');
	$table->width = '95%';
    $table->size = array ('5%', '20%', '5%', '5%', '20%', '20%', '5%', '5%');
    $table->columnwidth = array (4, 20, 5, 8, 30, 20, 8, 8, 10, 8, 8, 10, 10);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examreport','block_dean'). ' за ' . date("d.m.Y", $timestartday);
    $table->downloadfilename = "examreport_".date("d_m_Y", $timestartday);
    $table->worksheetname = 'examreport';
	
	$countrows = 0;
	if ($timestartday == 1)	{
		$timeendday = time();
	} else {	
		$timeendday = $timestartday +DAYSECS;
	}	
 
	if ($schedules = get_records_select ('quiz_schedule',  "timeopen > $timestartday AND timeopen < $timeendday", 'timeopen', 'id, idquiz, timeopen'))	{
		// notify ('Found quiz in this day = '. count ($schedules), 'green');
		$hasbeenschedule = array();
		foreach ($schedules as $schedule)	{
			if (in_array($schedule->idquiz, $hasbeenschedule))	continue;
			$hasbeenschedule[] = $schedule->idquiz;
			$strfaculty = $strgroup = $strcourse = $strquiz = '-';  
			if ($quiz = get_record_select('quiz', "id = {$schedule->idquiz}", 'id, course, name'))	{
				$course = get_record_select ('course', "id = $quiz->course", 'id, fullname'); 
				$strcourse = $course->fullname; 
				$strquiz = $quiz->name;
				if ($timestartday == 1)	{ 
					$strtime = date("d.m.y H:i", $schedule->timeopen); 
				} else {
					$strtime = date("H:i", $schedule->timeopen);
				}	
			} else {
				continue;
			}	
			
			$start->year = date('Y', $schedule->timeopen);
			$start->month = date('m', $schedule->timeopen);
			$start->day = date('d', $schedule->timeopen);
			$startime = make_timestamp($start->year, $start->month, $start->day);
			$starend = make_timestamp($start->year, $start->month, $start->day+1);
			
			if ($attempts = get_records_select ('quiz_attempts',  "quiz = {$schedule->idquiz} AND timestart > $startime AND timestart < $starend", 'timestart', 'id, quiz, userid')) {
				//print_r($attempts); echo '<hr>';
				$userids = array();
				foreach ($attempts as $attempt)	{
					$userids[] = $attempt->userid;
				}
				$listuserids = implode(',', $userids);
				if ($groupsmembers = get_records_select('dean_academygroups_members', "userid in ($listuserids)", '', 'id, academygroupid'))	{
					//print_r($groupsmembers); echo '<hr>';
					$groupsids = array();
					foreach ($groupsmembers as $groupsmember)	{
						// echo $groupsmember->groupid . '<br>';
						$groupsids[] = $groupsmember->academygroupid;
					}	
					
					$agroupsids = array_unique ($groupsids);
					// print_r($agroupsids); echo '<hr>';
					foreach ($agroupsids as $agroupsid)	{
						// id, facultyid, specialityid, curriculumid, name, startyear, term, description, timemodified
						if ($agroup = get_record_select ('dean_academygroups', "id = $agroupsid", 'id, facultyid, name'))	{
							
							$namegroup = trim($agroup->name);
							$len = strlen ($namegroup);
							$numgroup = substr($namegroup, -2);
							if ($len == 8 && $numgroup < 50 && is_numeric($namegroup))	{
								$strgroup = $agroup->name;
								$faculty = get_record_select ('dean_faculty', "id = $agroup->facultyid", 'id, name');
								$strfaculty = $faculty->name;
								
								if ($mgroup = get_record_select('groups', "courseid=$course->id and name='$agroup->name'", 'id, name'))	{
									$groupid = $mgroup->id;
								} else {
									if ($notify)  notify ("Группа {$agroup->name} не найдена в дисциплине: {$course->fullname}");
									continue;
								}	

								$arrayids = array();
								if ($gradeitems = get_records_select ('grade_items', "courseid=$course->id and (itemname like '%экзам%' or itemname like '%итог%')", '', 'id, courseid, itemname, iteminstance')) {
									foreach ($gradeitems as $gradeitem)	{
										$arrayids[] = $gradeitem->id;
									}
								}		
								$itemids = implode(',', $arrayids);
						
								$export_feedback = 0;
								$updatedgradesonly = 0;
								$displaytype = 1;
								$decimalpoints = 2;
								
								// print all the exported data here
								$export = new grade_export_xls($course, $groupid, $itemids, $export_feedback, $updatedgradesonly, $displaytype, $decimalpoints);
								// print_r($export);
								$tablerow = $export->get_tablerow_grades();
								
								$enrolyear = '20' . substr($strgroup, 2, 2);
								$kurs = get_kurs($enrolyear);
								
								if (!$notify)	$strgroup = "'". $strgroup;
								
								if ($tablerow)	{
									$vsego = $tablerow[0] + $tablerow[2] + $tablerow[3] + $tablerow[4] + $tablerow[5];
									// $strmarks = implode('|', $tablerow); 
									if (!( ($tablerow[2] == 1 || $tablerow[2] == 0) &&  ($tablerow[3] == 0 || $tablerow[3] == 1) && 
											($tablerow[4] == 0 || $tablerow[4] == 1) && ($tablerow[5] == 0 || $tablerow[5] == 1)))	{
										$table->data[] = array (++$countrows, $strfaculty, $kurs, $strgroup, $strcourse, $strquiz, $strtime, 
																$tablerow[0], $tablerow[2], $tablerow[3], $tablerow[4], $tablerow[5], $vsego);
									}							
								} else {
									// if ($notify)  notify ("Оценки не найдены для группы {$agroup->name} в дисциплине: {$course->fullname}");
									// $table->data[] = array (++$countrows, $strfaculty, $kurs, $strgroup, $strcourse, $strquiz, $strtime, '-', '-', '-', '-', '-');
								}	
							}	 
						}	  
					}	
				}	
			}	
			
		}
	}		
	
	return $table;	
}	

?>