<?php // $Id: examcompare.php,v 1.1 2011/01/19 15:52:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
    
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
		$table = table_examcompare($timestartday, false);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('examcompare','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examcompare';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_date("examcompare.php?uts=", $timestartday);
	echo '</table>';

    if ($timestartday != 0) {
    	
		$table = table_examcompare($timestartday);
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



function table_examcompare($timestartday, $notify = true)	
{
	global $CFG;
    
    $stryes = get_string('yes');
    $strno = '<b><font color=red>' . get_string('no') . '</font></b>';
	
    $table->head  = array ('№', get_string('group'), $stryes. '/' . $strno, get_string('discipline','block_dean') . '/' . 
							get_string('course'),  get_string('modulename', 'quiz'),   
                            get_string ('action', 'block_dean'));
    $table->align = array ("center", "center", 'center', "left", "left", 'center', 'center', 'center', 'center', 'center', 'center');
	$table->width = '80%';
    $table->size = array ('3%', '5%', '3%', '30%', '25%', '5%');
    $table->columnwidth = array (4,  5, 8, 30, 20, 8, 8, 10, 8, 8, 10, 10);
	
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
    // id, department, codespeciality, namespeciality, namediscipline, rostrum, fio, groups, , countstudent, availabilitytestct, availabilitytestdo, author, auditorium, startexamination, note, presentability, fioverify, userid, timemodified
    $tgroups = array();
    $badgroups = array();
    $strsql = "dataexamination >= $timestartday AND dataexamination < $timeendday AND presentability = 1";
    // echo $strsql . '<br>'; 
    if ($timeexams = get_records_select ('test',  $strsql, 'dataexamination', 'id, groups, namediscipline'))	{
        foreach ($timeexams as $timeexam)   {
            // print_r($timeexam); echo '<hr>';
            if (is_agroup_day($timeexam->groups))   {
                $tgroups["$timeexam->groups"] = $timeexam->namediscipline;
            } else {
                $badgroups[] = $timeexam->groups;
             }   
        }    
    }    
    // print_r($tgroups); echo '<hr>';
    
	if ($schedules = get_records_select ('quiz_schedule',  "timeopen > $timestartday AND timeopen < $timeendday AND visib=1", 'timeopen', 'id, idquiz, timeopen'))	{
		// notify ('Found quiz in this day = '. count ($schedules), 'green');
		$hasbeenschedule = array();
        $quizes = array();
        $i = 0;
		foreach ($schedules as $schedule)	{
			if (in_array($schedule->idquiz, $hasbeenschedule))	continue;
			$hasbeenschedule[] = $schedule->idquiz;
			if ($quiz = get_record_select('quiz', "id = {$schedule->idquiz}", 'id, course, name'))	{
				$course = get_record ('course', 'id', $quiz->course);
                $quizes[$i]->courseid = $course->id;
                $quizes[$i]->id = $quiz->id;
                  
                $clearname = mb_ereg_replace('(ЦТ)', '', $course->fullname);
                $clearname = mb_ereg_replace('_дневное', '', $clearname); 
                $clearname = mb_ereg_replace('(дневное)', '', $clearname);
                
                $pos = strpos($clearname, '(');
                if ($pos === false) {
                    $pos = strpos($clearname, '_');
                    if ($pos === false) {
                    } else {
                        $names = explode ('_', $clearname); 
                    }    
                } else {
                    $names = explode ('(', $clearname);
                }    
                if (isset($names[0])) $clearname = $names[0]; 
				$quizes[$i]->coursefullname = trim($clearname);
                 
				// $quizes[$i]->coursefullname = trim($course->fullname);
                $quizes[$i]->quizname = $quiz->name;
                // print_r($quizes[$i]); echo '<hr>';
                $i++;
			} else {
				continue;
			}
        }
    }        	

    $badcourses = array();
    foreach ($quizes as $index => $quiz)  {
        $flag = false;
        foreach ($tgroups as $group => $namediscipline)   {
            //$pos = mb_strpos($quiz->coursefullname,  $namediscipline);
            // if ($pos === false) continue;
            // echo "! $group => $quiz->coursefullname '<br>";
            if ($quiz->coursefullname ==  $namediscipline)  {
                $quizes[$index]->group = $group;
                $quizes[$index]->namediscipline = $namediscipline;
                $flag = true;
            }     
        }
            
        if (!$flag) {
            $quizes[$index]->group = '?';
            $quizes[$index]->namediscipline = '?';
        }    
        
        // if (!$flag) $badcourses[] = $quiz->coursefullname;
    }
    
    // uasort – сортирует массив, используя пользовательскую функцию mySort
    uasort($quizes,"SortByDiscipline");

    
    $i = 1;
    foreach ($quizes as $index => $quiz)    {
        
        $stryesno = '?';
        if ($mgroup = get_record_select('groups', "courseid=$quiz->courseid and name='$quiz->group'", 'id, name'))   {
            $stryesno = $stryes;
            $strlinkupdate = "<a href=\"examprint.php?idc={$quiz->courseid}&amp;gid={$mgroup->id}\">".'<img src="'.$CFG->pixpath."/f/excel.gif\" height=16 width=16/>".'</a>';            
        }  else {
            $stryesno = $strno;
            $strlinkupdate = '';            
        }    
        $strdiscipline = $quiz->namediscipline . '/<br>';
        $strdiscipline .= "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id={$quiz->courseid}\">$quiz->coursefullname</a></strong>";
        
        if ($quiz->group != '?')    {
	        $ugroup = get_record_sql("SELECT * FROM {$CFG->prefix}dean_academygroups  WHERE name=$quiz->group");
            $strgroup = "<strong><a href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>";
        } else {
            $strgroup = $quiz->group;
            $strlinkupdate = '';
        }    
        

            
        $table->data[] = array ($i++.'.', $strgroup, $stryesno, $strdiscipline, $quiz->quizname, $strlinkupdate);  
    }   
    
    foreach ($badgroups as $badgroup)   {
        $table->data[] = array ($i++.'.', $badgroup, '?',  '?', '', '');  
    }   
        
    /*    
    foreach ($badcourses as $badcourse)   {
        $table->data[] = array ($i++.'.', '', '?', $badcourse,  '?', '', '');  
    } 
    */  
        
	return $table;	
}	


function is_agroup_day($namegroup)
{
    $ret = false;
        
    $len = strlen ($namegroup);
    $numgroup = substr($namegroup, -2);
    if ($len == 6 && $numgroup < 50) $ret = true;	
      
    return $ret;
}    


function SortByGroup($f1,$f2)
{
   if ($f1->group < $f2->group) return -1;
   else if($f1->group > $f2->group) return 1;
   else return 0;
}


function SortByDiscipline($f1,$f2)
{
   if ($f1->namediscipline < $f2->namediscipline) return -1;
   else if($f1->namediscipline > $f2->namediscipline) return 1;
   else return 0;
}


?>