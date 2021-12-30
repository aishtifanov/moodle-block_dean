<?php // $Id: examrptattempts.php,v 1.8 2011/02/11 12:11:08 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';

    require_login();
    
    define('_5MINSECS', 300);
    define('_10MINSECS', 600);
        
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
		$table = table_examreportattempts($timestartday, false);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('examrptattempts','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examrptattempts';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_date("examrptattempts.php?uts=", $timestartday);
	echo '</table>';

    if ($timestartday != 0) {
    	
		$table = table_examreportattempts($timestartday);
  	   	print_table($table);

		echo  '<form name="allgroups" method="post" action="examrptattempts.php">';
		echo  '<input type="hidden" name="uts" value="'.$timestartday.'" />';
		echo  '<input type="hidden" name="action" value="excel" />';
		echo  '<div align="center">';
		echo  '<input type="submit" name="mark" value="';
  		print_string('downloadexcel');
		echo '"></div></form>';
   }

   print_footer();



function table_examreportattempts($timestartday, $notify = true)	
{
	global $CFG;
	
    $table->head  = array ('№', get_string('discipline','block_dean'),  get_string('modulename', 'quiz'),  
                            get_string ('attemptsallowed', 'quiz'), "Меньше 5 мин.", "От 5 до 10 мин.", "Больше 10 мин.",
                            "Среднее время", "Средний балл"); 
    $table->align = array ("center", "left", "center", "center", 'center', 'center', 'center', 'center', 'center');
	$table->width = '75%';
    $table->size = array ('5%', '20%', '20%', '10%', '10%', '10%', '10%', '10%', '10%');
    $table->columnwidth = array (4, 20, 30, 8, 8, 8, 8, 8, 8);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examrptattempts','block_dean');
    $table->downloadfilename = "examrptattempts";
    $table->worksheetname = 'examrptattempts';
	
	$countrows = 0;
	if ($timestartday == 1)	{
		$timeendday = time();
	} else {	
		$timeendday = $timestartday +DAYSECS;
	}	
    
	if ($schedules = get_records_select ('quiz_schedule',  "timeopen > $timestartday AND timeopen < $timeendday and visib=1", 'timeopen', 'id, idquiz, timeopen'))	{
		// notify ('Found quiz in this day = '. count ($schedules), 'green');
		$hasbeenschedule = array();
		foreach ($schedules as $schedule)	{
			// if (in_array($schedule->idquiz, $hasbeenschedule))	continue;
			$hasbeenschedule[] = $schedule->idquiz;
        }
        $quizids = array_unique($hasbeenschedule);
        
        foreach ($quizids as $idquiz)  {   
			$strfaculty = $strgroup = $strcourse = $strquiz = '-';  
			if ($quiz = get_record_select('quiz', "id = $idquiz", 'id, course, name, sumgrades'))	{
				$course = get_record_select ('course', "id = $quiz->course", 'id, fullname'); 
				$strcourse = $course->fullname; 
				$strquiz = $quiz->name;
			} else {
				continue;
			}	
			
			$start->year = 2010; // date('Y');
			$start->month = 11; // date('m');
			$start->day = 31; // date('d');
			$end->year = date('Y');
			$end->month = date('m');
			$end->day = date('d');
			$startime = make_timestamp($start->year, $start->month, $start->day);
			$starend = make_timestamp($end->year, $end->month, $end->day);
			
            // id, uniqueid, quiz, userid, attempt, sumgrades, timestart, timefinish, timemodified, layout, preview
			if ($attempts = get_records_select ('quiz_attempts',  "quiz = $idquiz AND timestart > $startime AND timestart < $starend", 'timestart', 'id, quiz, userid, sumgrades, timestart, timefinish')) {
				//print_r($attempts); echo '<hr>';
                $vsego = $time5 = $time10 = $time11 = $summavremeni = $allmarks = 0;
				foreach ($attempts as $attempt)	{
				    if ($attempt->timefinish == 0 || $attempt->sumgrades == 0) continue; 

			        $vsego++;
			        $attime = $attempt->timefinish - $attempt->timestart;
                    $summavremeni += $attime; 
                    if ($attime <= _5MINSECS) {
                        $time5++;
                    } else if ($attime > _5MINSECS &&  $attime  <= _10MINSECS) {
                        $time10++;
                    }  else {
                        $time11++;
                    }   
                    
                    $ball = ($attempt->sumgrades*100)/$quiz->sumgrades;
                    $mark = 0;
                    if ($ball <= 39)						$mark = 2;
                    else if ($ball >= 40 && $ball <= 59)	$mark = 3;	
                    else if ($ball >= 60 && $ball <= 79)	$mark = 4;
                    else if ($ball >= 80 )					$mark = 5;
                    
                    $allmarks += $mark;
				}
                
                
                if ($vsego > 0) {
                    $avgtime = round(($summavremeni/$vsego)/60);
                    $avgmark = number_format($allmarks/$vsego, 2, ',', ''); 
                } else {
                    $avgmark = $avgtime = '-';
                }    
				$table->data[] = array (++$countrows, $strcourse, $strquiz, $vsego, $time5, $time10, $time11, $avgtime, $avgmark);
            }     
			
		}
	}		
	
	return $table;	
}	

?>