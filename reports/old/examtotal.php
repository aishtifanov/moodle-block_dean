<?php // $Id: examtotal.php,v 1.3 2011/03/03 10:50:17 shtifanov Exp $

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
	
    $table->head  = array ('№', get_string('faculty','block_dean'),  "Все дисциплины",  
                            "Пегасовские дисциплины ", "Всего дисциплины", "Всего апробированные"); 
    $table->align = array ("center", "left", "left", "left", 'center', 'center');
	$table->width = '75%';
    $table->size = array ('5%', '20%', '20%', '20%', '10%', '10%');
    $table->columnwidth = array (4, 20, 30, 30, 8, 8);
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('examrptattempts','block_dean');
    $table->downloadfilename = "examrptattempts";
    $table->worksheetname = 'examrptattempts';
    
    $allcourses = array();
    if ($courses = get_records_sql ("SELECT id, course FROM mdl_quiz where id in (SELECT distinct idquiz FROM mdl_quiz_schedule)"))	{
        foreach ($courses as $course)   {
            $allcourses[] = $course->course;
        }            
    }    

    $facultys = get_records('dean_faculty', '', '', 'number');
    foreach ($facultys as $faculty) {
        $num = $faculty->number - 100;
        $strsql = "SELECT distinct namediscipline FROM mdl_test where department=$num order by namediscipline";
        $strdisc = '';
        if ($exams = get_records_sql ($strsql))	{
            foreach ($exams as $exam)   {
                $strdisc .= $exam->namediscipline . '; <br>';
            }
        }   

        
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
                    foreach ($courses as $course)   {
                            $straprob .= $course->fullname . '; <br>';
                    }
                }
            }        
        }
        
        $table->data[] = array ($num, $faculty->name, $strdisc, $straprob, count($exams), count($courses)); 
        // $table->data[] = array ($num, $faculty->name, '', '', count($exams), count($courses));
        
    }
	return $table;	
}	

?>