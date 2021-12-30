<?php // $Id: egereport.php,v 1.7 2013/09/03 14:01:08 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
    
    require_login();

    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
        
	$action = optional_param('action', '');
    $fid = optional_param('fid', 0, PARAM_INT);    // Faculty id
    $gid = optional_param('gid', 0, PARAM_INT);    // Group id
    $vid = optional_param('vid', 0, PARAM_INT);    // Variant view
    $startyear = optional_param('sy', 2016, PARAM_INT);    // Group id    

	$admin_is = isadmin();
	$creator_is = iscreator();
	$methodist_is = ismethodist();

    /*
    if ($USER->id == 59682) {
        $admin_is = true;
    } 
    */

    if (!$admin_is && !$creator_is && !$methodist_is) {
        // error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    if ($admin_is || $creator_is || !$methodist_is) {
    	if ($action == 'excel')	{
    		$table = table_egereport($fid, $gid, $startyear, $vid);
      		print_table_to_excel($table);
            exit();
    	}	
    }
    
	$strtitle = get_string('egereport', 'block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'egereport';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
    listbox_ege_not_ege("egereport.php?gid=$gid&fid=$fid&vid=", $vid);
	listbox_faculty("egereport.php?vid=$vid&gid=$gid&fid=", $fid);
    listbox_group_allfaculty("egereport.php?vid=$vid&fid=$fid&amp;gid=", $fid, $gid, false, $startyear);
	echo '</table>';

    if (($fid != 0 && $gid != 0) || $fid == -1 ) {
        
        // if ($vid == 1)  {
		$table = table_egereport($fid, $gid, $startyear, $vid);
  	   	print_table($table);

		echo  '<form name="allgroups" method="post" action="egereport.php">';
		echo  '<input type="hidden" name="fid" value="'.$fid.'" />';
		echo  '<input type="hidden" name="gid" value="'.$gid.'" />';
        echo  '<input type="hidden" name="vid" value="'.$vid.'" />';
		echo  '<input type="hidden" name="action" value="excel" />';
		echo  '<div align="center">';
		echo  '<input type="submit" name="mark" value="';
  		print_string('downloadexcel');
		echo '"></div></form>';
        
   }

   print_footer();


function table_egereport($fid, $gid, $startyear, $vid)	
{
	global $CFG;
    
    $ref_country = get_records_select_menu('bsu_ref_country', '', 'id', 'id, country');
    
    $facultet = get_record_select("dean_faculty", "id =$fid", "id, name, number");
	
    $table = new stdClass();
    if ($vid == 1)  {
        $egetable = 'bsu_ege';
        $table->head  = array ('Факультет', 'Группа',  get_string('fullname'), 'Гражданство', 'Основа обучения', 'ЕГЭ', 'Оценка', 'Вводное тестирование', 'Оценка (%)', 'Балл');
    	$table->align = array ('left', 'left', 'left', 'left', 'left', 'left', 'center', 'left', 'center', 'center');
        $table->size = array ('20%', '10%', '15%', '10%', '10%', '10%', '5%', '20%', '5%', '5%');
    	$table->columnwidth = array (45, 10, 35, 17, 17, 17, 9, 38, 11, 6);
    } else if ($vid == 2)  {
        $egetable = 'bsu_ege_exam';
        $table->head  = array ('Факультет', 'Группа',  get_string('fullname'), 'Гражданство', 'Основа обучения', 'Экзамен', 'Оценка', 'Вводное тестирование', 'Оценка (%)', 'Балл');
    	$table->align = array ('left', 'left', 'left', 'left', 'left', 'left', 'center', 'left', 'center', 'center');
        $table->size = array ('20%', '10%', '15%', '10%', '10%', '10%', '5%', '20%', '5%', '5%');
    	$table->columnwidth = array (45, 10, 35, 17, 17, 17, 9, 38, 11, 6);
    } else if ($vid == 3)  {
        $egetable = 'bsu_ege';
        $table->head  = array ('Факультет', 'Группа',  get_string('fullname'), 'Гражданство', 'Основа обучения', 
                                'ЕГЭ', 'Оценка ЕГЭ',  'Экзамен', 'Оценка ЭКЗ', 'Вводное тестирование', 'Оценка (%)', 'Балл');
    	$table->align = array ('left', 'left', 'left', 'left', 'left', 'left', 'center', 'left', 'center', 'left', 'center', 'center');
        $table->size = array ('20%', '10%', '15%', '10%', '10%', '10%', '5%', '10%', '5%', '20%', '5%', '5%');
    	$table->columnwidth = array (45, 10, 35, 17, 17, 17, 9, 17, 9, 38, 11, 6);
    }
    
    $table->head[] = get_string('startedon', 'quiz');
    $table->align[] = 'center';
    $table->size[] = '10%';
    $table->columnwidth[] = 15; 
    
    // $table->head[] = get_string('completedon','quiz');
    $table->head[] = get_string('startedon', 'quiz');
    $table->align[] = 'center';
    $table->size[] = '10%';
    $table->columnwidth[] = 15;
    
    $table->head[] = get_string('attemptduration', 'quiz');
    $table->align[] = 'center';
    $table->size[] = '10%';
    $table->columnwidth[] = 15;    

    $table->head[] = 'ip';
    $table->align[] = 'center';
    $table->size[] = '10%';
    $table->columnwidth[] = 15;    
    
    $table->head[] = 'чужой';
    $table->align[] = 'center';
    $table->size[] = '10%';
    $table->columnwidth[] = 15;    

    // $table->datatype = array ('char', 'char');
   	$table->width = '100%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();

    $strtimeformat = get_string('strftimedatetime');
    
    $eges = get_records ('bsu_ege_discipline');
    
    $coursenames = array();
    foreach ($eges as $ege) {
       if ($ege->courseid == 0)    {
            $coursenames[0] = '-';
       } else {
            $course  = get_record_select('course', "id = $ege->courseid", 'id, fullname');
            $coursenames[$ege->courseid] = $course->fullname;
       }       
    }   
    
    $agroupsids = array();

    if ($fid == -1) {
        $agroups = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		 						     WHERE startyear = $startyear
                                     ORDER BY name");
        foreach ($agroups as $agroup)   {
			$namegroup = trim($agroup->name);
			$len = strlen ($namegroup);
			$numgroup = substr($namegroup, -2);
			if ($len == 8 && $numgroup < 50)	{
					$agroupsids[] = $agroup->id;
			}
        }
        
        $table->titles[] = 'ВСЕ ИНСТИТУТЫ/ФАКУЛЬТЕТЫ';
        $table->titlesrows = array(30);
        $table->worksheetname = 'universitet';
        $table->downloadfilename = $table->worksheetname;
                                             
    } else if ($gid == -1) {
        $agroups = get_records_sql ("SELECT id, name  FROM {$CFG->prefix}dean_academygroups
		 						     WHERE facultyid=$fid AND startyear = $startyear
                                     ORDER BY name");
        foreach ($agroups as $agroup)   {
			$namegroup = trim($agroup->name);
			$len = strlen ($namegroup);
			$numgroup = substr($namegroup, -2);
			if ($len == 8 && $numgroup < 50)	{
					$agroupsids[] = $agroup->id;
			}
        }
        
        $table->titles[] = $facultet->name;
        $table->titlesrows = array(30);
        $table->worksheetname = 'facultet_'.$facultet->number . '_' . $vid;
        $table->downloadfilename = $table->worksheetname;
                                             
    } else {
        $agroupsids[] = $gid;
        $agroup = get_record_select ('dean_academygroups', "id = $gid", 'id, name');
        $table->titles[] = get_string('group') . ' ' . $agroup->name;
        $table->titlesrows = array(30);
        $table->worksheetname = $agroup->name;
        $table->downloadfilename = 'group_'.$agroup->name;
    } 
    
    foreach ($agroupsids as $agroupid)  {
        
        $agroup = get_record_select ('dean_academygroups', "id = $agroupid", 'id, name');
        
        $studentsql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.picture
                       FROM {$CFG->prefix}user u
                       LEFT JOIN {$CFG->prefix}dean_academygroups_members m ON m.userid = u.id
    				   WHERE m.academygroupid = $agroupid AND u.deleted = 0 AND u.confirmed = 1
    				   ORDER BY u.lastname, u.firstname";
    
        // print_r($studentsql); echo '<hr>';

        if($students = get_records_sql($studentsql)) {
        	
             foreach ($students as $student) {
                
                $fullname = "<div align=left><strong>".fullname($student)."</strong></div>"; // . ' (' . $agroup->name . ')'
                $picture = print_user_picture($student->id, 1, $student->picture, false, true);

                $sql = "SELECT CodePhysPerson, FormObutsTxt, kodcountry FROM mdl_bsu_students WHERE CodePhysPerson = '{$student->username}'";
                
                $namecountry = $formaobuts = '-';
                if ($st =  get_record_sql($sql))   {
                    $namecountry = $ref_country[$st->kodcountry];
                    $formaobuts =  $st->FormObutsTxt;
                    // print_object($st);
                }                          
                
                foreach ($eges as $ege) {
                    
                    $quiz = get_record_select('quiz', "id = $ege->quizid", 'id, course, name, sumgrades');
                    $strege = '';
                    $str2 = '-';                        

                    $strsql = "SELECT id, uniqueid, quiz, userid, attempt, sumgrades, timestart, timefinish, 
                                      timemodified, layout, ip,
                                       CASE WHEN timefinish = 0 THEN null WHEN timefinish > timestart 
                                       THEN timefinish - timestart 
                                       ELSE 0 END AS duration 
                               FROM {$CFG->prefix}quiz_attempts
            			       WHERE userid= $student->id and quiz = $ege->quizid";
                    // echo $strsql . '<br>';
                    
                    $rows = array();
                            
                	if ($qattempts = get_records_sql($strsql))  {
            	       //  print_object($qattempts);
                       $str2 = '';

                       $rows[1] = "<strong><a href=\"{$CFG->wwwroot}/course/view.php?id=$ege->courseid\">".$coursenames[$ege->courseid]."</a></strong>";
                       $rows[2] = $rows[3] = 0;
                       $rows[5] = $rows[6] = $rows[7] = $rows[8] = '-';
                       $rows[4] = $rows[9] = ''; 
    
                       foreach ($qattempts as $attempt)   {
                
                            $gradestr = ($attempt->sumgrades*100)/($quiz->sumgrades);      
                            $mark = $ball = 0;
                            if (is_numeric($gradestr)) {
                                $ball = round($gradestr);
                
                                if ($ball >= 5 && $ball < 20)		$mark = 1;                        
                                else if ($ball >= 20 && $ball < 50)	$mark = 2;
                                else if ($ball >= 50 && $ball < 75)	$mark = 3;	
                                else if ($ball >= 75 && $ball < 90)	$mark = 4;
                                else if ($ball >= 90)				$mark = 5;
                            }
                             
                            if ($ball > $rows[2])  {
                                $rows[2] = $ball; 
                                $rows[3] = $mark;
                                $startdate = userdate($attempt->timestart, '%d.%m.%Y'); // $strtimeformat
                                $rows[5] = $startdate;
                                if ($attempt->timefinish) {
                                    $timefinish = userdate($attempt->timefinish, '%H:%M'); // $strtimeformat
                                    $duration = format_time($attempt->duration);
                                    $rows[6] = $timefinish;
                                    $rows[7] = $duration;
                                } else {
                                    $rows[6] = '-';
                                    $rows[7] = get_string('unfinished', 'quiz');
                                }
                                $rows[8] = $attempt->ip;
                                $ips = explode('.', $attempt->ip);
                                if ($ips[0] != 172) {
                                    $rows[9] = 1; 
                                }
                                 
                            }       
                            
                        }
                        // print_object($rows); 
                    }  
                
                	if ($egeattempt = get_record_select($egetable, "CodePhysPerson = '{$student->username}' AND idNameDisc = $ege->idNameDisc"))  {
      	                 // $strege =  $ege->NameDisc . ': ' . $egeattempt->Ball;
                        $rows[0] = $egeattempt->Ball;
                    }
                    
                    if ($vid == 3)    {
                    	if ($examattempt = get_record_select('bsu_ege_exam', "CodePhysPerson = '{$student->username}' AND idNameDisc = $ege->idNameDisc"))  {
          	                 // $strege =  $ege->NameDisc . ': ' . $egeattempt->Ball;
                            $rows[4] = $examattempt->Ball;
                        }
                    }
                    
                    

                    if (!empty($rows))  {
                        if (!isset($rows[0]))   {
                            $rows[0] = '';
                            
                        } else if (!isset($rows[1]))    {
                            $rows[1] = $rows[2] = $rows[3] = '';
                        }
                        if ($vid == 3)  {
                            $egeNameDisc  = $ege->NameDisc;
                            $examNameDisc = $ege->NameDisc;
                            if (empty($rows[0]))    {
                                $egeNameDisc = '';
                            }
                            if (empty($rows[4]))    {
                                $examNameDisc = '';
                            }
                            $table->data[] = array ($facultet->name, $agroup->name, $fullname, $namecountry, $formaobuts, 
                                                    $egeNameDisc, 
                                                    $rows[0], $examNameDisc, 
                                                    $rows[4], $rows[1], $rows[2], $rows[3],
                                                    $rows[5], $rows[6], $rows[7], $rows[8], $rows[9]);
                        } else {
                            $table->data[] = array ($facultet->name, $agroup->name, $fullname, $namecountry, $formaobuts, 
                                                    $ege->NameDisc, 
                                                    $rows[0], $rows[1], $rows[2], $rows[3],
                                                    $rows[5], $rows[6], $rows[7], $rows[8], $rows[9]);
                        }    
                    }  else {
                        /*
                        if ($vid == 3)  {
                            $egeNameDisc  = $ege->NameDisc;
                            $examNameDisc = $ege->NameDisc;
                            $table->data[] = array ($facultet->name, $agroup->name, $fullname, $namecountry, $formaobuts, 
                                                    $egeNameDisc, '-', $examNameDisc, '-', '-', '-', '-');
                        } else {
                            $table->data[] = array ($facultet->name, $agroup->name, $fullname, $namecountry, $formaobuts, 
                                                    $ege->NameDisc,  '-', '-', '-', '-');
                        }    
                        */
                    }
                }
            }            
    
    	}
    }    
    
    return $table;
}


function listbox_ege_not_ege($scriptname, $vid)
{
  global $CFG;

  $menu = array();
  $menu[0] = 'Выберите вариант отображения ...';
  $menu[1] = 'Результаты с ЕГЭ';
  $menu[2] = 'Результаты без ЕГЭ';
  $menu[3] = 'Сводный отчет';

  echo '<tr> <td>Вариант отображения: </td><td>';
  popup_form($scriptname, $menu, 'switchvid', $vid, '', '', '', false);
  echo '</td></tr>';
  return 1;
}

?>