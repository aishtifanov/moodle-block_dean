<?php // $Id: examdbq.php,v 1.2 2011/02/08 11:26:30 shtifanov Exp $

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

	$strtitle = get_string('examdbq','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examdbq';
    include('tabs.php');
	
	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_date("examdbq.php?uts=", $timestartday);
	echo '</table>';

    if ($timestartday != 0) {
    	
		$table = table_examreportattempts($timestartday);
  	   	print_table($table);

		echo  '<form name="allgroups" method="post" action="examdbq.php">';
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
    // 'multichoice',
    $qtypenames = array('truefalse', 'match', 'numerical', 'shortanswer', 'essay');
	
    $table->head  = array ('№', get_string('discipline','block_dean'), get_string ('vsego', 'block_dean'),
                                get_string('onetomany', 'block_dean'), get_string('manytomany', 'block_dean'));  
                             
    $table->align = array ("center", "left", "center", "center", "center");
	$table->width = '75%';
    $table->size = array ('5%', '20%', '10%', '10%', '10%');
    $table->columnwidth = array (4, 20, 8, 8, 8);
    
    foreach ($qtypenames as $qtypename) {
        $table->head[] = get_string($qtypename, 'quiz');
        $table->align[] = 'center';
        $table->size[] = '10%';
        $table->columnwidth[] = 8; 
    }
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examdbq','block_dean');
    $table->downloadfilename = "examdbq";
    $table->worksheetname = 'examdbq';
	
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
        
        $hasbeenschedule = array();
        foreach ($quizids as $idquiz)  {   
			$strfaculty = $strgroup = $strcourse = $strquiz = '-';  
			if ($quiz = get_record_select('quiz', "id = $idquiz", 'id, course, name'))	{
	 			if (in_array($quiz->course, $hasbeenschedule))	continue;
			    $hasbeenschedule[] = $quiz->course;
				$course = get_record_select ('course', "id = $quiz->course", 'id, fullname'); 
				$strcourse = $course->fullname; 
				$strquiz = $quiz->name;
                $tabledata = array (++$countrows, $strcourse);
                
                $ctx = get_context_instance(CONTEXT_COURSE, $course->id);
                
                // id, name, contextid, info, stamp, parent, sortorder
                if ($qcats = get_records_select ('question_categories', "contextid = $ctx->id", 'id')) {
                    $qcatsids = array();
                    foreach ($qcats as $qcat)   {
                        $qcatsids[] = $qcat->id;
                    }
                    $listqcatsids = implode (',', $qcatsids);
                    
                    // id, category, parent, name, questiontext, questiontextformat, image, generalfeedback, defaultgrade, penalty, qtype, length, stamp, version, hidden, timecreated, timemodified, createdby, modifiedby
                    if ($questions = get_records_select('question', "category in ($listqcatsids)", '', 'id, qtype')) {
                          $qtypecounts = array( 'truefalse' => 0, 'match' => 0, 'numerical' => 0, 'shortanswer' => 0, 'essay' => 0);
                           $countquestions = $multichoice1 = $multichoice2 = 0;                     
                           foreach ($questions  as $question)   {
                                if ($question->qtype == 'random') continue;
                                $countquestions++;                                
                                if ($question->qtype == 'multichoice')  {
                                    if ($whatmchoice = get_record_select ('question_multichoice', "question = $question->id", 'id, single'))    {
                                        if ($whatmchoice->single) $multichoice1++;
                                        else $multichoice2++;
                                    }    
                                    continue;
                                }
                                $qtypecounts["$question->qtype"]++;
                           } 
                           
                           $tabledata[] = $countquestions;
                           $tabledata[] = $multichoice1;
                           $tabledata[] = $multichoice2;
                           foreach ($qtypecounts as $qtypecount)    {
                                $tabledata[] = $qtypecount;  
                           } 
				           $table->data[] =  $tabledata;
                           unset($tabledata); 
                    }
                }
                
			} else {
				continue;
			}	
		}
	}		
	
	return $table;	
}	

?>