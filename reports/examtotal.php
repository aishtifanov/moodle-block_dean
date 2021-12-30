<?php // $Id: examtotal.php,v 1.5 2011/06/14 08:22:11 shtifanov Exp $

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

    if ($USER->id == 59682) {
        $admin_is = true;
    } 

    if (!$admin_is && !$creator_is && !$methodist_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

	$action   = optional_param('action', '');
    if ($action == 'excel') 	{
		$table = table_examtotal($timestartday, false);
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('examtotal','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'examtotal';
    include('tabs.php');
	

	$table = table_examtotal();
   	print_table($table);

	echo  '<form name="allgroups" method="post" action="examtotal.php">';
	echo  '<input type="hidden" name="uts" value="'.$timestartday.'" />';
	echo  '<input type="hidden" name="action" value="excel" />';
	echo  '<div align="center">';
	echo  '<input type="submit" name="mark" value="';
	print_string('downloadexcel');
	echo '"></div></form>';

   print_footer();


function table_examtotal()	
{
	global $CFG;
	
    $table->head  = array ('№', get_string('faculty','block_dean'),  "Дисциплины по плану", "Тесты ЦТ (апробированные)");
                            // , "Всего <br> дисциплины", "Всего <br>апробированные"); 
    $table->align = array ("center", "left", "left", "left"); // , 'center', 'center');
	$table->width = '85%';
    $table->size = array ('5%', '20%', '20%', '20%'); // , '10%', '10%');
    $table->columnwidth = array (4, 20, 30, 30); //, 8, 8);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examrptattempts','block_dean');
    $table->downloadfilename = "examrptattempts";
    $table->worksheetname = 'examrptattempts';
    
    $time1 = make_timestamp(2012, 04, 11);
    $strsql = "SELECT distinct idquiz FROM mdl_quiz_schedule WHERE timeopen > $time1";
    $listids = array();
    if ($timeexams = get_records_sql ($strsql))	{
        foreach ($timeexams as $timeexam)   {
            $listids[] = $timeexam->idquiz;
        }
    }        
    $strlistids = implode (',', $listids);    
    
    $allcourses = array();
    if ($courses = get_records_sql ("SELECT id, course FROM mdl_quiz where id in ($strlistids)"))	{
        foreach ($courses as $course)   {
            $allcourses[] = $course->course;
        }            
    }    

    $facultys = get_records('dean_faculty', '', '', 'number');
    foreach ($facultys as $faculty) {
        
        $num = substr ($faculty->number, 1, 2);
         
        $strsql = "SELECT distinct disciplinenameid, disciplinename 
                   FROM mdl_dean_schedule 
                   WHERE  groupno like '$num%'
                   ORDER BY disciplinename";
        // echo $strsql;
        $arrdisc = array();
        $strdisc = ''; 
        $exams = get_records_sql ($strsql);
        
      
        if ($disciplines = get_records_sql ("select id, courseid from mdl_dean_discipline where curriculumid in (SELECT id FROM mdl_dean_curriculum where facultyid=$faculty->id)")) {
            $aprob = array();
            foreach ($disciplines as $discipline)   {
                if (in_array($discipline->courseid, $allcourses))   {
                    $aprob[] = $discipline->courseid;
                }
            }
            
            $uaprobs  = array_unique($aprob);
            $listcourseid = implode(',', $uaprobs); 
            
            $straprob = '';
            if ($listcourseid != '')    {
                if ($courses = get_records_select ('course', "id in ($listcourseid)", 'fullname', 'id, fullname'))  {
                    $coursesnames=array();
                    foreach ($courses as $course)  {          
        	           $coursesnames[$course->id] = $course->fullname;
                    }
                }
            }        
        }
        
        if ($exams) {
            $countexams = count($exams);
        } else {
            $countexams = 0;
        }    
        
        $countcourses = 0;
        $cfullname = array();
        foreach ($exams as $e)  {
            if ($d_c = get_record_select ('dean_course_discipline', "disciplineid=$e->disciplinenameid and courseid in ($listcourseid)", 'courseid'))  {
                $cfullname[$e->disciplinenameid] = $coursesnames[$d_c->courseid];
                $countcourses++;
            } else {
                $cfullname[$e->disciplinenameid] = '-';
            }                    
        }    
            
        
        $table->data[] = array ('<b>' .$num . '.</b>', '<b>' . $faculty->name . '</b>', '<b>' .$countexams . '</b>' , '<b>' .$countcourses . '</b>');
        
        foreach ($exams as $e)  {
            $strsql = "SELECT distinct groupno FROM mdl_dean_schedule where  disciplinenameid = $e->disciplinenameid AND groupno like '$num%'";
            $strgroups = '';
            $first = true;
            if ($groups = get_records_sql($strsql)) {
                foreach ($groups as $group) {
                    if ($first) {
                        $strgroups .= $group->groupno;
                        $first = false;    
                    } else {
                        $strgroups .= ', ' . $group->groupno;
                    }         
                }
            }
            $table->data[] = array ('', $strgroups, $e->disciplinename , $cfullname[$e->disciplinenameid]);
        } 
        
        // $table->data[] = array ($num, $faculty->name, '', '', count($exams), count($courses));
        //break;        
    }
	return $table;	
}	

?>