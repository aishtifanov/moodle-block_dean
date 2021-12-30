<?php // $Id: examreportvv.php,v 1.3 2013/09/03 14:01:08 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';

    $START_TESTING_TIME = make_timestamp(2011, 10, 03);
      
    require_login();
    
    $fid = optional_param('fid', 0, PARAM_INT);    // Faculty id
    $nid = optional_param('nid', 0, PARAM_INT);    // Number kurs 
	$action = optional_param('action', '');
    $uts = optional_param('uts', 0, PARAM_INT);    // Unix time stamp id

	$admin_is   = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();
    
    if ($USER->id == 59682) {
        $admin_is = true;
    } 

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	if (!$frm = data_submitted()) {
	    $frm = (object) $_GET;
	}

    $frm->uts = $uts;
    $frm->nid = 0;
    $frm->fid = 0;
    
	if (isset($frm->s_day) && $frm->s_day != 0 && $frm->s_month != 0 && $frm->s_year != 0) {
	    $frm->startdatereport = get_timestamp_from_date($frm->s_day, $frm->s_month, $frm->s_year);
	    $startdaterep = $frm->startdatereport;
	} else {
	    $startdaterep = 0;
	}

	if (isset($frm->e_day) && $frm->e_day != 0 && $frm->e_month != 0 && $frm->e_year != 0) {
	    $frm->enddatereport = get_timestamp_from_date($frm->e_day, $frm->e_month, $frm->e_year);
	    $enddaterep = $frm->enddatereport;
	} else {
	    $enddaterep = 0;
	}

	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
	    $table = table_examreport($frm, $startdaterep, $enddaterep, false);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('examreport','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examreportvv';
    include('tabs.php');

	// echo '<form name="choosetime" method="post" action="examreport.php">';
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	// listbox_faculty("examreport.php?nid=$nid&amp;fid=", $fid);
    // listbox_coursenumber("examreport.php?fid=$fid&amp;nid=", $nid);
   	// listbox_date("examreport.php?nid=$nid&amp;fid=$fid&amp;uts=", $timestartday);
    // combobox_faculty($fid);
    // combobox_coursenumber($nid);
    combobox_date($uts);
/*
	// echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    echo '<tr> <td>или</td><td></td></tr>';
	echo '<tr> <td>Временной период: с</td><td>';
	print_date_monitoring('s_day', 's_month', 's_year', $startdaterep, 1);
	echo ' по ';
	print_date_monitoring('e_day', 'e_month', 'e_year', $enddaterep, 1);
	echo '</td></tr>';
	echo '<tr><td align=center colspan=2>';
	echo '<input type="submit" name="setdate" value="' . get_string('go') . '">';
    */
	echo '</td></tr></form></table>';

    // print_r($frm);
    $flag = true;
    /*
    foreach ($frm as $field)    {
        if ($field != 0) $flag = false;    
    }
    */
    if ($uts != 0) {
        // notify('Заполните форму и нажмите кнопку "Применить".');    
   
		$table = table_examreport($frm, $startdaterep, $enddaterep);
  	   	print_table($table);

		echo  '<form name="allgroups" method="post" action="examreportvv.php">';
		echo  '<input type="hidden" name="fid" value="'.$frm->fid.'" />';
        echo  '<input type="hidden" name="nid" value="'.$frm->nid.'" />';
        echo  '<input type="hidden" name="uts" value="'.$frm->uts.'" />';
/*        
        echo  '<input type="hidden" name="s_day" value="'.$frm->s_day.'" />';
        echo  '<input type="hidden" name="s_month" value="'.$frm->s_month.'" />';
        echo  '<input type="hidden" name="s_year" value="'.$frm->s_year.'" />';
        echo  '<input type="hidden" name="e_day" value="'.$frm->e_day.'" />';
        echo  '<input type="hidden" name="e_month" value="'.$frm->e_month.'" />';
        echo  '<input type="hidden" name="e_year" value="'.$frm->e_year.'" />';
*/        
		echo  '<input type="hidden" name="action" value="excel" />';
		echo  '<div align="center">';
		echo  '<input type="submit" name="mark" value="';
  		print_string('downloadexcel');
		echo '"></div></form>';
        
   }

   print_footer();



function table_examreport($frm, $startdaterep, $enddaterep, $notify = true)	
{
	global $CFG, $START_TESTING_TIME;
	
    $table->head  = array ('№', get_string('ffaculty','block_dean'), get_string('sspeciality','block_dean'), get_string('group'), 
							get_string('discipline','block_dean'), // get_string('modulename', 'quiz'),  get_string('time', 'quiz'), 
							get_string ('n_6', 'block_dean'), get_string ('n_2', 'block_dean'), get_string ('n_3', 'block_dean'),
							get_string ('n_4', 'block_dean'), get_string ('n_5', 'block_dean'), get_string ('vsego', 'block_dean'));
    $table->align = array ("center", "left", "left", "center", "left", "left", 'center', 'center', 'center', 'center', 'center', 'center', 'center');
	$table->width = '95%';
    $table->size = array ('5%', '20%', '20%', '5%', '20%', '5%', '5%', '5%', '5%', '5%', '5%');
    $table->columnwidth = array (4, 20, 25, 8, 30, 10, 8, 8, 8, 8, 8);
	
    $timestartday = $frm->uts;
    $timeendday = 0;
    
	$countrows = 0;
	if ($timestartday == 1)	{
	    $timestartday = $START_TESTING_TIME;
		$timeendday = time();
	} else if ($timestartday > 0)	{	
		$timeendday = $timestartday +DAYSECS;
	}	
    
    if ($startdaterep >0 && $enddaterep > 0)    {
        $timestartday = $startdaterep;
        $timeendday = $enddaterep;         
    }
 
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examreport','block_dean'). ' c ' . date("d.m.Y", $timestartday) .  ' по ' . date("d.m.Y", $timeendday);
    $table->downloadfilename = "examreport_".date("d_m_Y", $timestartday);
    $table->worksheetname = 'examreport';
    
    $curryear = date('Y');
    $currmonth = date('n');
    
    $strsql = '';
    if ($timestartday != 0 && $timeendday != 0)     {
        $strsql = "timeopen > $timestartday AND timeopen < $timeendday";
    } else {
        $time1 =  $START_TESTING_TIME;
        $strsql = "timeopen >= $time1";
    }
    
    // echo $strsql;
     
    $persent = array(0, 0, 0, 0, 0, 0, 0);
    
    $testcoursesids = array();
   
    if ($courses = get_records_select('course', "category = 81" , 'id'))    {
        foreach ($courses as $course)   { 
            $testcoursesids[] = $course->id;
        }
    }
    
    $coursesids = implode (',', $testcoursesids);          
    
    $strsql = "SELECT id as idquiz, course FROM mdl_quiz where course in ($coursesids)";

	// if ($schedules = get_records_select ('quiz_schedule',  $strsql, 'timeopen', 'id, idquiz, timeopen'))	{
	if ($schedules = get_records_sql ($strsql))	{   
		// notify ('Found quiz in this day = '. count ($schedules), 'green');
		$hasbeenschedule = array();
		foreach ($schedules as $schedule)	{
			if ($quiz = get_record_select('quiz', "id = {$schedule->idquiz}", 'id, course, name'))	{
			     if (!in_array( $quiz->course, $testcoursesids))   continue;
    			 if (in_array($schedule->idquiz, $hasbeenschedule))	continue;
                 $hasbeenschedule[] = $schedule->idquiz;
			     $strfaculty = $strgroup = $strcourse = $strquiz = '-';  
				 $course = get_record_select ('course', "id = $quiz->course", 'id, fullname');
				$strcourse = $course->fullname; 
				$strquiz = $quiz->name;
				// $strtime = date("d.m.y H:i", $schedule->timeopen);
			} else {
				continue;
			}	
			
            /*
			$start->year = date('Y', $schedule->timeopen);
			$start->month = date('m', $schedule->timeopen);
			$start->day = date('d', $schedule->timeopen);
			$startime = make_timestamp($start->year, $start->month, $start->day);
			$starend = make_timestamp($start->year, $start->month, $start->day+1);
            */
			
			// if ($attempts = get_records_select ('quiz_attempts',  "quiz = {$schedule->idquiz} AND timestart > $startime AND timestart < $starend", 'timestart', 'id, quiz, userid')) {
		    if ($attempts = get_records_select ('quiz_attempts',  "quiz = {$schedule->idquiz} AND timestart > $timestartday AND timestart < $timeendday", 'timestart', 'id, quiz, userid')) {
			 
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
							    if ($frm->nid > 0)  {
                                    $numkurs = '20'.substr($namegroup, 2, 2);
                                    $numkurs =  $curryear - $numkurs; 
                                    if ($currmonth > 8) $numkurs++;
                                    if ($numkurs != $frm->nid) continue;
                                }     							 
                                
								$strgroup = $agroup->name;
								$faculty = get_record_select ('dean_faculty', "id = $agroup->facultyid", 'id, number, name');
                                
                                if ($frm->fid > 0)  {
                                    $number1 = 100 + $frm->fid;
                                    if ($number1 != $faculty->number) continue;  
                                }
                                
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
								
                                /*
								$enrolyear = '20' . substr($strgroup, 2, 2);
								$kurs = get_kurs($enrolyear);
                                */
                                $strsql = "SELECT DISTINCT grup, FakultetName, Specyal, Kvalif  FROM mdl_bsu_students 
                                           where grup = '{$agroup->name}'";
                                           
                                // echo $strsql . '<br>';            
                                           
                                $spec = get_record_sql ($strsql);
                                $kurs = $spec->Specyal . ' (' . $spec->Kvalif . ')';
                                $strquiz = ''; // !!!!!!!!!!!! 
								
								if (!$notify)	$strgroup = "'". $strgroup;
								
								if ($tablerow)	{
									$vsego = $tablerow[0] + $tablerow[2] + $tablerow[3] + $tablerow[4] + $tablerow[5];
									// $strmarks = implode('|', $tablerow); 
									if (!( ($tablerow[2] == 1 || $tablerow[2] == 0) &&  ($tablerow[3] == 0 || $tablerow[3] == 1) && 
											($tablerow[4] == 0 || $tablerow[4] == 1) && ($tablerow[5] == 0 || $tablerow[5] == 1)))	{
										$table->data[] = array (++$countrows, $strfaculty, $kurs, $strgroup, $strcourse, // $strquiz, $strtime, 
																$tablerow[0], $tablerow[2], $tablerow[3], $tablerow[4], $tablerow[5], $vsego);
                                                                
                                        $persent[0] += $tablerow[0];
                                        $persent[2] += $tablerow[2];
                                        $persent[3] += $tablerow[3];
                                        $persent[4] += $tablerow[4];
                                        $persent[5] += $tablerow[5];                         
                                        $persent[6] += $vsego;
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
    
    if ($persent[6] > 0)    {
        $table->data[] = array ('', 'Итого сумма', '', '', '',  $persent[0], $persent[2], $persent[3], $persent[4], $persent[5], $persent[6]);

        $proc = array(0, 0, 0, 0, 0, 0, 0);
        $proc[0] = number_format($persent[0]/$persent[6]*100, 2, ',', ''). '%';
        $proc[2] = number_format($persent[2]/$persent[6]*100, 2, ',', ''). '%';
        $proc[3] = number_format($persent[3]/$persent[6]*100, 2, ',', ''). '%';
        $proc[4] = number_format($persent[4]/$persent[6]*100, 2, ',', ''). '%';
        $proc[5] = number_format($persent[5]/$persent[6]*100, 2, ',', ''). '%';
        $table->data[] = array ('', 'Итого процент от всех', '', '', '',   $proc[0], $proc[2], $proc[3], $proc[4], $proc[5], '');        

        $persent[6] = $persent[6] - $persent[0];
        $proc[2] = number_format($persent[2]/$persent[6]*100, 2, ',', ''). '%';
        $proc[3] = number_format($persent[3]/$persent[6]*100, 2, ',', ''). '%';
        $proc[4] = number_format($persent[4]/$persent[6]*100, 2, ',', ''). '%';
        $proc[5] = number_format($persent[5]/$persent[6]*100, 2, ',', ''). '%';
        $table->data[] = array ('', 'Итого процент от явившихся', '', '', '', '', $proc[2], $proc[3], $proc[4], $proc[5], $persent[6]);        

    }    
		
	
	return $table;	
}	


function combobox_faculty($fid)
{
  global $CFG;

  $facultymenu = array();
  $facultymenu[0] = get_string('selectafaculty', 'block_dean').'...';

   // id, number, name, deanid, deanphone1, deanphone2, deanaddress, timemodified, shortname, zamdeanid, zzdeanid, idfakultet
  if($allfacs = get_records_sql("SELECT id, number, name FROM {$CFG->prefix}dean_faculty ORDER BY number"))   {
		foreach ($allfacs as $facultyI) 	{
            $n = $facultyI->number - 100;
			$facultymenu[$facultyI->id] = $n. '. ' . $facultyI->name;
		}
  }

  echo '<tr> <td>'.get_string('ffaculty', 'block_dean').': </td><td>';
  choose_from_menu($facultymenu, 'fid', $fid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

// Display list parallel number as popup_form
function combobox_coursenumber($nid)
{
  global $CFG;

  $strtitle = 'Выберите курс ...';
  $groupmenu = array();
  $parmenu[0] = $strtitle;
  for ($i = 1; $i <= 6; $i++) {
	  	$parmenu[$i] = $i;
  }

  echo '<tr><td>Курс: </td><td>';
  choose_from_menu($parmenu, 'nid', $nid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function combobox_date($uts)
{
  global $CFG, $START_TESTING_TIME;

  $utsmenu = array();
  $utsmenu[0] = get_string('selectadate', 'block_dean') . '...';
  $utsmenu[1] = get_string('allperiod', 'block_dean');

  $time1 = $START_TESTING_TIME; 

  for ($i=0; $i<=95; $i++)	{
	$utsmenu[$time1] = date("d.m.Y", $time1);
	$time1 += DAYSECS;
  }

  echo '<tr> <td>'.get_string('dateofgive', 'block_dean').': </td><td>';
  // choose_from_menu($utsmenu, 'uts', $uts, '', '', '', false);
  popup_form('examreportvv.php?uts=', $utsmenu, 'uts', $uts, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

?>