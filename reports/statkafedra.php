<?php // $Id: examtotal.php,v 1.5 2011/06/14 08:22:11 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';

    require_login();
    
    define('_5MINSECS', 300);
    define('_10MINSECS', 600);
        
	$action = optional_param('action', '');
    // $timestartday = optional_param('uts', 0, PARAM_INT);    // Unix time stamp id

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
		$table = table_statkafedra();
  		print_table_to_excel($table);
        exit();
	}

	$strtitle = get_string('statkafedra','block_dean');
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    $currenttab = 'statkafedra';
    include('tabs.php');
	

	$table = table_statkafedra();
   	print_table($table);

	echo  '<form name="allgroups" method="post" action="statkafedra.php">';
	// echo  '<input type="hidden" name="uts" value="'.$timestartday.'" />';
	echo  '<input type="hidden" name="action" value="excel" />';
	echo  '<div align="center">';
	echo  '<input type="submit" name="mark" value="';
	print_string('downloadexcel');
	echo '"></div></form>';

   print_footer();


function table_statkafedra()	
{
	global $CFG;
	
    $table->head  = array ('№', 'Course ID', "Дисциплина в плане", "Курс в Пегасе", 'Преподаватель', 'Кафедра');
                            // , "Всего <br> дисциплины", "Всего <br>апробированные"); 
    $table->align = array ("center", "left", "left", "left", 'left'); 
	$table->width = '85%';
    $table->size = array ('5%', '5%', '20%', '20%' , '10%', '30%');
    $table->columnwidth = array (4, 10, 30, 30, 30, 50); 
	
   	$table->titlesrows = array(30);
    $table->titles = array();
    $table->titles[] = get_string('statkafedra','block_dean');
    $table->downloadfilename = "statkafedra";
    $table->worksheetname = 'statkafedra';


   $kafedrs = get_records_select ('bsu_ref_subdepartment', "NotUsing = 0", '', 'Id, Name');

    $departments = array();
 	if ($kafedrs)	{
 		foreach ($kafedrs as $kafedr)	{
 			$departments[$kafedr->Id] = $kafedr->Name;
 		}
 	}
    

    $strsql = "SELECT DISTINCT d.courseid, d.name FROM mdl_dean_discipline d
               where courseid>1 and curriculumid in 
               (SELECT id FROM mdl_dean_curriculum where formlearning='correspondenceformtraining')
               ORDER BY d.name";
    // echo $strsql;
    
    if ($disciplines = get_records_sql ($strsql))   {
        
        $uaprobs  = array();
        foreach ($disciplines as $discipline)   {
            $uaprobs[] = $discipline->courseid;
        }
             
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


        $i=0;
        foreach ($disciplines as $discipline)   {
            // $table->data[] = array (++$i . '.', $discipline->courseid, $discipline->name, $coursesnames[$discipline->courseid], '', '');
            $prepodnames = '';
            $strdepart = '';            
            $departsids = array();
            $strsql = "SELECT userid FROM mdl_role_assignments
                       where roleid = 4 AND contextid in (SELECT id FROM mdl_context where contextlevel=50 AND instanceid = $discipline->courseid)";
            if ($roles = get_records_sql($strsql))  {
                foreach ($roles as $role)   {
                    $teacher = get_record_select('user', "id = $role->userid", 'id, lastname, firstname');
                    // $prepodnames .= fullname($teacher) . '<br>';
                    // $prepodnames .=  $role->userid . '<br>';

                    /*
                    if ($teacher_cards = get_records_select ('dean_teacher_card', "userid = $role->userid", '', 'id, staffid'))  {
                        $strdepart = '';
                        foreach ($teacher_cards as $teacher_card)   {
                            $staform = get_record_select('bsu_staffform', "id = $teacher_card->staffid", 'Id, SubDepartmentId');
                            if (isset($departments[$staform->SubDepartmentId])) {
                                $strdepart .=  $departments[$staform->SubDepartmentId].'<br>';
                            }     
                        }
                    } 
                    */
                    if ($teacher_cards = get_records_select ('dean_teacher_card', "userid = $role->userid", '', 'id, staffid'))  {
                        foreach ($teacher_cards as $teacher_card)   {
                            $staform = get_record_select('bsu_staffform', "id = $teacher_card->staffid", 'Id, SubDepartmentId');
                            if (isset($departments[$staform->SubDepartmentId])) {
                                $departsids[$staform->SubDepartmentId] = 1;
                                // $strdepart .=  $departments[$staform->SubDepartmentId].'<br>';
                            }     
                        }
                    } 
                    
                }
            }    
            if (empty($departsids)) {
                $table->data[] = array (++$i . '.', $discipline->courseid, $discipline->name, $coursesnames[$discipline->courseid], $prepodnames, $strdepart);                
            }   else {
                foreach ($departsids as $iddd => $departsid) {
                    $table->data[] = array (++$i . '.', $discipline->courseid, $discipline->name, 
                                        $coursesnames[$discipline->courseid], '', $departments[$iddd]);
                }
            }                    
            // $table->data[] = array ('', '', '', '', fullname($teacher), $strdepart);
        }    
    }
    
	return $table;
}    

/*
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
*/

?>