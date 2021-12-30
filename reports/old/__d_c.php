<?php // $Id: examcompare.php,v 1.1 2011/01/19 15:52:29 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../lib.php');
	require_once $CFG->dirroot.'/grade/export/lib.php';
	require_once $CFG->dirroot.'/grade/export/xls/grade_export_xls.php';
    
	$admin_is = isadmin();

    if (!$admin_is) {
        error(get_string('adminaccess', 'block_dean'), '..\faculty\faculty.php');
    }

    ignore_user_abort(false); 
        
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();

	$strtitle = 'Сопоставление дисциплин курсам.';
    
    $breadcrumbs  = '<a href="'.$CFG->wwwroot.'/blocks/dean/index.php">'.get_string('dean','block_dean').'</a>';
	$breadcrumbs .= " -> $strtitle";
	print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);
    
    // sopostavlenie_discipline_courses();
    sopostavlenie_userid_staffid();

    
    print_footer();
        

function sopostavlenie_discipline_courses()    
{
    global $CFG; 

   $strsql = "SELECT id, fullname FROM {$CFG->prefix}course
              where category in (58, 64)    
              ORDER BY fullname";
   $courses = get_records_sql($strsql);

    $coursesnames =array();
    foreach ($courses  as $course)  {          
        $clearname = $course->fullname;
        $clearname = mb_ereg_replace('(ЦТ)', '', $clearname);
        $clearname = mb_ereg_replace('_дневное', '', $clearname); 
        $clearname = mb_ereg_replace('(дневное)', '', $clearname);
        
        $pos = strpos($clearname, '(');
        $names = array();
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
		$coursesnames[$course->id] = trim($clearname);
    }
    
    // $bsudisciplines = get_records_select ('bsu_ref_disciplinename', "", '', 'id, name');
    $bsudisciplines = get_records_sql ("SELECT DISTINCT a.DisciplineNameId, b.name FROM mdl_dean_schedule a
                                      INNER JOIN mdl_bsu_ref_disciplinename b ON  a.DisciplineNameId=b.Id
                                      ORDER BY b.name;");
        

    foreach ($bsudisciplines as $bsudiscipline)   {
        echo '<hr>';
        foreach ($coursesnames as $courseid => $coursesname) {
            if ($coursesname == $bsudiscipline->name)  {
                echo $coursesname . ' === ' . $bsudiscipline->name . '<br>';
                echo $courseid . ' === ' . $bsudiscipline->DisciplineNameId . '<br>';
                if (!record_exists_select('dean_course_discipline', "disciplineid = $bsudiscipline->DisciplineNameId AND courseid=$courseid")) {
                    $rec->disciplineid = $bsudiscipline->DisciplineNameId;
                    $rec->courseid     = $courseid;
                    insert_record('dean_course_discipline', $rec);
                }
            } else {
                $pos = mb_strpos($coursesname ,  $bsudiscipline->name);
                if ($pos === false) continue;
                echo $coursesname . ' .=. ' . $bsudiscipline->name . '<br>';         
                echo $courseid . ' === ' . $bsudiscipline->DisciplineNameId . '<br>';       
                if (!record_exists_select('dean_course_discipline', "disciplineid = $bsudiscipline->DisciplineNameId AND courseid=$courseid")) {
                    $rec->disciplineid = $bsudiscipline->DisciplineNameId;
                    $rec->courseid     = $courseid;
                    insert_record('dean_course_discipline', $rec);
                }
            }
        }
    }        	
   
    
    return '';
}    


function sopostavlenie_userid_staffid()    
{
    global $CFG; 

   $strsql = "SELECT id, lastname, firstname FROM {$CFG->prefix}user
              where (auth = 'ldap2' || email like '%@bsu.edu.ru') and  deleted = 0
              ORDER BY lastname";
              
   $teachers = get_records_sql($strsql);
    
   $staffids = array();
   
   $notfoundteacher = array();
   $i=0; 
   foreach ($teachers as $teacher)  {
        $UPPERTEACHER = mb_strtoupper($teacher->lastname, 'UTF-8');
        
        $strsql = "SELECT Id, Name FROM mdl_bsu_staffform 
                   WHERE Name like '$UPPERTEACHER%'";
        // echo $strsql . '<br>';            
        if ($staffs = get_records_sql($strsql)) {
            $UPPERTEACHER = mb_strtoupper($teacher->lastname, 'UTF-8') . ' ' . mb_strtoupper($teacher->firstname, 'UTF-8');
            $flag = false;
            foreach ($staffs as $staff) {
                if ($UPPERTEACHER == $staff->Name)   {
                    // echo fullname($teacher) . ' === ' . $staff->Name . '<br>'; 
                    // $flag = true;  break;
                    $staffids[] = $staff->Id;
                    if (!record_exists_select('dean_teacher_card', "userid = $teacher->id AND staffid=$staff->Id")) {
                        $rec->userid = $teacher->id;
                        $rec->staffid=$staff->Id;
                        insert_record('dean_teacher_card', $rec);
                        echo $i++ . '<br>';
                    }
                } 
            }
            /*
            if (!$flag) {
                echo fullname($teacher) . ' === ???' . '<br>';
            }
            */
        } else {
            // echo fullname($teacher) . ' === ?' . '<br>';
            $notfoundteacher[] = fullname($teacher);
        }               
    }
    
    // $allstaffs = get_records_sql("SELECT staffformid FROM mdl_bsu_teachingload where edworkid in (SELECT distinct EdWorkId FROM mdl_dean_schedule);"
    $allstaffs = get_records_sql("SELECT id, Name FROM mdl_bsu_staffform");
    
    foreach ($allstaffs as $staff)   {
        if (!in_array($staff->id, $staffids))   {
            // echo $staff->Name . ' === ' . '??? <br>';
            $notfoundteacher[] = $staff->Name; 
        } 
    }
    
    sort($notfoundteacher);
    
    foreach ($notfoundteacher as $nft)  {
        echo $nft . '<br>';
    }
    
    return true;
}    


?>