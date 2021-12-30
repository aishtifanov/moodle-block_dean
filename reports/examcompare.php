<?php // $Id: examcompare.php,v 1.8 2013/09/03 14:01:08 shtifanov Exp $

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

    if ($USER->id == 59682) {
        $admin_is = true;
    } 

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
							get_string('course'),  get_string ('action', 'block_dean'));
    $table->align = array ("center", "center", 'center', "left", "left", 'center', 'center', 'center', 'center', 'center', 'center');
	$table->width = '60%';
    $table->size = array ('3%', '5%', '3%', '20%', '5%');
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
    $strtimestartday = date ('Y-m-d', $timestartday); 
    $strtimeendday = date ('Y-m-d', $timeendday); 	
    
    // Id, GroupNo, DisciplineNameId, SubDepartmentId, EdWorkId, SpecialityId, DisciplineName, SubDepartment, Speciality, AuditoryId, Aud, KorpName, StaffFormId, DateTimeStart, DateTimeFinish, CustomText, IsExamTesting
    
    $tgroups = array();
    $badgroups = array();
    $strsql = "DateTimeStart >= '$strtimestartday' AND DateTimeFinish < '$strtimeendday'"; //  AND presentability = 1";
    // echo $strsql . '<br>'; 
    if ($timeexams = get_records_select ('dean_schedule',  $strsql, 'DateTimeStart', 'GroupNo, DisciplineNameId, DisciplineName'))	{
        foreach ($timeexams as $timeexam)   {
            // print_r($timeexam); echo '<hr>';
            $namegroup = checkname($timeexam->GroupNo);
            if (!empty($namegroup)) {
                $tgroups["$namegroup"]->name = $timeexam->DisciplineName;
                $tgroups["$namegroup"]->idname = $timeexam->DisciplineNameId;
            } else {
                $badgroups[] = '?';
            }   
        }    
    }    
    
    // echo '<pre>';  print_r($tgroups); echo '</pre>';
      
    $coursesids0 = array();
	if ($schedules = get_records_select ('quiz_schedule',  "timeopen > $timestartday AND timeopen < $timeendday AND visib=1", 'timeopen', 'id, idquiz, timeopen'))	{
		foreach ($schedules as $schedule)	{
		    if ($quiz = get_record_select('quiz', "id = {$schedule->idquiz}", 'id, course, name'))	{
		        $coursesids0[] = $quiz->course;
            }
        }
    } 
    $coursesids = array_unique($coursesids0);
    $listcrsids = implode(',' , $coursesids); 
    
       
    $coursesnames=array();
    if ($courses = get_records_select ('course', "id in ($listcrsids)", 'id', 'id, fullname'))  {
        foreach ($courses as $course)  {          
        	$coursesnames[$course->id] = $course->fullname;
        }
    }    
    
    // echo '<pre>'; print_r($coursesnames); echo '</pre>';  
      
    $quizes = array();
    $index = 0;
    foreach ($tgroups as $group => $namediscipline)   {
        
        $flag = false;
        $quizes[$index]->group = $group;
        $quizes[$index]->namediscipline = $namediscipline->name;
        $quizes[$index]->iddiscipline = $namediscipline->idname;
        
        if ($d_c = get_record_select ('dean_course_discipline', "disciplineid=$namediscipline->idname and courseid in ($listcrsids)", 'courseid'))  {
            $quizes[$index]->courseid = $d_c->courseid;
            $quizes[$index]->coursefullname = $coursesnames[$d_c->courseid];
        } else {
            $quizes[$index]->courseid = 0;
            $quizes[$index]->coursefullname = '?';
            
        }
            
        $index++;   
    }        	

    $tabledata = array();
    $i = 0;
    foreach ($quizes as $index => $quiz)    {
        
        $stryesno = '?';
        $strlinkupdate = '';
        if ($quiz->courseid > 0)    {
            if ($mgroup = get_record_select('groups', "courseid=$quiz->courseid and name='$quiz->group'", 'id, name'))   {
                $stryesno = $stryes;
                $strlinkupdate = "<a href=\"examprint.php?idc={$quiz->courseid}&amp;gid={$mgroup->id}\">".'<img src="'.$CFG->pixpath."/f/excel.gif\" height=16 width=16/>".'</a>';            
            }  else {
                $stryesno = $strno;
            }    
            $strdiscipline = $quiz->namediscipline . '/<br>';
            $strdiscipline .= "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id={$quiz->courseid}\">$quiz->coursefullname</a></strong>";
        } else {
            $strdiscipline = $quiz->namediscipline . '/<br>';
            $strdiscipline .= "?";
            
        }    
        
        if ($quiz->group != '?')    {
	        $ugroup = get_record_sql("SELECT * FROM {$CFG->prefix}dean_academygroups  WHERE name='$quiz->group'");
            $strgroup = "<strong><a href=\"{$CFG->wwwroot}/blocks/dean/gruppa/lstgroupmember.php?mode=4&amp;fid={$ugroup->facultyid}&amp;sid={$ugroup->specialityid}&amp;cid={$ugroup->curriculumid}&amp;gid={$ugroup->id}\">$ugroup->name</a></strong>";
        } else {
            $strgroup = $quiz->group;
        }    
            
        // $table->data[] = array ($i++.'.', $strgroup, $stryesno, $strdiscipline, $strlinkupdate);
        $tabledata[$i]->courseid = $quiz->courseid;
        $tabledata[$i]->group = $strgroup;
        $tabledata[$i]->yesno = $stryesno;
        $tabledata[$i]->namediscipline = $quiz->namediscipline;
        $tabledata[$i]->coursefullname = $quiz->coursefullname;
        $tabledata[$i]->linkupdate = $strlinkupdate;
        $i++;
    }   
    
    foreach ($badgroups as $badgroup)   {
        // $table->data[] = array ($i++.'.', $badgroup, '?',  '?', '');
        $tabledata[$i]->courseid = 0;
        $tabledata[$i]->group = $badgroup;
        $tabledata[$i]->yesno = '?';
        $tabledata[$i]->namediscipline = '?';
        $tabledata[$i]->coursefullname = '?';
        $tabledata[$i]->linkupdate = '';
        $i++;
    }   
        
  /*      
    foreach ($coursesnames as $courseid => $coursesname) { 
        $flag = false; 
        foreach ($tgroups as $group => $namediscipline)   {
            $pos = mb_strpos($coursesname ,  $namediscipline);
            if ($pos === false) continue;
            $flag = true;
            break;
        }  
        if (!$flag) {
            $strdiscipline = '?/<br>';
            $strdiscipline .= "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id=$courseid\">$coursesname</a></strong>";
            // $table->data[] = array ($i++.'.', '?',  '?', $strdiscipline, '');
            $tabledata[$i]->courseid = $courseid;
            $tabledata[$i]->group = '?';
            $tabledata[$i]->yesno = '?';
            $tabledata[$i]->namediscipline = $coursesname;
            $tabledata[$i]->coursefullname = $coursesname;
            $tabledata[$i]->linkupdate = '-';
            $i++;
        }    
    }
 */   
    // uasort – сортирует массив, используя пользовательскую функцию mySort
    uasort($tabledata,"SortByDiscipline");
    
    $j = 1;
    foreach ($tabledata as $key => $td) {
        
        if ($td->linkupdate == '-') {
            $td->linkupdate = '';
            $td->namediscipline = '?';
        }
        
        $strdiscipline = $td->namediscipline . ' /<br>';
        if ($td->coursefullname != '?') {
            $strdiscipline .= "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id={$td->courseid}\">$td->coursefullname</a></strong>";
        } else {
            $strdiscipline .= "?";
        }    
            
        $table->data[] = array ( $j++.'.', $td->group, $td->yesno, $strdiscipline, $td->linkupdate);
    }    
    /*    
    foreach ($badcourses as $badcourse)   {
        $table->data[] = array ($i++.'.', '', '?', $badcourse,  '?', '', '');  
    } 
    */  
        
	return $table;	
}	


function checkname($namegroup, $firstname='')
{
    $ret = trim($namegroup);
    $len = strlen ($ret);
    if ($len >= 7) {
        // echo $namegroup . '<br>';
        $lastsym = mb_substr ($namegroup, -1);
        if ($lastsym == 'A' || $lastsym == 'a' || $lastsym == 'А' || $lastsym == 'а' || $lastsym == 'М' || $lastsym == 'м') return '';      
        $lastsym = mb_substr ($namegroup, -2);
        if ($lastsym == 'A' || $lastsym == 'a' || $lastsym == 'А' || $lastsym == 'а' || $lastsym == 'М' || $lastsym == 'м') return '';      
        return substr($ret, 0, 8);
    }    
    if ($len == 8) return $ret; 
    if ($len == 7) return '0'.$ret;
    
    if ($len == 2 || $len == 3) {
        $fgroup = trim($firstname);
        $len2 = strlen ($fgroup);
        if ($len2 == 5) $fgroup = '0'.$fgroup;
        $strgroup = substr($fgroup, 0, $len2 - $len);
        return $strgroup . $namegroup; 
    }    

    return '';
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